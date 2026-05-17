<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\HttpClient\CurlClient;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setMaxNetworkRetries(0);
        CurlClient::instance()->setTimeout(30);
        CurlClient::instance()->setConnectTimeout(10);
    }

    public function process(ServiceRequest $serviceRequest, string $method, array $payload): array
    {
        return match ($method) {
            'card'   => $this->processCard($serviceRequest, $payload),
            'crypto' => $this->processCrypto($serviceRequest, $payload),
            default  => ['success' => false, 'message' => 'Unknown payment method.'],
        };
    }

    // ── Card Payment (Stripe Checkout Session) ─────────────────────
    private function processCard(ServiceRequest $req, array $payload): array
    {
        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => strtolower($req->service->currency ?? 'usd'),
                        'product_data' => [
                            'name'        => $req->service->name,
                            'description' => 'Service Request: ' . $req->reference_number,
                        ],
                        'unit_amount' => (int) ($req->service->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('citizen.payment.success', $req) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('citizen.payment.cancel', $req),
                'metadata'    => [
                    'service_request_id' => $req->id,
                    'reference_number'   => $req->reference_number,
                ],
            ]);

            return [
                'success'      => true,
                'redirect_url' => $session->url,
                'session_id'   => $session->id,
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => 'Stripe error: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Could not connect to payment processor. Please try again.'];
        }
    }

    // ── Verify Stripe Session ──────────────────────────────────────
    public function verifyStripeSession(string $sessionId, ServiceRequest $req): array
    {
        try {
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['success' => false, 'message' => 'Payment not completed.'];
            }

            // Reject sessions that belong to a different ServiceRequest — without
            // this check a paid session_id from one request could be replayed on
            // another to mark it paid for free.
            if ((int) ($session->metadata['service_request_id'] ?? 0) !== (int) $req->id) {
                return ['success' => false, 'message' => 'Payment session does not match this request.'];
            }

            // Confirm the amount paid matches the expected price to prevent
            // a $1 session being replayed against a $1000 request.
            $expectedCents = (int) round($req->service->price * 100);
            if ((int) $session->amount_total !== $expectedCents) {
                return ['success' => false, 'message' => 'Payment amount does not match the request price.'];
            }

            return [
                'success'        => true,
                'transaction_id' => $session->payment_intent,
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Crypto Payment (NOWPayments hosted invoice) ────────────────
    // Creates an invoice on NOWPayments and returns the hosted invoice URL.
    // The citizen completes payment on NOWPayments' page; we get the final
    // status via the IPN webhook (see WebhookController::nowpayments).
    private function processCrypto(ServiceRequest $req, array $payload): array
    {
        $apiKey  = config('services.nowpayments.api_key');
        $baseUrl = rtrim((string) config('services.nowpayments.base_url'), '/');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Crypto payments are not configured. Please use card payment.',
            ];
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key'    => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(20)
                ->post("{$baseUrl}/invoice", [
                    'price_amount'      => (float) $req->service->price,
                    'price_currency'    => strtolower($req->service->currency ?? 'usd'),
                    'pay_currency'      => strtolower($payload['crypto_currency'] ?? 'btc'),
                    'order_id'          => (string) $req->id,
                    'order_description' => 'Service Request: ' . $req->reference_number,
                    'ipn_callback_url'  => route('webhooks.nowpayments'),
                    'success_url'       => route('citizen.payment.success', $req),
                    'cancel_url'        => route('citizen.payment.cancel', $req),
                ]);

            if (!$response->successful()) {
                Log::warning('NOWPayments invoice creation failed.', [
                    'status' => $response->status(),
                    'body'   => Str::limit($response->body(), 400),
                ]);
                return ['success' => false, 'message' => 'Could not create crypto invoice. Please try again.'];
            }

            $data = $response->json();
            $invoiceUrl = $data['invoice_url'] ?? null;
            $invoiceId  = $data['id'] ?? null;

            if (empty($invoiceUrl)) {
                return ['success' => false, 'message' => 'Crypto provider returned an invalid response.'];
            }

            return [
                'success'     => true,
                'invoice_id'  => (string) $invoiceId,
                'invoice_url' => (string) $invoiceUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('NOWPayments invoice request threw: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not connect to crypto payment processor. Please try again.'];
        }
    }

    // ── Currency Conversion ────────────────────────────────────────
    public function convertCurrency(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $cacheKey = "exchange_rate_{$from}_{$to}";

        $rate = Cache::remember($cacheKey, 3600, function () use ($from, $to) {
            try {
                $response = Http::timeout(5)->get(
                    "https://api.exchangerate-api.com/v4/latest/{$from}"
                );

                if ($response->successful()) {
                    return $response->json("rates.{$to}");
                }
            } catch (\Exception $e) {
                // Fail silently, use fallback
            }

            // Fallback rates
            $fallback = [
                'USD' => ['LBP' => 89500, 'EUR' => 0.92],
                'LBP' => ['USD' => 0.0000112, 'EUR' => 0.0000103],
                'EUR' => ['USD' => 1.09, 'LBP' => 97000],
            ];

            return $fallback[$from][$to] ?? 1;
        });

        return round($amount * $rate, 2);
    }
}
