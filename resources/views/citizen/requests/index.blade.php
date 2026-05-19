@extends('layouts.app')
@section('title', __('My Requests'))
@section('page-title', __('My Requests'))

@section('content')
@php
    $allRequests = auth()->user()->serviceRequests;
    $statusGroups = [
        ['key' => null, 'label' => __('All')],
        ['key' => 'pending', 'label' => __('Pending')],
        ['key' => 'in_review', 'label' => __('In Review')],
        ['key' => 'approved', 'label' => __('Approved')],
        ['key' => 'completed', 'label' => __('Done')],
    ];

    $baseFilterParams = request()->except(['status', 'page']);
@endphp

<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-body citizen-requests-filter-wrap">
        <form method="GET" class="citizen-requests-filter-grid">
            <div class="citizen-filter-input-wrap">
                <i class="bi bi-search citizen-filter-input-icon"></i>
                <input
                    type="text"
                    name="search"
                    class="form-control citizen-filter-input"
                    placeholder="{{ __('Search by reference, service, or office...') }}"
                    value="{{ request('search') }}"
                >
            </div>
            <select name="status" class="form-select citizen-filter-select">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach(['pending','in_review','missing_documents','approved','rejected','completed'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ __(ucfirst(str_replace('_', ' ', $status))) }}
                    </option>
                @endforeach
            </select>
            <select name="payment_status" class="form-select citizen-filter-select">
                <option value="">{{ __('All Payments') }}</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
            </select>
            <button type="submit" class="btn btn-primary citizen-filter-btn">
                <i class="bi bi-funnel me-1"></i> {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'payment_status']))
                <a href="{{ route('citizen.requests') }}" class="btn btn-outline-secondary citizen-filter-btn">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>
</div>

<div class="citizen-request-chips citizen-reveal" data-citizen-reveal>
    @foreach($statusGroups as $group)
        @php
            $isActive = request('status') === $group['key'] || (is_null($group['key']) && !request()->filled('status'));
            $targetParams = is_null($group['key'])
                ? $baseFilterParams
                : array_merge($baseFilterParams, ['status' => $group['key']]);
            $count = is_null($group['key']) ? $allRequests->count() : $allRequests->where('status', $group['key'])->count();
        @endphp
        <a href="{{ route('citizen.requests', $targetParams) }}" class="citizen-request-chip {{ $isActive ? 'is-active' : '' }}">
            <span>{{ $group['label'] }}</span>
            <span class="citizen-request-chip-count">{{ $count }}</span>
        </a>
    @endforeach
</div>

<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div>
            <span class="card-title">{{ __('All Requests') }}</span>
            <div class="citizen-request-subtitle">{{ __('Track progress, payments, and submission details') }}</div>
        </div>
        <a href="{{ route('citizen.offices') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> {{ __('New Request') }}
        </a>
    </div>

    <div class="d-none d-md-block">
        <div class="table-responsive citizen-requests-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Office') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Chat') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td><code>{{ $req->reference_number }}</code></td>
                            <td class="citizen-request-service">{{ $req->service->name }}</td>
                            <td class="citizen-request-office">{{ $req->office->name }}</td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td><x-status-pill :status="$req->payment_status === 'paid' ? 'paid' : 'unpaid'" /></td>
                            <td class="citizen-request-date">{{ $req->created_at->format('M d, Y') }}</td>
                            <td>
                                @if(($req->unread_messages_count ?? 0) > 0)
                                    <span class="badge bg-danger">{{ $req->unread_messages_count }} {{ __('unread') }}</span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem">{{ __('No new') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('citizen.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="citizen-requests-empty-cell">
                                <x-empty-state
                                    icon="bi-inbox"
                                    :title="__('No requests found')"
                                    :message="__('Try changing filters or submit your first request.')"
                                    :action-url="route('citizen.offices')"
                                    :action-label="__('Browse Services')"
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
            <a href="{{ route('citizen.requests.show', $req) }}" class="citizen-request-mobile-item">
                <div class="citizen-request-mobile-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="citizen-request-mobile-main">
                    <div class="citizen-request-mobile-title">{{ $req->service->name }}</div>
                    <div class="citizen-request-mobile-sub">{{ $req->office->name }}</div>
                    @if(($req->unread_messages_count ?? 0) > 0)
                        <span class="badge bg-danger mt-1">{{ $req->unread_messages_count }} unread</span>
                    @endif
                    <code>{{ $req->reference_number }}</code>
                </div>
                <div class="citizen-request-mobile-status">
                    <x-status-pill :status="$req->status" />
                    @if($req->payment_status !== 'paid')
                        <x-status-pill status="unpaid" class="mt-1" />
                    @endif
                </div>
                <i class="bi bi-chevron-right citizen-request-mobile-arrow"></i>
            </a>
        @empty
            <div class="citizen-requests-empty-mobile">
                <x-empty-state
                    icon="bi-inbox"
                    :title="__('No requests yet')"
                    :message="__('Start by browsing available municipal services.')"
                    :action-url="route('citizen.offices')"
                    :action-label="__('Browse Services')"
                />
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="citizen-request-pagination">{{ $requests->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CITIZEN REQUESTS — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

body.es-role-citizen .citizen-requests-filter-wrap {
    padding: 1.1rem;
}

body.es-role-citizen .citizen-requests-filter-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 12rem 10rem auto auto;
    gap: .65rem;
    align-items: center;
}

body.es-role-citizen .citizen-filter-input-wrap {
    position: relative;
}

body.es-role-citizen .citizen-filter-input-icon {
    position: absolute;
    left: .82rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: .85rem;
    pointer-events: none;
    transition: color .2s ease;
}

body.es-role-citizen .citizen-filter-input-wrap:focus-within .citizen-filter-input-icon {
    color: #0EA5E9;
}

body.es-role-citizen .citizen-filter-input {
    padding-left: 2.3rem;
    background: rgba(255,255,255,0.6);
    border: 1.5px solid rgba(203,213,225,0.4);
    backdrop-filter: blur(4px);
}

body.es-role-citizen .citizen-filter-input:focus {
    background: rgba(255,255,255,0.9);
    border-color: #0EA5E9;
    box-shadow: 0 0 0 4px rgba(14,165,233,0.1), 0 4px 12px rgba(14,165,233,0.08);
}

body.es-role-citizen .citizen-filter-select {
    background-color: rgba(255,255,255,0.6);
    border: 1.5px solid rgba(203,213,225,0.4);
    backdrop-filter: blur(4px);
}

body.es-role-citizen .citizen-filter-select,
body.es-role-citizen .citizen-filter-btn {
    height: 2.5rem;
}

/* ── Status chips with gradient active ── */
body.es-role-citizen .citizen-request-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin: 1rem 0;
}

body.es-role-citizen .citizen-request-chip {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .36rem .78rem;
    border-radius: 999px;
    border: 1px solid rgba(191,219,254,0.6);
    background: rgba(240,249,255,0.6);
    backdrop-filter: blur(4px);
    color: #0369A1;
    font-size: .73rem;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-request-chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(14,165,233,0.15);
    color: #0369A1;
    border-color: rgba(14,165,233,0.3);
}

body.es-role-citizen .citizen-request-chip.is-active {
    color: #fff;
    border-color: transparent;
    background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 100%);
    box-shadow: 0 4px 16px rgba(14,165,233,0.3);
}

body.es-role-citizen .citizen-request-chip.is-active .citizen-request-chip-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
}

body.es-role-citizen .citizen-request-chip-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.2rem;
    border-radius: 999px;
    padding: .06rem .4rem;
    font-size: .63rem;
    background: rgba(255,255,255,0.7);
}

body.es-role-citizen .citizen-request-subtitle {
    margin-top: .18rem;
    font-size: .73rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-requests-table-wrap thead th {
    font-size: .66rem;
}

body.es-role-citizen .citizen-request-service {
    font-size: .84rem;
    font-weight: 600;
}

body.es-role-citizen .citizen-request-office,
body.es-role-citizen .citizen-request-date {
    font-size: .79rem;
    color: #64748B;
}

body.es-role-citizen .citizen-requests-empty-cell {
    padding: 2rem .8rem !important;
}

/* ── Mobile cards: glass style ── */
body.es-role-citizen .citizen-request-mobile-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem .95rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    text-decoration: none;
    color: inherit;
    transition: all .22s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-request-mobile-item:hover {
    background: rgba(224,242,254,0.25);
    color: inherit;
    transform: translateX(3px);
}

body.es-role-citizen .citizen-request-mobile-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: .8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E0F2FE, #EDE9FE);
    border: 1px solid rgba(14,165,233,0.15);
    color: #0284C7;
    flex-shrink: 0;
    font-size: 1rem;
}

body.es-role-citizen .citizen-request-mobile-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-request-mobile-title {
    font-size: .83rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-request-mobile-sub {
    margin-top: .1rem;
    color: #64748B;
    font-size: .72rem;
}

body.es-role-citizen .citizen-request-mobile-main code {
    display: inline-block;
    margin-top: .2rem;
    font-size: .67rem;
    background: rgba(14,165,233,0.06);
    padding: .1rem .35rem;
    border-radius: .25rem;
    color: #0284C7;
}

body.es-role-citizen .citizen-request-mobile-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-request-mobile-arrow {
    color: #CBD5E1;
    font-size: .82rem;
    flex-shrink: 0;
    transition: transform .2s ease;
}

body.es-role-citizen .citizen-request-mobile-item:hover .citizen-request-mobile-arrow {
    transform: translateX(3px);
    color: #0EA5E9;
}

body.es-role-citizen .citizen-requests-empty-mobile {
    padding: 1.5rem .55rem 1.6rem;
}

body.es-role-citizen .citizen-request-pagination {
    border-top: 1px solid rgba(226,232,240,0.5);
    padding: .9rem 1rem;
}

@media (max-width: 1199.98px) {
    body.es-role-citizen .citizen-requests-filter-grid {
        grid-template-columns: minmax(0, 1fr) repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    body.es-role-citizen .citizen-requests-filter-grid {
        grid-template-columns: 1fr;
    }

    body.es-role-citizen .citizen-filter-select,
    body.es-role-citizen .citizen-filter-btn {
        width: 100%;
    }
}
</style>
@endpush
