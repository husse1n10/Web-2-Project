@extends('layouts.app')
@section('title', 'Support')
@section('page-title', 'Support')

@section('content')
<div class="card citizen-reveal mb-3" data-citizen-reveal>
    <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h5 class="mb-1">Need help?</h5>
            <div class="text-muted" style="font-size:.85rem">
                Contact an administrator about account issues, profile problems, or any question that isn't tied to a specific service request.
            </div>
        </div>
        <a href="{{ route('citizen.support.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Ticket
        </a>
    </div>
</div>

<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-header">
        <span class="card-title">My Support Tickets</span>
    </div>

    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Last activity</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <div style="font-weight:600">{{ $ticket->subject }}</div>
                                @if($ticket->unread_count > 0)
                                    <span class="badge bg-primary mt-1">{{ $ticket->unread_count }} new reply</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $cls = match($ticket->status) {
                                        'open'     => 'bg-warning-subtle text-warning-emphasis',
                                        'answered' => 'bg-success-subtle text-success-emphasis',
                                        'closed'   => 'bg-secondary-subtle text-secondary-emphasis',
                                        default    => 'bg-light text-muted',
                                    };
                                @endphp
                                <span class="badge {{ $cls }}">{{ ucfirst($ticket->status) }}</span>
                            </td>
                            <td class="text-muted" style="font-size:.85rem">
                                {{ ($ticket->last_reply_at ?? $ticket->updated_at)->diffForHumans() }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('citizen.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4">
                                <x-empty-state
                                    icon="bi-life-preserver"
                                    title="No tickets yet"
                                    message="Open a support ticket if you need help with your account or profile."
                                    :action-url="route('citizen.support.create')"
                                    action-label="New Ticket"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($tickets as $ticket)
            <a href="{{ route('citizen.support.show', $ticket) }}"
               class="d-flex align-items-center justify-content-between gap-2 p-3 border-bottom text-decoration-none text-body">
                <div style="min-width:0;flex:1">
                    <div style="font-weight:600" class="text-truncate">{{ $ticket->subject }}</div>
                    <div class="text-muted" style="font-size:.75rem">
                        {{ ($ticket->last_reply_at ?? $ticket->updated_at)->diffForHumans() }}
                        @if($ticket->unread_count > 0)
                            · <span class="text-primary fw-semibold">{{ $ticket->unread_count }} new</span>
                        @endif
                    </div>
                </div>
                <span class="badge bg-light text-muted">{{ ucfirst($ticket->status) }}</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        @empty
            <div class="p-3">
                <x-empty-state
                    icon="bi-life-preserver"
                    title="No tickets yet"
                    message="Open a ticket if you need help."
                    :action-url="route('citizen.support.create')"
                    action-label="New Ticket"
                />
            </div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="p-3 border-top">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
