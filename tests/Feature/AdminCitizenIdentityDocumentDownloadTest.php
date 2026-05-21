<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCitizenIdentityDocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_stream_citizen_identity_document_from_private_disk(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $path = 'id_documents/national-id.jpg';
        Storage::disk('private')->put($path, 'image-bytes');

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'id_document' => $path,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.identity.document', $citizen));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
        $this->assertSame('image-bytes', $response->streamedContent());
    }

    public function test_admin_gets_not_found_when_identity_document_is_missing_on_disk(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'id_document' => 'id_documents/missing.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.identity.document', $citizen))
            ->assertNotFound();
    }
}
