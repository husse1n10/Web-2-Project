<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function makeServiceWithOffice(array $requiredDocs = ['National ID copy', 'Photo']): Service
    {
        $municipality = Municipality::create([
            'name' => 'Beirut',
            'is_active' => true,
        ]);

        $office = Office::create([
            'name' => 'Beirut Civil Registry',
            'address' => 'Downtown Beirut',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);

        return Service::create([
            'name' => 'Birth Certificate',
            'office_id' => $office->id,
            'price' => 10.00,
            'currency' => 'USD',
            'estimated_duration_days' => 5,
            'required_documents' => $requiredDocs,
            'is_active' => true,
        ]);
    }

    private function makeCompletedCitizen(): User
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

    public function test_citizen_can_submit_a_request_and_is_redirected_to_payment(): void
    {
        Event::fake();
        Storage::fake('private');
        Storage::fake('public');

        $citizen = $this->makeCompletedCitizen();
        $service = $this->makeServiceWithOffice();

        $response = $this->actingAs($citizen)->post(
            route('citizen.requests.submit', $service),
            [
                'notes' => 'Test submission',
                'documents' => [
                    UploadedFile::fake()->create('national_id.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->image('photo.jpg'),
                ],
            ]
        );

        $serviceRequest = ServiceRequest::where('citizen_id', $citizen->id)->first();

        $this->assertNotNull($serviceRequest, 'Service request should be created');
        $response->assertRedirect(route('citizen.payment', $serviceRequest));
        $response->assertSessionHas('success');

        $this->assertSame('pending', $serviceRequest->status);
        $this->assertSame('unpaid', $serviceRequest->payment_status);
        $this->assertCount(2, $serviceRequest->documents);
        $this->assertNotNull($serviceRequest->qr_code);

        // Reference number format: SRQ-YYYY-XXXXXXXX (8-char random suffix, non-enumerable)
        $this->assertMatchesRegularExpression(
            '/^SRQ-\d{4}-[A-Z0-9]{8}$/',
            $serviceRequest->reference_number
        );
    }

    public function test_submission_is_blocked_when_citizen_profile_is_incomplete(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => null,
            'id_document' => null,
        ]);

        $service = $this->makeServiceWithOffice();

        $response = $this->actingAs($citizen)->post(
            route('citizen.requests.submit', $service),
            ['notes' => 'should fail']
        );

        $response->assertRedirect(route('citizen.profile'));
        $this->assertSame(0, ServiceRequest::count());
    }

    public function test_submission_is_blocked_when_citizen_identity_is_not_approved(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => '1234567890',
            'id_document' => 'id_documents/fake.pdf',
            'citizen_verification_status' => 'pending',
        ]);

        $service = $this->makeServiceWithOffice();

        $response = $this->actingAs($citizen)->post(
            route('citizen.requests.submit', $service),
            ['notes' => 'should fail']
        );

        $response->assertRedirect(route('citizen.profile'));
        $this->assertSame(0, ServiceRequest::count());
    }
}
