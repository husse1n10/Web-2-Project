<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenProfileIdentityUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_citizen_can_update_identity_details_with_document_upload(): void
    {
        Storage::fake('private');

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'name' => 'Social User',
            'national_id' => null,
            'id_document' => null,
            'citizen_verification_status' => 'pending',
        ]);

        $response = $this->actingAs($citizen)->put(route('citizen.profile.update'), [
            'name' => 'Rony Abou Ezzi',
            'national_id' => '000075573653',
            'national_id_document' => UploadedFile::fake()->image('national-id.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $citizen->refresh();

        $this->assertSame('Rony Abou Ezzi', $citizen->name);
        $this->assertSame('000075573653', $citizen->national_id);
        $this->assertSame('pending', $citizen->citizen_verification_status);
        $this->assertNotNull($citizen->id_document);
        Storage::disk('private')->assertExists($citizen->id_document);
    }
}
