<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenReadOnlyAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingCitizen(): User
    {
        return User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'national_id' => '1234567890',
            'id_document' => 'id_documents/fake.pdf',
            'citizen_verification_status' => 'pending',
        ]);
    }

    private function makeOffice(): Office
    {
        $municipality = Municipality::create([
            'name' => 'Beirut',
            'is_active' => true,
        ]);

        return Office::create([
            'name' => 'Beirut Civil Registry',
            'address' => 'Downtown Beirut',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);
    }

    public function test_pending_citizen_is_sent_to_dashboard_not_profile_from_home(): void
    {
        $citizen = $this->makePendingCitizen();

        $response = $this->actingAs($citizen)->get(route('home'));

        $response->assertRedirect(route('citizen.dashboard'));
    }

    public function test_pending_citizen_can_open_read_only_navigation_pages(): void
    {
        $citizen = $this->makePendingCitizen();
        $office = $this->makeOffice();

        $routes = [
            route('citizen.dashboard'),
            route('citizen.offices'),
            route('citizen.offices.show', $office),
            route('citizen.requests'),
            route('citizen.appointments'),
            route('citizen.payments'),
            route('citizen.support'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($citizen)->get($url)->assertOk();
        }
    }

    public function test_pending_citizen_cannot_book_a_new_appointment(): void
    {
        $citizen = $this->makePendingCitizen();
        $office = $this->makeOffice();

        $response = $this->actingAs($citizen)->post(route('citizen.appointments.book'), [
            'office_id' => $office->id,
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '10:00',
        ]);

        $response->assertRedirect(route('citizen.profile'));
    }
}
