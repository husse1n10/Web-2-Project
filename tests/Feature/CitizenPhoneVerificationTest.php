<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PhoneVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CitizenPhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCitizen(): User
    {
        return User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'phone' => null,
            'phone_verified_at' => null,
        ]);
    }

    public function test_invalid_phone_otp_returns_json_error_and_does_not_verify_phone(): void
    {
        $citizen = $this->makeCitizen();

        Cache::put('phone_otp_' . $citizen->id, [
            'otp' => '123456',
            'phone' => '+96171123456',
        ], now()->addMinutes(5));

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.verify'), [
                'otp' => '654321',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'verified' => false,
                'message' => 'Invalid or expired code. Please try again.',
            ]);

        $citizen->refresh();

        $this->assertNull($citizen->phone);
        $this->assertNull($citizen->phone_verified_at);
    }

    public function test_invalid_phone_number_is_rejected_before_sending_otp(): void
    {
        Notification::fake();

        $citizen = $this->makeCitizen();

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.send'), [
                'phone' => 'abc123',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'sent' => false,
                'message' => 'Enter a valid phone number, for example +96171123456.',
            ]);

        Notification::assertNothingSent();
        $this->assertNull(Cache::get('phone_otp_' . $citizen->id));
    }

    public function test_valid_local_phone_number_is_normalized_before_sending_otp(): void
    {
        Notification::fake();

        $citizen = $this->makeCitizen();

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.send'), [
                'phone' => '071 123 456',
            ]);

        $response->assertOk()
            ->assertJson([
                'sent' => true,
                'message' => 'Verification code sent via WhatsApp.',
                'phone' => '+96171123456',
            ]);

        Notification::assertSentOnDemand(
            PhoneVerificationNotification::class,
            function (PhoneVerificationNotification $notification, array $channels, object $notifiable): bool {
                return ($notifiable->routes['sms'] ?? null) === '+96171123456';
            }
        );

        $cached = Cache::get('phone_otp_' . $citizen->id);

        $this->assertIsArray($cached);
        $this->assertSame('+96171123456', $cached['phone'] ?? null);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $cached['otp'] ?? '');
    }

    public function test_twilio_rejection_returns_json_error_and_does_not_cache_otp(): void
    {
        config()->set('services.sms.driver', 'twilio');
        config()->set('services.twilio.sid', 'ACtest');
        config()->set('services.twilio.token', 'secret');
        config()->set('services.twilio.from', '+14155238886');
        config()->set('services.twilio.channel', 'whatsapp');

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/ACtest/Messages.json' => Http::response([
                'code' => 63038,
                'message' => 'Daily message limit exceeded.',
            ], 429),
        ]);

        $citizen = $this->makeCitizen();

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.send'), [
                'phone' => '+96171123456',
            ]);

        $response->assertStatus(502)
            ->assertJson([
                'sent' => false,
                'message' => 'Twilio WhatsApp sandbox daily message limit reached. Try again tomorrow or use SMS/log mode locally.',
            ]);

        $this->assertNull(Cache::get('phone_otp_' . $citizen->id));
    }

    public function test_non_twilio_driver_in_production_returns_json_error_and_does_not_cache_otp(): void
    {
        config()->set('app.env', 'production');
        config()->set('services.sms.driver', 'log');

        $citizen = $this->makeCitizen();

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.send'), [
                'phone' => '+96171123456',
            ]);

        $response->assertStatus(502)
            ->assertJson([
                'sent' => false,
                'message' => 'WhatsApp sending is not configured on this server. Check SMS_DRIVER and Twilio settings.',
            ]);

        $this->assertNull(Cache::get('phone_otp_' . $citizen->id));
    }

    public function test_valid_phone_otp_returns_json_success_and_verifies_phone(): void
    {
        $citizen = $this->makeCitizen();

        Cache::put('phone_otp_' . $citizen->id, [
            'otp' => '123456',
            'phone' => '+96171123456',
        ], now()->addMinutes(5));

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.profile.phone.verify'), [
                'otp' => '123456',
            ]);

        $response->assertOk()
            ->assertJson([
                'verified' => true,
                'message' => 'Phone number verified successfully!',
                'phone' => '+96171123456',
            ]);

        $citizen->refresh();

        $this->assertSame('+96171123456', $citizen->phone);
        $this->assertNotNull($citizen->phone_verified_at);
        $this->assertNull(Cache::get('phone_otp_' . $citizen->id));
    }
}
