<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenPaymentValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingRequest(): array
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
            'name' => 'Beirut',
            'region' => 'Beirut',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Beirut Municipal Office',
            'address' => 'Downtown Beirut',
            'email' => 'office@example.test',
        ]);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Birth Certificate',
            'price' => 15.00,
            'currency' => 'USD',
            'estimated_duration_days' => 3,
            'is_active' => true,
        ]);

        $serviceRequest = ServiceRequest::create([
            'reference_number' => ServiceRequest::generateReference(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'service_name' => $service->name,
            'service_price' => $service->price,
            'service_currency' => $service->currency,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        return compact('citizen', 'serviceRequest');
    }

    public function test_crypto_payment_rejects_legacy_generic_usdt_code(): void
    {
        ['citizen' => $citizen, 'serviceRequest' => $serviceRequest] = $this->createPendingRequest();

        $response = $this->actingAs($citizen)
            ->from(route('citizen.payment', $serviceRequest))
            ->post(route('citizen.payment.process', $serviceRequest), [
                'payment_method' => 'crypto',
                'crypto_currency' => 'USDT',
            ]);

        $response->assertRedirect(route('citizen.payment', $serviceRequest));
        $response->assertSessionHasErrors('crypto_currency');
    }

    public function test_payment_page_lists_only_supported_crypto_options(): void
    {
        ['citizen' => $citizen, 'serviceRequest' => $serviceRequest] = $this->createPendingRequest();

        $response = $this->actingAs($citizen)
            ->get(route('citizen.payment', $serviceRequest));

        $response->assertOk();
        $response->assertSeeText('Bitcoin (BTC)');
        $response->assertSeeText('Tether (USDT ERC20)');
        $response->assertDontSeeText('Tether (USDT TRC20)');
        $response->assertDontSeeText('Tether (USDT BSC)');
        $response->assertDontSeeText('Ethereum (ETH)');
    }
}
