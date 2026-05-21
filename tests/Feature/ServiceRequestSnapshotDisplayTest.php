<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestSnapshotDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function createContext(): array
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => '1234567890',
            'id_document' => 'id_documents/fake.pdf',
            'citizen_verification_status' => 'approved',
            'citizen_verified_at' => now(),
        ]);

        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Aley',
            'region' => 'Mount Lebanon',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Aley Municipal Office',
            'address' => 'Main road',
            'email' => 'office@example.test',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Original Family Record',
            'price' => 120000,
            'currency' => 'LBP',
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

        return compact('citizen', 'officeUser', 'office', 'service', 'serviceRequest');
    }

    public function test_citizen_pages_use_snapshotted_service_details_after_service_changes(): void
    {
        ['citizen' => $citizen, 'service' => $service, 'serviceRequest' => $serviceRequest] = $this->createContext();

        $service->update([
            'name' => 'Updated Family Record',
            'price' => 25,
            'currency' => 'USD',
        ]);

        $requestPage = $this->actingAs($citizen)
            ->get(route('citizen.requests.show', $serviceRequest));

        $requestPage->assertOk();
        $requestPage->assertSeeText('Original Family Record');
        $requestPage->assertSeeText('LBP 120,000');
        $requestPage->assertDontSeeText('Updated Family Record');

        $paymentPage = $this->actingAs($citizen)
            ->get(route('citizen.payment', $serviceRequest));

        $paymentPage->assertOk();
        $paymentPage->assertSeeText('Original Family Record');
        $paymentPage->assertSeeText('120,000');
        $paymentPage->assertSeeText('LBP');
        $paymentPage->assertDontSeeText('Updated Family Record');
    }

    public function test_request_pages_still_load_after_service_is_soft_deleted(): void
    {
        [
            'citizen' => $citizen,
            'officeUser' => $officeUser,
            'service' => $service,
            'serviceRequest' => $serviceRequest,
        ] = $this->createContext();

        $service->delete();

        $citizenPage = $this->actingAs($citizen)
            ->get(route('citizen.requests.show', $serviceRequest));

        $citizenPage->assertOk();
        $citizenPage->assertSeeText('Original Family Record');

        $officePage = $this->actingAs($officeUser)
            ->get(route('office.requests.show', $serviceRequest));

        $officePage->assertOk();
        $officePage->assertSeeText('Original Family Record');
    }
}
