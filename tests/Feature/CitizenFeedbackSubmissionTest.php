<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenFeedbackSubmissionTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeOffice(): Office
    {
        $municipality = Municipality::create([
            'name' => 'Beirut',
            'is_active' => true,
        ]);

        return Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Beirut Civil Registry',
            'address' => 'Downtown Beirut',
            'is_active' => true,
        ]);
    }

    private function makeServiceRequest(User $citizen, Office $office, string $status = 'completed'): ServiceRequest
    {
        $service = Service::create([
            'office_id' => $office->id,
            'name' => 'Birth Certificate',
            'price' => 10,
            'currency' => 'USD',
            'estimated_duration_days' => 5,
            'is_active' => true,
        ]);

        return ServiceRequest::create([
            'reference_number' => ServiceRequest::generateReference(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => $status,
            'payment_status' => 'paid',
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function test_citizen_office_page_shows_feedback_form_when_completed_request_is_eligible(): void
    {
        $citizen = $this->makeCompletedCitizen();
        $office = $this->makeOffice();
        $serviceRequest = $this->makeServiceRequest($citizen, $office, 'completed');

        $response = $this->actingAs($citizen)
            ->get(route('citizen.offices.show', $office));

        $response->assertOk();
        $response->assertSeeText('Share Your Experience');
        $response->assertSeeText($serviceRequest->reference_number);
        $response->assertSeeText('Submit Feedback');
    }

    public function test_citizen_can_submit_feedback_for_own_completed_request(): void
    {
        $citizen = $this->makeCompletedCitizen();
        $office = $this->makeOffice();
        $serviceRequest = $this->makeServiceRequest($citizen, $office, 'completed');

        $response = $this->actingAs($citizen)
            ->from(route('citizen.offices.show', $office))
            ->post(route('citizen.feedback.submit'), [
                'office_id' => $office->id,
                'service_request_id' => $serviceRequest->id,
                'rating' => 5,
                'comment' => 'Very smooth experience.',
            ]);

        $response->assertRedirect(route('citizen.offices.show', $office));
        $response->assertSessionHas('success', 'Thank you for your feedback!');

        $this->assertDatabaseHas('feedbacks', [
            'citizen_id' => $citizen->id,
            'office_id' => $office->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 5,
            'comment' => 'Very smooth experience.',
        ]);
    }

    public function test_completed_request_page_shows_existing_feedback_summary(): void
    {
        $citizen = $this->makeCompletedCitizen();
        $office = $this->makeOffice();
        $serviceRequest = $this->makeServiceRequest($citizen, $office, 'completed');

        Feedback::create([
            'citizen_id' => $citizen->id,
            'office_id' => $office->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 4,
            'comment' => 'Helpful office staff.',
            'office_reply' => 'Thank you for the kind words.',
            'reply_is_public' => true,
        ]);

        $response = $this->actingAs($citizen)
            ->get(route('citizen.requests.show', $serviceRequest));

        $response->assertOk();
        $response->assertSeeText('Your Review');
        $response->assertSeeText('Helpful office staff.');
        $response->assertSeeText('Thank you for the kind words.');
    }

    public function test_citizen_cannot_submit_feedback_for_non_completed_request(): void
    {
        $citizen = $this->makeCompletedCitizen();
        $office = $this->makeOffice();
        $serviceRequest = $this->makeServiceRequest($citizen, $office, 'pending');

        $response = $this->actingAs($citizen)
            ->from(route('citizen.requests.show', $serviceRequest))
            ->post(route('citizen.feedback.submit'), [
                'office_id' => $office->id,
                'service_request_id' => $serviceRequest->id,
                'rating' => 4,
                'comment' => 'Trying too early.',
            ]);

        $response->assertRedirect(route('citizen.requests.show', $serviceRequest));
        $response->assertSessionHasErrors([
            'feedback' => 'Feedback becomes available once the request is completed.',
        ]);
        $this->assertSame(0, Feedback::count());
    }

    public function test_feedback_table_allows_only_one_feedback_per_service_request(): void
    {
        $citizen = $this->makeCompletedCitizen();
        $office = $this->makeOffice();
        $serviceRequest = $this->makeServiceRequest($citizen, $office, 'completed');

        Feedback::create([
            'citizen_id' => $citizen->id,
            'office_id' => $office->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 5,
            'comment' => 'First review.',
        ]);

        $this->expectException(QueryException::class);

        Feedback::create([
            'citizen_id' => $citizen->id,
            'office_id' => $office->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 4,
            'comment' => 'Duplicate review.',
        ]);
    }
}
