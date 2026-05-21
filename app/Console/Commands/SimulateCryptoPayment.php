<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Dev-only helper: posts a forged NOWPayments IPN to /webhooks/nowpayments
 * with a valid HMAC-SHA512 signature, so the rest of the post-payment flow
 * (status update, notifications, etc) can be tested without sending real crypto.
 *
 * Refuses to run when APP_ENV=production.
 */
class SimulateCryptoPayment extends Command
{
    protected $signature = 'crypto:simulate-payment
        {request? : ServiceRequest ID or reference number}
        {--latest : Use the latest unpaid request}
        {--list : List recent unpaid requests instead of simulating}
        {--status=finished : NOWPayments status to simulate}
        {--url= : Override the webhook URL (default: app.url + /webhooks/nowpayments)}';

    protected $description = 'Simulate a NOWPayments IPN for a service request (dev only).';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('This command is disabled in production.');
            return self::FAILURE;
        }

        if ($this->option('list')) {
            return $this->listRecentUnpaidRequests();
        }

        $secret = (string) config('services.nowpayments.ipn_secret');
        if ($secret === '') {
            $this->error('NOWPAYMENTS_IPN_SECRET is not set in .env.');
            return self::FAILURE;
        }

        $status = strtolower(trim((string) $this->option('status')));
        if (!in_array($status, $this->supportedStatuses(), true)) {
            $this->error('Unsupported status. Use one of: ' . implode(', ', $this->supportedStatuses()));
            return self::FAILURE;
        }

        $req = $this->resolveRequest();
        if (!$req) {
            $this->error('ServiceRequest not found. Pass an ID/reference, use --latest, or run with --list.');
            return self::FAILURE;
        }

        if ($req->payment_status === 'paid') {
            $this->warn("Request #{$req->id} is already marked paid. Nothing to do.");
            return self::SUCCESS;
        }

        $payload = [
            'payment_id'        => 'sim_' . now()->timestamp,
            'payment_status'    => $status,
            'pay_address'       => '0x0000000000000000000000000000000000000000',
            'price_amount'      => (float) $req->resolved_service_price,
            'price_currency'    => strtolower($req->resolved_service_currency),
            'pay_amount'        => 0.0,
            'pay_currency'      => 'usdterc20',
            'order_id'          => (string) $req->id,
            'order_description' => 'Service Request: ' . $req->reference_number,
        ];

        ksort($payload);
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha512', $body, $secret);

        $url = $this->option('url') ?: rtrim((string) config('app.url'), '/') . '/webhooks/nowpayments';

        $this->info("POSTing simulated '{$status}' IPN to {$url} for request #{$req->id} ({$req->reference_number})...");

        $response = Http::withHeaders([
                'Content-Type'      => 'application/json',
                'x-nowpayments-sig' => $signature,
            ])
            ->withBody($body, 'application/json')
            ->timeout(15)
            ->post($url);

        if (!$response->successful()) {
            $this->error("Webhook returned HTTP {$response->status()}: {$response->body()}");
            return self::FAILURE;
        }

        $this->info('Webhook accepted: ' . $response->body());

        $req->refresh();
        $this->line('');
        $this->line("Request #{$req->id} payment_status is now: <fg=green>{$req->payment_status}</>");

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function supportedStatuses(): array
    {
        return [
            'waiting',
            'confirming',
            'confirmed',
            'finished',
            'failed',
            'expired',
            'partially_paid',
        ];
    }

    private function resolveRequest(): ?ServiceRequest
    {
        $request = trim((string) $this->argument('request'));

        if ($request !== '') {
            if (ctype_digit($request)) {
                return ServiceRequest::find((int) $request)
                    ?? ServiceRequest::where('reference_number', $request)->first();
            }

            return ServiceRequest::where('reference_number', $request)->first();
        }

        if (!$this->option('latest')) {
            return null;
        }

        $query = ServiceRequest::query()->where('payment_status', '!=', 'paid');
        $req = (clone $query)
            ->where('payment_method', 'crypto')
            ->latest('id')
            ->first();

        $req ??= $query->latest('id')->first();

        if ($req) {
            $this->line("Using latest unpaid request: #{$req->id} ({$req->reference_number})");
        }

        return $req;
    }

    private function listRecentUnpaidRequests(): int
    {
        $requests = ServiceRequest::query()
            ->where('payment_status', '!=', 'paid')
            ->latest('id')
            ->limit(10)
            ->get([
                'id',
                'reference_number',
                'payment_method',
                'payment_status',
                'transaction_id',
                'updated_at',
            ]);

        if ($requests->isEmpty()) {
            $this->warn('No unpaid requests found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Reference', 'Method', 'Status', 'Transaction', 'Updated'],
            $requests->map(fn (ServiceRequest $request) => [
                $request->id,
                $request->reference_number,
                $request->payment_method ?? '-',
                $request->payment_status ?? '-',
                $request->transaction_id ?? '-',
                optional($request->updated_at)?->toDateTimeString(),
            ])->all()
        );

        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan crypto:simulate-payment 42');
        $this->line('  php artisan crypto:simulate-payment SRQ-2026-ABCDEFGH');
        $this->line('  php artisan crypto:simulate-payment --latest --status=confirmed');

        return self::SUCCESS;
    }
}
