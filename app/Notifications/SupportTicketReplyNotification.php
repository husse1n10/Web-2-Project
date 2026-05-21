<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $preview,
        public string $senderName,
        public string $senderRole,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $notifiable->isAdmin()
            ? route('admin.support.show', $this->ticket)
            : route('citizen.support.show', $this->ticket);

        $who = $this->senderRole === 'admin' ? 'an administrator' : $this->senderName;

        return (new MailMessage)
            ->subject("CedarGov — Reply on ticket #{$this->ticket->id}: {$this->ticket->subject}")
            ->greeting("Hello, {$notifiable->name}")
            ->line("You have a new reply from {$who} on your support ticket:")
            ->line("**{$this->ticket->subject}**")
            ->line("> " . $this->preview)
            ->action('View Ticket', $url)
            ->salutation('— The CedarGov Team');
    }

    public function toArray(object $notifiable): array
    {
        $url = $notifiable->isAdmin()
            ? route('admin.support.show', $this->ticket)
            : route('citizen.support.show', $this->ticket);

        return [
            'ticket_id' => $this->ticket->id,
            'subject'   => $this->ticket->subject,
            'preview'   => $this->preview,
            'sender'    => $this->senderName,
            'role'      => $this->senderRole,
            'url'       => $url,
            'message'   => "New reply on ticket: {$this->ticket->subject}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
