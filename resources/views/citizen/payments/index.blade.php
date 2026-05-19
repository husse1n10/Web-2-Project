@extends('layouts.app')
@section('title', __('My Payments'))
@section('page-title', __('My Payments'))

@section('content')
@php
    $allRequests = auth()->user()->serviceRequests;
    $paidCount   = $allRequests->where('payment_status', 'paid')->count();
    $unpaidCount = $allRequests->where('payment_status', '!=', 'paid')->count();
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
@endphp

{{-- Summary cards --}}
<div class="row g-3 mb-3 citizen-reveal" data-citizen-reveal>
    <div class="col-6 col-md-4">
        <div class="card h-100 citizen-pay-summary-card">
            <div class="card-body text-center py-3">
                <div class="citizen-pay-summary-icon" style="background:linear-gradient(135deg,#10B981,#059669);"><i class="bi bi-check-circle"></i></div>
                <div class="citizen-pay-summary-value" style="color:#059669;" data-citizen-counter="{{ $paidCount }}">{{ $paidCount }}</div>
                <div class="citizen-pay-summary-label" style="color:#047857;">{{ __('Paid') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card h-100 citizen-pay-summary-card">
            <div class="card-body text-center py-3">
                <div class="citizen-pay-summary-icon" style="background:linear-gradient(135deg,#F97316,#EA580C);"><i class="bi bi-hourglass-split"></i></div>
                <div class="citizen-pay-summary-value" style="color:#EA580C;" data-citizen-counter="{{ $unpaidCount }}">{{ $unpaidCount }}</div>
                <div class="citizen-pay-summary-label" style="color:#C2410C;">{{ __('Unpaid') }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100 citizen-pay-summary-card">
            <div class="card-body text-center py-3">
                <div class="citizen-pay-summary-icon" style="background:linear-gradient(135deg,#0EA5E9,#6366F1);"><i class="bi bi-receipt"></i></div>
                <div class="citizen-pay-summary-value" style="color:#2563EB;" data-citizen-counter="{{ $paidCount + $unpaidCount }}">{{ $paidCount + $unpaidCount }}</div>
                <div class="citizen-pay-summary-label" style="color:#1D4ED8;">{{ __('Total Requests') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div>
            <span class="card-title">{{ __('Payment History') }}</span>
            <div class="citizen-pay-subtitle">{{ __('Track payment status for all your service requests') }}</div>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Office') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td><code>{{ $req->reference_number }}</code></td>
                            <td class="fw-semibold" style="font-size:.84rem;">{{ $req->service->name }}</td>
                            <td class="text-muted" style="font-size:.79rem;">{{ $req->office->name }}</td>
                            <td class="fw-semibold">
                                ${{ number_format($req->amount_paid ?? $req->service->price, 2) }}
                            </td>
                            <td style="font-size:.79rem;">
                                @if($req->payment_method)
                                    <i class="bi {{ $req->payment_method === 'card' ? 'bi-credit-card' : 'bi-currency-bitcoin' }} me-1"></i>
                                    {{ ucfirst($req->payment_method) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><x-status-pill :status="$req->payment_status === 'paid' ? 'paid' : 'unpaid'" /></td>
                            <td class="text-end">
                                @if($req->payment_status !== 'paid')
                                    @if($citizenActionLocked)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-lock me-1"></i> {{ __('Profile Verification Required') }}
                                        </button>
                                    @else
                                        <a href="{{ route('citizen.payment', $req) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-credit-card me-1"></i> {{ __('Pay Now') }}
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('citizen.requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> {{ __('View') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4">
                                <x-empty-state
                                    icon="bi-credit-card"
                                    :title="__('No payments yet')"
                                    :message="__('Payments appear after you submit a service request.')"
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

    {{-- Mobile cards --}}
    <div class="d-md-none">
        @forelse($requests as $req)
            <div class="citizen-pay-mobile-item">
                <div class="citizen-pay-mobile-icon {{ $req->payment_status === 'paid' ? 'is-paid' : 'is-unpaid' }}">
                    <i class="bi {{ $req->payment_status === 'paid' ? 'bi-check-circle' : 'bi-hourglass-split' }}"></i>
                </div>
                <div class="citizen-pay-mobile-main">
                    <div class="citizen-pay-mobile-title">{{ $req->service->name }}</div>
                    <div class="citizen-pay-mobile-sub">{{ $req->office->name }}</div>
                    <code style="font-size:.68rem;">{{ $req->reference_number }}</code>
                </div>
                <div class="citizen-pay-mobile-end">
                    <div class="citizen-pay-mobile-amount">${{ number_format($req->amount_paid ?? $req->service->price, 2) }}</div>
                    <x-status-pill :status="$req->payment_status === 'paid' ? 'paid' : 'unpaid'" />
                </div>
            </div>
        @empty
            <div class="p-4">
                <x-empty-state
                    icon="bi-credit-card"
                    title="No payments yet"
                    message="Payments appear after you submit a service request."
                    :action-url="route('citizen.offices')"
                    action-label="Browse Services"
                />
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="citizen-pay-pagination">{{ $requests->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CITIZEN PAYMENTS — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

body.es-role-citizen .citizen-pay-summary-card {
    border: none !important;
    background: rgba(255,255,255,0.6) !important;
    backdrop-filter: blur(12px);
    position: relative;
    overflow: hidden;
}

body.es-role-citizen .citizen-pay-summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: inherit;
}

body.es-role-citizen .citizen-pay-summary-card:nth-child(1)::before { background: linear-gradient(90deg, #10B981, #059669); }

body.es-role-citizen .citizen-pay-summary-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: .7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .9rem;
    margin-bottom: .5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

body.es-role-citizen .citizen-pay-summary-value {
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.03em;
}

body.es-role-citizen .citizen-pay-summary-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-top: .1rem;
}

body.es-role-citizen .citizen-pay-subtitle {
    margin-top: .18rem;
    font-size: .73rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-pay-mobile-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem .95rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    transition: all .22s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-pay-mobile-item:hover {
    background: rgba(224,242,254,0.2);
    transform: translateX(3px);
}

body.es-role-citizen .citizen-pay-mobile-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: .8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .95rem;
}

body.es-role-citizen .citizen-pay-mobile-icon.is-paid {
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    border: 1px solid rgba(16,185,129,0.2);
    color: #059669;
    box-shadow: 0 3px 8px rgba(16,185,129,0.12);
}

body.es-role-citizen .citizen-pay-mobile-icon.is-unpaid {
    background: linear-gradient(135deg, #FFEDD5, #FED7AA);
    border: 1px solid rgba(234,88,12,0.15);
    color: #EA580C;
    box-shadow: 0 3px 8px rgba(234,88,12,0.1);
}

body.es-role-citizen .citizen-pay-mobile-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-pay-mobile-title {
    font-size: .83rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-pay-mobile-sub {
    margin-top: .1rem;
    color: #64748B;
    font-size: .72rem;
}

body.es-role-citizen .citizen-pay-mobile-end {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
    gap: .3rem;
}

body.es-role-citizen .citizen-pay-mobile-amount {
    font-size: .88rem;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.01em;
}

body.es-role-citizen .citizen-pay-pagination {
    border-top: 1px solid rgba(226,232,240,0.5);
    padding: .9rem 1rem;
}
</style>
@endpush
