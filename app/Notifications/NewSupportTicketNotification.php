<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $preview,
        public string $citizenName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("CedarGov — New support ticket #{$this->ticket->id}")
            ->greeting("Hello, {$notifiable->name}")
            ->line("**{$this->citizenName}** opened a new support ticket:")
            ->line("**{$this->ticket->subject}**")
            ->line("> " . $this->preview)
            ->action('Open in admin inbox', route('admin.support.show', $this->ticket))
            ->salutation('— The CedarGov Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject'   => $this->ticket->subject,
            'preview'   => $this->preview,
            'citizen'   => $this->citizenName,
            'url'       => route('admin.support.show', $this->ticket),
            'message'   => "New ticket from {$this->citizenName}: {$this->ticket->subject}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
