<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RequestStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ServiceRequest $serviceRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $req    = $this->serviceRequest;
        $status = ucfirst(str_replace('_', ' ', $req->status));

        $mail = (new MailMessage)
            ->subject("CedarGov — Request {$req->reference_number} Updated to {$status}")
            ->greeting("Hello, {$notifiable->name}")
            ->line("Your service request **{$req->reference_number}** has a new status update:")
            ->line("**New Status:** {$status}");

        if ($req->office_notes) {
            $mail->line("**Office Note:** {$req->office_notes}");
        }

        return $mail
            ->action('View My Request', route('citizen.requests.show', $req))
            ->line('You can track all your requests from your CedarGov dashboard.')
            ->salutation('— The CedarGov Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id'       => $this->serviceRequest->id,
            'reference_number' => $this->serviceRequest->reference_number,
            'status'           => $this->serviceRequest->status,
            'message'          => "Your request #{$this->serviceRequest->reference_number} status changed to {$this->serviceRequest->status}.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
