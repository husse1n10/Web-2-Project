<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $source = 'manual',
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database', 'broadcast'];

        $smsSources = ['appointment_confirmed', 'daily_scheduler'];
        if (in_array($this->source, $smsSources, true) && filled($notifiable->phone ?? null)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appt = $this->appointment;
        $date = \Carbon\Carbon::parse($appt->appointment_date)->format('l, M d, Y');
        $time = substr((string) $appt->appointment_time, 0, 5);
        $status = ucfirst($appt->status);
        $requestRef = $appt->request?->reference_number;

        $mail = (new MailMessage)
            ->subject("CedarGov — Appointment {$status} on {$date}")
            ->greeting("Hello, {$notifiable->name}")
            ->line("This is a reminder about your upcoming appointment:")
            ->line("**Date:** {$date}")
            ->line("**Time:** {$time}")
            ->line("**Status:** {$status}");

        if ($requestRef) {
            $mail->line("**Request:** {$requestRef}")
                ->action('View My Request', route('citizen.requests.show', $appt->service_request_id));
        } else {
            $mail->action('View Office Details', route('citizen.offices.show', $appt->office_id));
        }

        return $mail
            ->line('Please arrive on time and bring any required documents.')
            ->salutation('— The CedarGov Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'service_request_id' => $this->appointment->service_request_id,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->appointment_time,
            'status' => $this->appointment->status,
            'source' => $this->source,
            'message' => 'Appointment reminder for ' . $this->appointment->appointment_date . ' at ' . substr((string) $this->appointment->appointment_time, 0, 5) . '.',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toSms(object $notifiable): string
    {
        $date = (string) $this->appointment->appointment_date;
        $time = substr((string) $this->appointment->appointment_time, 0, 5);
        $status = ucfirst((string) $this->appointment->status);

        $link = $this->appointment->service_request_id
            ? route('citizen.requests.show', $this->appointment->service_request_id)
            : route('citizen.offices.show', $this->appointment->office_id);

        return "CedarGov: Appointment {$status} on {$date} at {$time}. {$link}";
    }
}
