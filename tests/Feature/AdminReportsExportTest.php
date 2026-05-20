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

class AdminReportsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_reports_pdf(): void
    {
        Carbon::setTestNow('2026-05-20 08:30:00');

        try {
            $admin = User::factory()->create([
                'role' => 'admin',
                'is_active' => true,
            ]);

            $citizen = User::factory()->create([
                'role' => 'citizen',
                'is_active' => true,
            ]);

            $municipality = Municipality::create([
                'name' => 'Beirut',
                'region' => 'Beirut',
            ]);

            $office = Office::create([
                'municipality_id' => $municipality->id,
                'name' => 'Ras Beirut Services Office',
                'address' => 'Beirut',
            ]);

            $service = Service::create([
                'office_id' => $office->id,
                'name' => 'Birth Certificate Issuance',
                'price' => 15,
            ]);

            ServiceRequest::create([
                'reference_number' => 'SRQ-2026-PDFTEST',
                'citizen_id' => $citizen->id,
                'service_id' => $service->id,
                'office_id' => $office->id,
                'status' => 'completed',
                'amount_paid' => 15,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'completed_at' => now(),
            ]);

            $response = $this->actingAs($admin)->get(route('admin.reports.export.pdf'));

            $response->assertOk();
            $this->assertStringStartsWith('application/pdf', $response->headers->get('content-type'));
            $this->assertStringContainsString(
                'reports-2026-05-20.pdf',
                $response->headers->get('content-disposition')
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_csv_exports_format_amounts_using_each_service_currency(): void
    {
        Carbon::setTestNow('2026-05-20 08:30:00');

        try {
            $admin = User::factory()->create([
                'role' => 'admin',
                'is_active' => true,
            ]);

            $citizen = User::factory()->create([
                'role' => 'citizen',
                'is_active' => true,
            ]);

            $municipality = Municipality::create([
                'name' => 'Metn',
                'region' => 'Mount Lebanon',
            ]);

            $office = Office::create([
                'municipality_id' => $municipality->id,
                'name' => 'Metn Citizen Service Office',
                'address' => 'Metn',
            ]);

            $lbpService = Service::create([
                'office_id' => $office->id,
                'name' => 'Family Record',
                'price' => 120000,
                'currency' => 'LBP',
                'estimated_duration_days' => 2,
                'is_active' => true,
            ]);

            $usdService = Service::create([
                'office_id' => $office->id,
                'name' => 'Civil Extract',
                'price' => 15.5,
                'currency' => 'USD',
                'estimated_duration_days' => 2,
                'is_active' => true,
            ]);

            ServiceRequest::create([
                'reference_number' => 'SRQ-2026-LBPTEST',
                'citizen_id' => $citizen->id,
                'service_id' => $lbpService->id,
                'office_id' => $office->id,
                'service_name' => $lbpService->name,
                'service_price' => $lbpService->price,
                'service_currency' => $lbpService->currency,
                'status' => 'completed',
                'amount_paid' => 120000,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'completed_at' => now(),
            ]);

            ServiceRequest::create([
                'reference_number' => 'SRQ-2026-USDTEST',
                'citizen_id' => $citizen->id,
                'service_id' => $usdService->id,
                'office_id' => $office->id,
                'service_name' => $usdService->name,
                'service_price' => $usdService->price,
                'service_currency' => $usdService->currency,
                'status' => 'completed',
                'amount_paid' => 15.5,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'completed_at' => now(),
            ]);

            $lbpService->update([
                'name' => 'Updated Family Record',
                'price' => 25,
                'currency' => 'USD',
            ]);

            $usdService->update([
                'name' => 'Updated Civil Extract',
                'price' => 20,
                'currency' => 'EUR',
            ]);

            $requestsCsv = $this->actingAs($admin)
                ->get(route('admin.reports.export', 'requests'));

            $requestsCsv->assertOk();
            $requestsContent = $requestsCsv->streamedContent();
            $this->assertStringContainsString('Currency', $requestsContent);
            $this->assertStringContainsString('Quoted Amount', $requestsContent);
            $this->assertStringContainsString('Family Record', $requestsContent);
            $this->assertStringContainsString('Civil Extract', $requestsContent);
            $this->assertStringContainsString('LBP,"120,000"', $requestsContent);
            $this->assertStringContainsString('USD,15.50', $requestsContent);

            $paymentsCsv = $this->actingAs($admin)
                ->get(route('admin.reports.export', 'payments'));

            $paymentsCsv->assertOk();
            $paymentsContent = $paymentsCsv->streamedContent();
            $this->assertStringContainsString('Currency', $paymentsContent);
            $this->assertStringContainsString('Amount', $paymentsContent);
            $this->assertStringContainsString('Paid At', $paymentsContent);
            $this->assertStringContainsString('LBP,"120,000"', $paymentsContent);
            $this->assertStringContainsString('USD,15.50', $paymentsContent);
        } finally {
            Carbon::setTestNow();
        }
    }
}
