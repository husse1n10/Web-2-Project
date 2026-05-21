<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketAttachmentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_owner_can_download_attachment_through_app_route(): void
    {
        Storage::fake('public');

        [$citizen, $ticket, $message] = $this->makeTicketWithAttachment();

        $response = $this->actingAs($citizen)
            ->get(route('support.attachments.download', [$ticket, $message]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('receipt.pdf', $response->headers->get('content-disposition'));

        $page = $this->actingAs($citizen)->get(route('citizen.support.show', $ticket));

        $page->assertOk();
        $page->assertSee(route('support.attachments.download', [$ticket, $message]), false);
    }

    public function test_admin_can_download_citizen_support_attachment(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        [, $ticket, $message] = $this->makeTicketWithAttachment();

        $this->actingAs($admin)
            ->get(route('support.attachments.download', [$ticket, $message]))
            ->assertOk();
    }

    public function test_other_citizen_cannot_download_someone_elses_support_attachment(): void
    {
        Storage::fake('public');

        $otherCitizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        [, $ticket, $message] = $this->makeTicketWithAttachment();

        $this->actingAs($otherCitizen)
            ->get(route('support.attachments.download', [$ticket, $message]))
            ->assertForbidden();
    }

    private function makeTicketWithAttachment(): array
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $citizen->id,
            'subject' => 'Help',
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        $path = 'support-attachments/receipt.pdf';
        Storage::disk('public')->put($path, 'pdf-bytes');

        $message = SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $citizen->id,
            'body' => 'See attached',
            'attachment' => $path,
            'attachment_name' => 'receipt.pdf',
            'attachment_size' => 867 * 1024,
        ]);

        return [$citizen, $ticket, $message];
    }
}
