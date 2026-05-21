<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportAttachmentController extends Controller
{
    public function download(SupportTicket $ticket, SupportTicketMessage $message)
    {
        abort_unless($message->support_ticket_id === $ticket->id, 404);

        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || ($user->isCitizen() && $ticket->user_id === $user->id),
            403
        );

        abort_if(blank($message->attachment), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($message->attachment), 404);

        return $disk->download(
            $message->attachment,
            $message->attachment_name ?: basename($message->attachment)
        );
    }
}
