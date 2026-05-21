<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestResubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $fromStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $req = $this->serviceRequest;
        $from = ucfirst(str_replace('_', ' ', $this->fromStatus));

        return (new MailMessage)
            ->subject("CedarGov — Request {$req->reference_number} Resubmitted")
            ->greeting("Hello, {$notifiable->name}")
            ->line("A citizen has resubmitted documents for request **{$req->reference_number}**.")
            ->line("Previous status: **{$from}** → now pending re-review.")
            ->action('Review Request', route('office.requests.show', $req))
            ->salutation('— CedarGov');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id'       => $this->serviceRequest->id,
            'reference_number' => $this->serviceRequest->reference_number,
            'status'           => $this->serviceRequest->status,
            'from_status'      => $this->fromStatus,
            'message'          => "Request #{$this->serviceRequest->reference_number} was resubmitted by the citizen.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
