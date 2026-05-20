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
}
