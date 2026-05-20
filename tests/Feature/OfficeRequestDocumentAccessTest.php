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

class OfficeRequestDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_user_can_view_document_for_owned_request(): void
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
            'name' => 'Baabda',
            'region' => 'Mount Lebanon',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Hazmieh Citizen Services Office',
            'address' => 'Hazmieh',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Birth Certificate Issuance',
            'price' => 15,
        ]);

        $serviceRequest = ServiceRequest::create([
            'reference_number' => 'SRQ-2026-DOCTEST',
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $path = "request_documents/{$serviceRequest->id}/national-id.jpg";
        Storage::disk('private')->put($path, 'document-bytes');

        $document = RequestDocument::create([
            'service_request_id' => $serviceRequest->id,
            'file_path' => $path,
            'original_name' => 'national-id.jpg',
            'document_type' => 'National ID',
            'uploaded_by' => 'citizen',
        ]);

        $response = $this->actingAs($officeUser)
            ->get(route('office.documents.view', [$serviceRequest, $document->id]));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }
}
