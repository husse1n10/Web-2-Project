<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle NOWPayments IPN webhook.
     *
     * NOWPayments POSTs payment status changes to this endpoint. We verify the
     * HMAC-SHA512 signature against our IPN secret, then update the matching
     * ServiceRequest's payment_status if the payment is confirmed/finished.
     */
    public function nowpayments(Request $request): JsonResponse
    {
        $secret = (string) config('services.nowpayments.ipn_secret');
        if ($secret === '') {
            Log::warning('NOWPayments webhook hit but IPN secret is not configured.');
            return response()->json(['ok' => false, 'error' => 'not_configured'], 503);
        }

        $signature = (string) $request->header('x-nowpayments-sig', '');
        $rawBody   = (string) $request->getContent();

        if (!$this->verifySignature($rawBody, $signature, $secret)) {
            Log::warning('NOWPayments webhook: invalid signature.', [
                'sig_prefix' => substr($signature, 0, 16),
                'ip'         => $request->ip(),
            ]);
            return response()->json(['ok' => false, 'error' => 'invalid_signature'], 401);
        }

        $payload  = $request->all();
        $orderId  = $payload['order_id'] ?? null;
        $status   = $payload['payment_status'] ?? null;

        if (!$orderId || !$status) {
            Log::info('NOWPayments webhook: missing order_id or payment_status.', $payload);
            return response()->json(['ok' => false, 'error' => 'missing_fields'], 422);
        }

        $serviceRequest = ServiceRequest::with('service')->find($orderId);
        if (!$serviceRequest) {
            Log::warning('NOWPayments webhook: order not found.', ['order_id' => $orderId]);
            // Return 200 so NOWPayments stops retrying for an order we don't know about.
            return response()->json(['ok' => true, 'note' => 'order_not_found']);
        }

        // Idempotent — already paid, no-op.
        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['ok' => true, 'note' => 'already_paid']);
        }

        // NOWPayments status flow: waiting → confirming → confirmed → finished
        // (or failed / expired / partially_paid). We only mark paid on confirmed/finished.
        if (!in_array($status, ['confirmed', 'finished'], true)) {
            Log::info('NOWPayments webhook: non-final status received.', [
                'order_id' => $orderId,
                'status'   => $status,
            ]);
            return response()->json(['ok' => true, 'note' => 'pending']);
        }

        // Amount check — make sure NOWPayments charged the expected USD amount.
        $expectedUsd = (float) $serviceRequest->service->price;
        $reportedUsd = (float) ($payload['price_amount'] ?? 0);
        if ($reportedUsd > 0 && abs($reportedUsd - $expectedUsd) > 0.01) {
            Log::warning('NOWPayments webhook: amount mismatch.', [
                'order_id'    => $orderId,
                'expected'    => $expectedUsd,
                'reported'    => $reportedUsd,
            ]);
            // 200 so NOWPayments doesn't retry. We just refuse to mark paid.
            return response()->json(['ok' => true, 'note' => 'amount_mismatch']);
        }

        $serviceRequest->update([
            'payment_status' => 'paid',
            'payment_method' => 'crypto',
            'transaction_id' => $payload['payment_id'] ?? $serviceRequest->transaction_id,
        ]);

        Log::info('NOWPayments webhook: payment confirmed.', [
            'order_id'   => $orderId,
            'payment_id' => $payload['payment_id'] ?? null,
            'pay_currency' => $payload['pay_currency'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Verify NOWPayments IPN signature.
     *
     * NOWPayments computes the signature as:
     *   HMAC-SHA512(payload_json_sorted_by_keys, ipn_secret)
     * where the payload is re-serialized after sorting its keys alphabetically.
     */
    private function verifySignature(string $payload, string $signature, string $secret): bool
    {
        if ($signature === '' || $payload === '') {
            return false;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return false;
        }

        ksort($decoded);
        $sortedJson = json_encode($decoded, JSON_UNESCAPED_SLASHES);
        if ($sortedJson === false) {
            return false;
        }

        $expected = hash_hmac('sha512', $sortedJson, $secret);

        return hash_equals($expected, $signature);
    }
}
