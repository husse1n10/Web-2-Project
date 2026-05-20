<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OfficeDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_this_month_uses_completed_at_in_current_year(): void
    {
        Carbon::setTestNow('2026-05-20 10:00:00');

        try {
            $officeUser = User::factory()->create([
                'role' => 'office_user',
                'is_active' => true,
            ]);

            $municipality = Municipality::create([
                'name' => 'Baabda',
                'region' => 'Mount Lebanon',
            ]);

            $office = Office::create([
                'municipality_id' => $municipality->id,
                'name' => 'Baabda Municipal Office',
                'address' => 'Main road',
            ]);

            $office->users()->attach($officeUser->id, ['role' => 'manager']);

            $service = Service::create([
                'office_id' => $office->id,
                'name' => 'Residence Certificate',
                'price' => 10,
                'currency' => 'USD',
                'estimated_duration_days' => 2,
                'is_active' => true,
            ]);

            $citizen = User::factory()->create([
                'role' => 'citizen',
                'is_active' => true,
            ]);

            $currentMonthRequest = ServiceRequest::create([
                'reference_number' => 'SRQ-2026-OFFICEA',
                'citizen_id' => $citizen->id,
                'service_id' => $service->id,
                'office_id' => $office->id,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'completed_at' => Carbon::parse('2026-05-19 12:00:00'),
            ]);

            $currentMonthRequest->forceFill([
                'created_at' => Carbon::parse('2026-05-05 09:00:00'),
                'updated_at' => Carbon::parse('2026-05-19 12:00:00'),
            ])->saveQuietly();

            $previousYearRequest = ServiceRequest::create([
                'reference_number' => 'SRQ-2026-OFFICEB',
                'citizen_id' => $citizen->id,
                'service_id' => $service->id,
                'office_id' => $office->id,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'completed_at' => Carbon::parse('2025-05-10 10:00:00'),
            ]);

            $previousYearRequest->forceFill([
                'created_at' => Carbon::parse('2025-05-01 09:00:00'),
                'updated_at' => Carbon::parse('2026-05-20 10:00:00'),
            ])->saveQuietly();

            $response = $this->actingAs($officeUser)
                ->get(route('office.dashboard'));

            $response->assertOk();
            $response->assertSeeText('Completed This Month');
            $response->assertSee('data-office-counter="1"', false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
