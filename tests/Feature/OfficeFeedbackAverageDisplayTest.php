<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeFeedbackAverageDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_feedback_page_uses_overall_average_not_current_page_average(): void
    {
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
            'address' => 'Main street',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        for ($i = 0; $i < 15; $i++) {
            $citizen = User::factory()->create([
                'role' => 'citizen',
                'is_active' => true,
            ]);

            Feedback::create([
                'citizen_id' => $citizen->id,
                'office_id' => $office->id,
                'rating' => 5,
                'comment' => 'Great service',
                'created_at' => now()->subMinutes($i),
                'updated_at' => now()->subMinutes($i),
            ]);
        }

        $oldCitizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        Feedback::create([
            'citizen_id' => $oldCitizen->id,
            'office_id' => $office->id,
            'rating' => 1,
            'comment' => 'Older review',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($officeUser)
            ->get(route('office.feedback'));

        $response->assertOk();
        $response->assertSeeText('(16 reviews)');
        $response->assertSeeText('4.8');
        $response->assertDontSeeText('5.0');
    }
}
