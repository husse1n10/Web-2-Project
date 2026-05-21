<?php

namespace App\Events;

use App\Models\SupportTicketMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportTicketMessage $message)
    {
        $this->message->loadMissing(['sender', 'ticket']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support-ticket.' . $this->message->support_ticket_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'support-ticket.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'                => $this->message->id,
            'support_ticket_id' => $this->message->support_ticket_id,
            'sender_id'         => $this->message->sender_id,
            'body'              => $this->message->body,
            'attachment_url'    => $this->message->attachment_url,
            'attachment_name'   => $this->message->attachment_name,
            'created_at'        => optional($this->message->created_at)->toIso8601String(),
            'sender'            => [
                'id'      => $this->message->sender?->id,
                'name'    => $this->message->sender?->name,
                'is_admin'=> $this->message->sender?->isAdmin() ?? false,
            ],
        ];
    }
}
