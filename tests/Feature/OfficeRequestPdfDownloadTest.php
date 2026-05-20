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

class OfficeRequestPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_user_can_download_receipt_approval_and_certificate_pdfs(): void
    {
        Storage::fake('private');

        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Jdeideh',
            'region' => 'Mount Lebanon',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Jdeideh Municipal Office',
            'address' => 'Central square',
            'email' => 'office@example.test',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Family Civil Record',
            'price' => 120000,
            'currency' => 'LBP',
            'estimated_duration_days' => 3,
        ]);

        $serviceRequest = ServiceRequest::create([
            'reference_number' => 'SRQ-2026-PDFTEST',
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'transaction_id' => 'txn_pdf_123',
        ]);

        foreach (['receipt', 'approval', 'certificate'] as $type) {
            $response = $this->actingAs($officeUser)
                ->get(route('office.requests.pdf', [$serviceRequest, $type]));

            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('content-type'));
            $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));
            $this->assertStringStartsWith('%PDF', $response->streamedContent());

            Storage::disk('private')->assertExists("pdfs/{$serviceRequest->id}/{$type}-{$serviceRequest->reference_number}.pdf");
        }
    }

    public function test_office_user_cannot_download_pdfs_before_request_reaches_required_state(): void
    {
        Storage::fake('private');

        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Zahle',
            'region' => 'Bekaa',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Zahle Municipal Office',
            'address' => 'Main avenue',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Building Permit',
            'price' => 25,
            'currency' => 'USD',
            'estimated_duration_days' => 7,
        ]);

        $serviceRequest = ServiceRequest::create([
            'reference_number' => 'SRQ-2026-PDFLOCK',
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        foreach (['receipt', 'approval', 'certificate'] as $type) {
            $this->actingAs($officeUser)
                ->get(route('office.requests.pdf', [$serviceRequest, $type]))
                ->assertForbidden();
        }
    }
}
