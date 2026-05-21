<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook as StripeWebhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

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
        $expectedAmount = $serviceRequest->resolved_service_price;
        $expectedCurrency = strtolower($serviceRequest->resolved_service_currency);
        $reportedAmount = (float) ($payload['price_amount'] ?? 0);
        $reportedCurrency = strtolower((string) ($payload['price_currency'] ?? ''));

        if ($reportedCurrency !== '' && $reportedCurrency !== $expectedCurrency) {
            Log::warning('NOWPayments webhook: currency mismatch.', [
                'order_id' => $orderId,
                'expected_currency' => $expectedCurrency,
                'reported_currency' => $reportedCurrency,
            ]);

            return response()->json(['ok' => true, 'note' => 'currency_mismatch']);
        }

        if ($reportedAmount > 0 && abs($reportedAmount - $expectedAmount) > 0.01) {
            Log::warning('NOWPayments webhook: amount mismatch.', [
                'order_id'    => $orderId,
                'expected'    => $expectedAmount,
                'reported'    => $reportedAmount,
            ]);
            // 200 so NOWPayments doesn't retry. We just refuse to mark paid.
            return response()->json(['ok' => true, 'note' => 'amount_mismatch']);
        }

        $serviceRequest->update([
            'amount_paid' => $serviceRequest->resolved_service_price,
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
     * Handle Stripe webhook events.
     *
     * Stripe signs every webhook with HMAC-SHA256 over `{timestamp}.{payload}`
     * using the endpoint signing secret. We let Stripe's SDK do the verification
     * via Webhook::constructEvent(); if the signature is bad it throws.
     *
     * We only act on `checkout.session.completed` — that's the event fired when
     * a card payment goes through Stripe Checkout. Other event types are ignored
     * but acknowledged (200 OK) so Stripe doesn't retry them.
     */
    public function stripe(Request $request): JsonResponse
    {
        $secret = (string) config('services.stripe.webhook_secret');
        if ($secret === '') {
            Log::warning('Stripe webhook hit but webhook_secret is not configured.');
            return response()->json(['ok' => false, 'error' => 'not_configured'], 503);
        }

        $payload = (string) $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');

        try {
            $event = StripeWebhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature.', ['ip' => $request->ip()]);
            return response()->json(['ok' => false, 'error' => 'invalid_signature'], 401);
        } catch (UnexpectedValueException $e) {
            return response()->json(['ok' => false, 'error' => 'invalid_payload'], 400);
        }

        // Only the checkout.session.completed event marks a request as paid.
        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ok' => true, 'note' => 'ignored', 'type' => $event->type]);
        }

        $session = $event->data->object;
        $serviceRequestId = $session->metadata->service_request_id ?? null;

        if (!$serviceRequestId) {
            Log::warning('Stripe webhook: missing service_request_id metadata.', [
                'session_id' => $session->id ?? null,
            ]);
            return response()->json(['ok' => true, 'note' => 'no_metadata']);
        }

        $serviceRequest = ServiceRequest::with('service')->find($serviceRequestId);
        if (!$serviceRequest) {
            Log::warning('Stripe webhook: service request not found.', [
                'service_request_id' => $serviceRequestId,
            ]);
            return response()->json(['ok' => true, 'note' => 'order_not_found']);
        }

        // Idempotent — already paid, no-op.
        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['ok' => true, 'note' => 'already_paid']);
        }

        // Make sure Stripe actually charged the card.
        if (($session->payment_status ?? null) !== 'paid') {
            return response()->json(['ok' => true, 'note' => 'session_not_paid']);
        }

        // Amount-check defense: session amount_total must match service price * 100 (cents).
        $expectedCents = PaymentService::toMinorUnit($serviceRequest->resolved_service_price);
        $actualCents   = (int) ($session->amount_total ?? 0);
        if ($actualCents !== $expectedCents) {
            Log::warning('Stripe webhook: amount mismatch.', [
                'service_request_id' => $serviceRequestId,
                'expected_cents'     => $expectedCents,
                'actual_cents'       => $actualCents,
            ]);
            return response()->json(['ok' => true, 'note' => 'amount_mismatch']);
        }

        $expectedCurrency = strtolower($serviceRequest->resolved_service_currency);
        $actualCurrency = strtolower((string) ($session->currency ?? ''));
        if ($actualCurrency !== '' && $actualCurrency !== $expectedCurrency) {
            Log::warning('Stripe webhook: currency mismatch.', [
                'service_request_id' => $serviceRequestId,
                'expected_currency'  => $expectedCurrency,
                'actual_currency'    => $actualCurrency,
            ]);
            return response()->json(['ok' => true, 'note' => 'currency_mismatch']);
        }

        $serviceRequest->update([
            'amount_paid' => $serviceRequest->resolved_service_price,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'transaction_id' => $session->payment_intent ?? $serviceRequest->transaction_id,
        ]);

        Log::info('Stripe webhook: payment confirmed.', [
            'service_request_id' => $serviceRequestId,
            'payment_intent'     => $session->payment_intent ?? null,
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
