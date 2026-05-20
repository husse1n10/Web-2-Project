<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeServiceCurrencyDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_services_page_uses_service_currency_for_display(): void
    {
        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Beit Mery',
            'region' => 'Mount Lebanon',
        ]);

        $office = Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Beit Mery Municipality Office',
            'address' => 'Main road',
        ]);

        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        Service::create([
            'office_id' => $office->id,
            'name' => 'ID Card',
            'price' => 120000,
            'currency' => 'LBP',
            'estimated_duration_days' => 1,
        ]);

        $response = $this->actingAs($officeUser)
            ->get(route('office.services'));

        $response->assertOk();
        $response->assertSeeText('ID Card');
        $response->assertSeeText('LBP 120,000');
        $response->assertDontSeeText('$120,000.00');
    }
}
