@extends('layouts.app')
@section('title','Support Tickets')
@section('page-title','Support Tickets')

@section('content')
<div class="card mb-3 admin-reveal">
    <div class="card-body">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div style="flex:1; min-width:200px">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       value="{{ $search }}" placeholder="Subject, citizen name or email...">
            </div>
            <div style="min-width:160px">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="open"     {{ $status === 'open'     ? 'selected' : '' }}>Open ({{ $counts['open'] }})</option>
                    <option value="answered" {{ $status === 'answered' ? 'selected' : '' }}>Answered ({{ $counts['answered'] }})</option>
                    <option value="closed"   {{ $status === 'closed'   ? 'selected' : '' }}>Closed ({{ $counts['closed'] }})</option>
                </select>
            </div>
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
            @if($search || $status)
                <a href="{{ route('admin.support') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card admin-reveal">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Tickets ({{ $tickets->total() }})</span>
        <div class="d-flex gap-2">
            <span class="badge bg-warning-subtle text-warning-emphasis">{{ $counts['open'] }} open</span>
            <span class="badge bg-success-subtle text-success-emphasis">{{ $counts['answered'] }} answered</span>
            <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $counts['closed'] }} closed</span>
        </div>
    </div>

    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Citizen</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Last reply</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td><code>#{{ $ticket->id }}</code></td>
                            <td>
                                <div style="font-weight:600">{{ $ticket->user->name }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $ticket->user->email }}</div>
                            </td>
                            <td>
                                <div style="font-weight:500">{{ $ticket->subject }}</div>
                                @if($ticket->unread_admin > 0)
                                    <span class="badge bg-primary mt-1">{{ $ticket->unread_admin }} unread</span>
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
                                <a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chat-left-text me-1"></i> Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted p-4">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($tickets as $ticket)
            <a href="{{ route('admin.support.show', $ticket) }}"
               class="d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none text-body">
                <div style="min-width:0;flex:1">
                    <div style="font-weight:600" class="text-truncate">{{ $ticket->subject }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $ticket->user->name }} · #{{ $ticket->id }}</div>
                </div>
                <span class="badge bg-light text-muted">{{ ucfirst($ticket->status) }}</span>
            </a>
        @empty
            <div class="text-center text-muted p-4">No tickets found.</div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="p-3 border-top">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
