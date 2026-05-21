<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['sender', 'request']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('request.' . $this->message->service_request_id),
            new PrivateChannel('user.' . $this->message->request->citizen_id),
            new PrivateChannel('office.' . $this->message->request->office_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'service_request_id' => $this->message->service_request_id,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->body,
            'created_at' => optional($this->message->created_at)->toIso8601String(),
            'citizen_id' => $this->message->request?->citizen_id,
            'office_id' => $this->message->request?->office_id,
            'sender' => [
                'id' => $this->message->sender?->id,
                'name' => $this->message->sender?->name,
            ],
        ];
    }
}
