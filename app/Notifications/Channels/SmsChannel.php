<?php

namespace App\Notifications\Channels;

use App\Support\PhoneNumber;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $rawTo = $notifiable->routeNotificationFor('sms', $notification) ?? null;
        $message = $notification->toSms($notifiable);

        if (blank($rawTo) || blank($message)) {
            return;
        }

        $to = PhoneNumber::normalize((string) $rawTo);

        if (blank($to)) {
            Log::warning('SMS notification skipped due to invalid phone number.', [
                'user_id' => $notifiable->id ?? null,
                'phone' => $rawTo,
                'notification' => get_class($notification),
            ]);
            return;
        }

        $driver = (string) config('services.sms.driver', 'log');
        if ($driver !== 'twilio') {
            Log::info('SMS (log driver)', [
                'to' => $to,
                'message' => $message,
                'notification' => get_class($notification),
            ]);
            return;
        }

        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from = (string) config('services.twilio.from');
        $channel = (string) config('services.twilio.channel', 'sms');

        if (blank($sid) || blank($token) || blank($from)) {
            Log::warning('SMS driver is Twilio but keys are missing.', [
                'notification' => get_class($notification),
                'to' => $to,
            ]);
            return;
        }

        if ($channel === 'whatsapp') {
            $to = 'whatsapp:' . $to;
            $from = str_starts_with($from, 'whatsapp:') ? $from : 'whatsapp:' . $from;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->timeout(20)
                ->post($url, [
                    'To' => $to,
                    'From' => $from,
                    'Body' => (string) $message,
                ]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('Twilio SMS request failed.', [
                'notification' => get_class($notification),
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        if (!$response->successful()) {
            Log::error('Twilio SMS rejected request.', [
                'notification' => get_class($notification),
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return;
        }

        Log::info('Twilio SMS sent successfully.', [
            'notification' => get_class($notification),
            'to' => $to,
            'sid' => $response->json('sid'),
        ]);
    }
}
