<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to CedarGov — Your Account is Ready')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Your CedarGov citizen account has been created successfully.')
            ->line('You can now browse municipal services, submit requests, make payments, and track your applications in real time.')
            ->action('Go to My Dashboard', route('citizen.dashboard'))
            ->line('If you have any questions, visit your nearest municipal office or contact us through the platform.')
            ->salutation('— The CedarGov Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your account was created successfully. Welcome to CedarGov Platform.',
            'type' => 'registration_confirmation',
        ];
    }
}
