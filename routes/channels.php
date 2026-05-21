<?php

use App\Models\ServiceRequest;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function (User $user, int $userId): bool {
    return $user->id === $userId;
});

Broadcast::channel('office.{officeId}', function (User $user, int $officeId): bool {
    if ($user->isAdmin()) {
        return true;
    }

    if (! $user->isOfficeUser()) {
        return false;
    }

    return $user->offices()->where('offices.id', $officeId)->exists();
});

Broadcast::channel('support-ticket.{ticketId}', function (User $user, int $ticketId): bool {
    if ($user->isAdmin()) {
        return true;
    }

    $ticket = SupportTicket::query()->select(['id', 'user_id'])->find($ticketId);

    return $ticket !== null && $ticket->user_id === $user->id;
});

Broadcast::channel('request.{requestId}', function (User $user, int $requestId): bool {
    $serviceRequest = ServiceRequest::query()
        ->select(['id', 'citizen_id', 'office_id'])
        ->find($requestId);

    if (! $serviceRequest) {
        return false;
    }

    if ($user->isAdmin() || $user->id === $serviceRequest->citizen_id) {
        return true;
    }

    if ($user->isOfficeUser()) {
        return $user->offices()->where('offices.id', $serviceRequest->office_id)->exists();
    }

    return false;
});
