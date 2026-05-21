<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $serviceRequestId,
        public array $messageIds,
        public int $readerId,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('request.' . $this->serviceRequestId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'service_request_id' => $this->serviceRequestId,
            'message_ids' => $this->messageIds,
            'reader_id' => $this->readerId,
        ];
    }
}
