<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\RequestDocument;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenRequestDocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_download_own_request_document_from_private_disk(): void
    {
        Storage::fake('private');

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Saida',
            'region' => 'South',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Saida Citizen Services Office',
            'address' => 'Saida',
        ]);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Occupancy Permit Request',
            'price' => 120,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $serviceRequest = ServiceRequest::create([
            'reference_number' => 'SRQ-2026-CITDOC01',
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $path = "request_documents/{$serviceRequest->id}/supporting-document.pdf";
        Storage::disk('private')->put($path, 'document-bytes');

        $document = RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'file_path' => $path,
            'original_name' => 'supporting-document.pdf',
            'document_type' => 'Supporting Document',
            'uploaded_by' => 'citizen',
        ]);

        $response = $this->actingAs($citizen)
            ->get(route('citizen.documents.download', [$serviceRequest, $document->id]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('supporting-document.pdf', $response->headers->get('content-disposition'));
    }
}
