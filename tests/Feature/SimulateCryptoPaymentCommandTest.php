<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SimulateCryptoPaymentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_accepts_reference_number_and_posts_finished_status(): void
    {
        Config::set('services.nowpayments.ipn_secret', 'test-ipn-secret');
        Http::fake([
            'https://example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $serviceRequest = $this->createPendingRequest();

        $this->artisan('crypto:simulate-payment', [
            'request' => $serviceRequest->reference_number,
            '--url' => 'https://example.test/webhooks/nowpayments',
        ])->assertExitCode(0);

        Http::assertSent(function (ClientRequest $request) use ($serviceRequest) {
            $payload = json_decode($request->body(), true);

            return $request->url() === 'https://example.test/webhooks/nowpayments'
                && $request->hasHeader('x-nowpayments-sig')
                && is_array($payload)
                && $payload['order_id'] === (string) $serviceRequest->id
                && $payload['payment_status'] === 'finished';
        });
    }

    public function test_latest_prefers_unpaid_crypto_requests_and_allows_custom_status(): void
    {
        Config::set('services.nowpayments.ipn_secret', 'test-ipn-secret');
        Http::fake([
            'https://example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $this->createPendingRequest([
            'reference_number' => 'SRQ-2026-OLDER000',
            'payment_method' => null,
        ]);

        $cryptoRequest = $this->createPendingRequest([
            'reference_number' => 'SRQ-2026-CRYPTO01',
            'payment_method' => 'crypto',
        ]);

        $this->createPendingRequest([
            'reference_number' => 'SRQ-2026-LATEST00',
            'payment_method' => 'card',
        ]);

        $this->artisan('crypto:simulate-payment', [
            '--latest' => true,
            '--status' => 'confirmed',
            '--url' => 'https://example.test/webhooks/nowpayments',
        ])->assertExitCode(0);

        Http::assertSent(function (ClientRequest $request) use ($cryptoRequest) {
            $payload = json_decode($request->body(), true);

            return is_array($payload)
                && $payload['order_id'] === (string) $cryptoRequest->id
                && $payload['payment_status'] === 'confirmed';
        });
    }

    public function test_command_rejects_unsupported_status(): void
    {
        Config::set('services.nowpayments.ipn_secret', 'test-ipn-secret');
        Http::fake();

        $serviceRequest = $this->createPendingRequest();

        $this->artisan('crypto:simulate-payment', [
            'request' => (string) $serviceRequest->id,
            '--status' => 'done',
            '--url' => 'https://example.test/webhooks/nowpayments',
        ])->assertExitCode(1);

        Http::assertNothingSent();
    }

    private function createPendingRequest(array $overrides = []): ServiceRequest
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => '1234567890',
            'id_document' => 'id_documents/fake.pdf',
            'citizen_verification_status' => 'approved',
            'citizen_verified_at' => now(),
        ]);

        $municipality = Municipality::create([
            'name' => 'Beirut ' . uniqid(),
            'region' => 'Beirut',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Beirut Municipal Office ' . uniqid(),
            'address' => 'Downtown Beirut',
            'email' => 'office-' . uniqid() . '@example.test',
        ]);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Birth Certificate',
            'price' => 15.00,
            'currency' => 'USD',
            'estimated_duration_days' => 3,
            'is_active' => true,
        ]);

        return ServiceRequest::create(array_merge([
            'reference_number' => ServiceRequest::generateReference(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'service_name' => $service->name,
            'service_price' => $service->price,
            'service_currency' => $service->currency,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => null,
        ], $overrides));
    }
}
