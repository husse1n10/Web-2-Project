<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_avatar_uses_public_disk_url_on_profile_page(): void
    {
        Storage::fake('public');

        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);

        $this->actingAs($citizen)
            ->post(route('citizen.profile.avatar'), [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect();

        $citizen->refresh();

        $this->assertNotNull($citizen->avatar);
        Storage::disk('public')->assertExists($citizen->avatar);

        $expectedUrl = Storage::disk('public')->url($citizen->avatar);

        $response = $this->actingAs($citizen)
            ->get(route('citizen.profile'));

        $response->assertOk();
        $response->assertSee($expectedUrl, false);
    }
}
