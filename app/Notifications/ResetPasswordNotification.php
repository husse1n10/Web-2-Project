<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('CedarGov — Password Reset Request')
            ->greeting("Hello, {$notifiable->name}")
            ->line('We received a request to reset the password for your CedarGov account.')
            ->line('Click the button below to choose a new password:')
            ->action('Reset My Password', $url)
            ->line('This link will expire in 60 minutes. If you did not request this, you can safely ignore this email — your password will remain unchanged.')
            ->salutation('— The CedarGov Team');
    }
}
