@extends('layouts.app')
@section('title', 'Requests')
@section('page-title', 'Incoming Requests')

@section('content')
<div class="card mb-3 office-reveal" data-office-reveal>
    <div class="card-body">
        <form method="GET" class="office-request-filter-grid">
            <div class="office-filter-field">
                <label class="form-label">Search</label>
                <div class="office-filter-input-wrap">
                    <i class="bi bi-search office-filter-input-icon"></i>
                    <input
                        type="text"
                        name="search"
                        class="form-control office-filter-input"
                        placeholder="Reference or citizen name..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>
            <div class="office-filter-field office-filter-status">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['pending','in_review','missing_documents','approved','rejected','completed'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary office-filter-btn">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('office.requests') }}" class="btn btn-outline-secondary office-filter-btn">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card office-reveal" data-office-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <span class="card-title">Requests</span>
        <span class="office-request-total">{{ $requests->total() }} total</span>
    </div>

    <div class="d-none d-md-block">
        <div class="table-responsive office-request-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Chat</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td><code>{{ $req->reference_number }}</code></td>
                            <td>
                                <div class="office-request-citizen">{{ $req->citizen->name }}</div>
                                <div class="office-request-citizen-email">{{ $req->citizen->email }}</div>
                            </td>
                            <td class="office-request-service">{{ $req->service->name }}</td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td><x-status-pill :status="$req->payment_status === 'paid' ? 'paid' : 'unpaid'" /></td>
                            <td class="office-request-date">{{ $req->created_at->format('M d, Y') }}</td>
                            <td>
                                @if(($req->unread_messages_count ?? 0) > 0)
                                    <span class="badge bg-danger">{{ $req->unread_messages_count }} unread</span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem">No new</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('office.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="office-request-empty-cell">
                                <x-empty-state
                                    icon="bi-inbox"
                                    title="No requests found"
                                    message="Try adjusting filters or check back later."
                                    class="py-2"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($requests as $req)
            <a href="{{ route('office.requests.show', $req) }}" class="office-request-mobile-item">
                <div class="office-request-mobile-head">
                    <div class="office-request-mobile-name">{{ $req->citizen->name }}</div>
                    <x-status-pill :status="$req->status" />
                </div>
                <div class="office-request-mobile-service">{{ $req->service->name }}</div>
                @if(($req->unread_messages_count ?? 0) > 0)
                    <span class="badge bg-danger mb-1">{{ $req->unread_messages_count }} unread</span>
                @endif
                <div class="office-request-mobile-foot">
                    <code>{{ $req->reference_number }}</code>
                    <x-status-pill :status="$req->payment_status === 'paid' ? 'paid' : 'unpaid'" />
                </div>
            </a>
        @empty
            <div class="office-request-empty-mobile">
                <x-empty-state
                    icon="bi-inbox"
                    title="No requests found"
                    message="Incoming requests will appear here."
                />
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="office-request-pagination">{{ $requests->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE REQUESTS INDEX — PREMIUM GLASSMORPHISM
   ═══════════════════════════════════════════════════════ */

body.es-role-office_user .office-request-filter-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 11rem auto auto;
    gap: .62rem;
    align-items: end;
}
body.es-role-office_user .office-filter-field { min-width: 0; }
body.es-role-office_user .office-filter-status { min-width: 10rem; }

body.es-role-office_user .office-filter-input-wrap { position: relative; }
body.es-role-office_user .office-filter-input-icon {
    position: absolute; left: .78rem; top: 50%; transform: translateY(-50%);
    color: #94A3B8; font-size: .83rem; pointer-events: none;
    transition: color .22s ease;
}
body.es-role-office_user .office-filter-input-wrap:focus-within .office-filter-input-icon {
    color: #2563EB;
}
body.es-role-office_user .office-filter-input {
    padding-left: 2.2rem;
    border: 1px solid rgba(37,99,235,0.1);
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(6px);
    transition: border-color .22s ease, box-shadow .22s ease;
}
body.es-role-office_user .office-filter-input:focus {
    border-color: rgba(37,99,235,0.3);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}
body.es-role-office_user .office-filter-btn { height: 2.45rem; }

body.es-role-office_user .office-request-total {
    font-size: .75rem; color: #64748B;
}
body.es-role-office_user .office-request-citizen { font-weight: 600; font-size: .83rem; }
body.es-role-office_user .office-request-citizen-email { font-size: .72rem; color: #94A3B8; }
body.es-role-office_user .office-request-service { color: #64748B; font-size: .8rem; }
body.es-role-office_user .office-request-date { color: #94A3B8; font-size: .78rem; }
body.es-role-office_user .office-request-empty-cell { padding: 1.8rem .8rem !important; }

/* Mobile cards — glass */
body.es-role-office_user .office-request-mobile-item {
    display: block;
    padding: .9rem 1rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    text-decoration: none;
    color: inherit;
    transition: background .22s ease, transform .22s ease;
}
body.es-role-office_user .office-request-mobile-item:hover {
    background: rgba(224,242,254,0.2);
    transform: translateX(4px);
    color: inherit;
}
body.es-role-office_user .office-request-mobile-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: .35rem; gap: .6rem;
}
body.es-role-office_user .office-request-mobile-name { font-size: .84rem; font-weight: 700; color: #0F172A; }
body.es-role-office_user .office-request-mobile-service { font-size: .75rem; color: #64748B; margin-bottom: .24rem; }
body.es-role-office_user .office-request-mobile-foot { display: flex; justify-content: space-between; align-items: center; gap: .45rem; }
body.es-role-office_user .office-request-empty-mobile { padding: 1.25rem .55rem 1.4rem; }
body.es-role-office_user .office-request-pagination { padding: .75rem 1rem; border-top: 1px solid rgba(226,232,240,0.5); }

@media (max-width: 991.98px) {
    body.es-role-office_user .office-request-filter-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
}
@media (max-width: 767.98px) {
    body.es-role-office_user .office-request-filter-grid { grid-template-columns: 1fr; }
    body.es-role-office_user .office-filter-btn { width: 100%; }
}
</style>
@endpush
