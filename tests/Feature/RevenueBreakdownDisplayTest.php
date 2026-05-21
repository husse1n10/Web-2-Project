<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueBreakdownDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function createOffice(): Office
    {
        $municipality = Municipality::create([
            'name' => 'Jounieh',
            'region' => 'Mount Lebanon',
        ]);

        return Office::create([
            'municipality_id' => $municipality->id,
            'name' => 'Jounieh Municipal Office',
            'address' => 'Main road',
            'is_active' => true,
        ]);
    }

    private function createPaidRequest(Office $office, string $currency, float $amount): ServiceRequest
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->id,
            'name' => $currency . ' Service',
            'price' => $amount,
            'currency' => $currency,
            'estimated_duration_days' => 3,
            'is_active' => true,
        ]);

        return ServiceRequest::create([
            'reference_number' => ServiceRequest::generateReference(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'service_name' => $service->name,
            'service_price' => $service->price,
            'service_currency' => $service->currency,
            'status' => 'completed',
            'payment_status' => 'paid',
            'amount_paid' => $amount,
            'completed_at' => now(),
        ]);
    }

    public function test_office_dashboard_shows_paid_revenue_as_currency_breakdown(): void
    {
        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $office = $this->createOffice();
        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $this->createPaidRequest($office, 'USD', 10);
        $this->createPaidRequest($office, 'LBP', 120000);

        $response = $this->actingAs($officeUser)
            ->get(route('office.dashboard'));

        $response->assertOk();
        $response->assertSeeText('LBP 120,000, $10.00');
        $response->assertDontSeeText('$120,010.00');
    }

    public function test_admin_reports_show_paid_revenue_as_currency_breakdown(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $office = $this->createOffice();

        $this->createPaidRequest($office, 'USD', 10);
        $this->createPaidRequest($office, 'LBP', 120000);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports'));

        $response->assertOk();
        $response->assertSeeText('Revenue by Currency');
        $response->assertSeeText('LBP 120,000, $10.00');
        $response->assertDontSeeText('$120,010.00');
    }

    public function test_admin_dashboard_shows_explicit_mixed_currency_revenue_trend_state(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $office = $this->createOffice();

        $this->createPaidRequest($office, 'USD', 10);
        $this->createPaidRequest($office, 'LBP', 120000);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText('LBP 120,000, $10.00');
        $response->assertSeeText('N/A');
        $response->assertSeeText('mixed currencies');
    }

    public function test_revenue_breakdown_uses_request_snapshot_after_service_is_edited(): void
    {
        $officeUser = User::factory()->create([
            'role' => 'office_user',
            'is_active' => true,
        ]);

        $office = $this->createOffice();
        $office->users()->attach($officeUser->id, ['role' => 'manager']);

        $request = $this->createPaidRequest($office, 'LBP', 120000);

        $request->service->update([
            'name' => 'Edited Service',
            'price' => 25,
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($officeUser)
            ->get(route('office.dashboard'));

        $response->assertOk();
        $response->assertSeeText('LBP 120,000');
        $response->assertDontSeeText('$120,000.00');
    }
}
