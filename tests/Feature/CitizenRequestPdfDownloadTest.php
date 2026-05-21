<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenRequestPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function makeCitizen(): User
    {
        return User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => '1234567890',
            'id_document' => 'id_documents/fake.pdf',
            'citizen_verification_status' => 'approved',
            'citizen_verified_at' => now(),
        ]);
    }

    private function makeRequest(User $citizen, string $status = 'completed', string $paymentStatus = 'paid'): ServiceRequest
    {
        $municipality = Municipality::create([
            'name' => 'Bikfaya',
            'region' => 'Mount Lebanon',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Bikfaya Municipal Office',
            'address' => 'Town center',
            'email' => 'office@example.test',
        ]);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Civil Record Extract',
            'price' => 120000,
            'currency' => 'LBP',
            'estimated_duration_days' => 3,
            'is_active' => true,
        ]);

        return ServiceRequest::create([
            'reference_number' => ServiceRequest::generateReference(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentStatus === 'paid' ? 'card' : null,
            'transaction_id' => $paymentStatus === 'paid' ? 'txn_citizen_pdf' : null,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function test_citizen_request_page_shows_available_pdf_links_and_downloads_them(): void
    {
        Storage::fake('private');

        $citizen = $this->makeCitizen();
        $serviceRequest = $this->makeRequest($citizen, 'completed', 'paid');

        $page = $this->actingAs($citizen)
            ->get(route('citizen.requests.show', $serviceRequest));

        $page->assertOk();
        $page->assertSeeText('Download Receipt');
        $page->assertSeeText('Download Approval Letter');
        $page->assertSeeText('Download Certificate');

        $receipt = $this->actingAs($citizen)
            ->get(route('citizen.requests.receipt', $serviceRequest));
        $receipt->assertOk();
        $this->assertSame('application/pdf', $receipt->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $receipt->streamedContent());

        foreach (['approval', 'certificate'] as $type) {
            $response = $this->actingAs($citizen)
                ->get(route('citizen.requests.pdf', [$serviceRequest, $type]));

            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF', $response->streamedContent());
        }
    }

    public function test_citizen_request_page_shows_approval_link_without_receipt_when_request_is_approved_but_unpaid(): void
    {
        $citizen = $this->makeCitizen();
        $serviceRequest = $this->makeRequest($citizen, 'approved', 'unpaid');

        $response = $this->actingAs($citizen)
            ->get(route('citizen.requests.show', $serviceRequest));

        $response->assertOk();
        $response->assertSeeText('Download Approval Letter');
        $response->assertDontSeeText('Download Receipt');
        $response->assertDontSeeText('Download Certificate');
    }

    public function test_citizen_cannot_download_pdfs_before_state_allows_them(): void
    {
        Storage::fake('private');

        $citizen = $this->makeCitizen();
        $serviceRequest = $this->makeRequest($citizen, 'pending', 'unpaid');

        $this->actingAs($citizen)
            ->get(route('citizen.requests.receipt', $serviceRequest))
            ->assertForbidden();

        foreach (['approval', 'certificate'] as $type) {
            $this->actingAs($citizen)
                ->get(route('citizen.requests.pdf', [$serviceRequest, $type]))
                ->assertForbidden();
        }
    }
}
