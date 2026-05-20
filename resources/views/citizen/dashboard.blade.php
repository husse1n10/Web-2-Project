@extends('layouts.app')
@section('title', __('Citizen Dashboard'))
@section('page-title', __('Citizen Dashboard'))

@section('content')
@php
    $user = auth()->user();
    $allRequests = $user->serviceRequests;
    $activeRequests = $allRequests->whereIn('status', ['pending', 'in_review', 'missing_documents', 'approved']);
    $recentNotifications = $user->notifications()->latest()->take(5)->get();
    $upcomingAppointments = $upcomingAppointments
        ?? $user->appointments()
            ->with('office')
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

    $completedCount = $allRequests->where('status', 'completed')->count();
    $totalCount = max(1, $allRequests->count());
    $completionRate = (int) round(($completedCount / $totalCount) * 100);
    $missingFields = $user->missingCitizenProfileFields();

    $profileChecks = [
        filled($user->name),
        filled($user->email),
        filled($user->phone),
        filled($user->national_id),
        filled($user->id_document),
    ];
    $profileCompletion = (int) round((collect($profileChecks)->filter()->count() / count($profileChecks)) * 100);
@endphp

<div class="card citizen-hero-card citizen-reveal" data-citizen-reveal>
    <div class="card-body">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-xl-8">
                <span class="citizen-hero-eyebrow">{{ __('Citizen Workspace') }}</span>
                <h2 class="citizen-hero-title">{{ __('Welcome back,') }} {{ \Illuminate\Support\Str::before($user->name, ' ') }}.</h2>
                <p class="citizen-hero-copy">
                    {{ __('Track requests, complete pending actions, and keep your profile ready so submissions stay fast.') }}
                </p>

                <div class="citizen-progress-wrap">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="citizen-progress-label">{{ __('Request completion progress') }}</span>
                        <span class="citizen-progress-value">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress citizen-progress-bar" role="progressbar" aria-label="{{ __('Request completion progress') }}" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>

                @if(!empty($missingFields))
                    <div class="citizen-inline-alert mt-3">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ __('Profile incomplete:') }} {{ implode(', ', $missingFields) }}</span>
                    </div>
                @else
                    <div class="citizen-inline-alert is-success mt-3">
                        <i class="bi bi-check2-circle"></i>
                        <span>{{ __('Your profile is complete and ready for new requests.') }}</span>
                    </div>
                @endif
            </div>
            <div class="col-12 col-xl-4">
                <div class="citizen-hero-panel">
                    <div class="citizen-hero-orb citizen-hero-orb-one"></div>
                    <div class="citizen-hero-orb citizen-hero-orb-two"></div>
                    <div class="citizen-hero-meta">
                        <div class="citizen-hero-meta-label">{{ __('Profile readiness') }}</div>
                        <div class="citizen-hero-meta-value" data-citizen-counter="{{ $profileCompletion }}">{{ $profileCompletion }}</div>
                        <div class="citizen-hero-meta-suffix">% {{ __('complete') }}</div>
                    </div>
                    <a href="{{ route('citizen.profile') }}" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-person-check me-1"></i> {{ __('Update Profile') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card citizen-kpi-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="citizen-kpi-label">{{ __('Active') }}</span>
                        <h3 class="citizen-kpi-value" data-citizen-counter="{{ $activeRequests->count() }}">{{ $activeRequests->count() }}</h3>
                    </div>
                    <span class="stat-card-icon bg-teal"><i class="bi bi-activity"></i></span>
                </div>
                <div class="citizen-kpi-sub">{{ __('Currently in progress') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card citizen-kpi-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="citizen-kpi-label">{{ __('Pending') }}</span>
                        <h3 class="citizen-kpi-value" data-citizen-counter="{{ $allRequests->where('status', 'pending')->count() }}">{{ $allRequests->where('status', 'pending')->count() }}</h3>
                    </div>
                    <span class="stat-card-icon bg-amber"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="citizen-kpi-sub">{{ __('Waiting to be reviewed') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card citizen-kpi-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="citizen-kpi-label">{{ __('Completed') }}</span>
                        <h3 class="citizen-kpi-value" data-citizen-counter="{{ $completedCount }}">{{ $completedCount }}</h3>
                    </div>
                    <span class="stat-card-icon bg-emerald"><i class="bi bi-check-circle"></i></span>
                </div>
                <div class="citizen-kpi-sub">{{ __('Finished successfully') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card citizen-kpi-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="citizen-kpi-label">{{ __('Unpaid') }}</span>
                        <h3 class="citizen-kpi-value" data-citizen-counter="{{ $allRequests->where('payment_status', '!=', 'paid')->count() }}">{{ $allRequests->where('payment_status', '!=', 'paid')->count() }}</h3>
                    </div>
                    <span class="stat-card-icon bg-rose"><i class="bi bi-credit-card"></i></span>
                </div>
                <div class="citizen-kpi-sub">{{ __('Need payment action') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-body py-3">
                <div class="citizen-actions-grid">
                    <a href="{{ route('citizen.offices') }}" class="citizen-action-link">
                        <span class="citizen-action-icon"><i class="bi bi-search"></i></span>
                        <span>
                            <span class="citizen-action-title">{{ __('Browse Services') }}</span>
                            <span class="citizen-action-sub">{{ __('Find office services near you') }}</span>
                        </span>
                    </a>
                    <a href="{{ route('citizen.requests') }}" class="citizen-action-link">
                        <span class="citizen-action-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span>
                            <span class="citizen-action-title">{{ __('My Requests') }}</span>
                            <span class="citizen-action-sub">{{ __('Track and manage submissions') }}</span>
                        </span>
                    </a>
                    <a href="{{ route('citizen.requests') }}?payment_status=unpaid" class="citizen-action-link">
                        <span class="citizen-action-icon"><i class="bi bi-credit-card-2-front"></i></span>
                        <span>
                            <span class="citizen-action-title">{{ __('Complete Payments') }}</span>
                            <span class="citizen-action-sub">{{ __('Resolve unpaid requests quickly') }}</span>
                        </span>
                    </a>
                    <a href="{{ route('citizen.profile') }}" class="citizen-action-link">
                        <span class="citizen-action-icon"><i class="bi bi-person-vcard"></i></span>
                        <span>
                            <span class="citizen-action-title">{{ __('Profile') }}</span>
                            <span class="citizen-action-sub">{{ __('Keep your account up to date') }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card h-100 citizen-reveal" data-citizen-reveal>
            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <div>
                    <h6 class="card-title">{{ __('Active Requests') }}</h6>
                    <small class="text-muted">{{ __('Most recent requests with current status') }}</small>
                </div>
                @if($requests->count() > 8)
                    <a href="{{ route('citizen.requests') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($requests->count())
                    <div class="table-responsive citizen-table-wrap">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Service') }}</th>
                                    <th>{{ __('Office') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Payment') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests->take(8) as $request)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $request->reference_number }}</span></td>
                                        <td>{{ $request->resolved_service_name }}</td>
                                        <td>{{ $request->office->name }}</td>
                                        <td><x-status-pill :status="$request->status" /></td>
                                        <td><x-status-pill :status="$request->payment_status" /></td>
                                        <td class="text-end">
                                            <a href="{{ route('citizen.requests.show', $request) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center citizen-empty">
                        <div class="mb-2"><i class="bi bi-inbox"></i></div>
                        <div class="fw-semibold mb-1">{{ __('No service requests yet') }}</div>
                        <div class="text-muted mb-3">{{ __('Start by browsing available municipality services.') }}</div>
                        <a href="{{ route('citizen.offices') }}" class="btn btn-sm btn-primary">{{ __('Browse services') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100 citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <h6 class="card-title">{{ __('Upcoming Appointments') }}</h6>
                <small class="text-muted">{{ __('Scheduled visits and timings') }}</small>
            </div>
            <div class="card-body">
                @if($upcomingAppointments->count())
                    <div class="d-flex flex-column gap-2">
                        @foreach($upcomingAppointments as $appointment)
                            <div class="citizen-appointment-card">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $appointment->office->name }}</span>
                                    <x-status-pill :status="$appointment->status" />
                                </div>
                                <div class="text-muted citizen-appointment-time">
                                    <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                    <span class="mx-1">&middot;</span>
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted text-center citizen-appointment-empty">{{ __('No upcoming appointments.') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <h6 class="card-title">{{ __('Recent Notifications') }}</h6>
                <small class="text-muted">{{ __('Latest activity and system messages') }}</small>
            </div>
            <div class="card-body">
                @if($recentNotifications->count())
                    <div class="list-group list-group-flush">
                        @foreach($recentNotifications as $notification)
                            <div class="list-group-item citizen-note-item px-0 d-flex justify-content-between align-items-start bg-transparent border-bottom">
                                <div>
                                    <div class="fw-semibold citizen-note-title">{{ $notification->data['message'] ?? __('Notification received') }}</div>
                                    <div class="text-muted citizen-note-time">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                                @if(is_null($notification->read_at))
                                    <span class="badge rounded-pill citizen-note-badge">{{ __('new') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted text-center citizen-note-empty">{{ __('No notifications to display.') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CITIZEN DASHBOARD — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

/* ── Hero card: animated gradient mesh + glassmorphism ── */
body.es-role-citizen .citizen-hero-card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.5);
    background:
        radial-gradient(ellipse at 20% -20%, rgba(56,189,248,0.25) 0%, transparent 55%),
        radial-gradient(ellipse at 80% 120%, rgba(99,102,241,0.18) 0%, transparent 55%),
        radial-gradient(ellipse at 50% 50%, rgba(16,185,129,0.08) 0%, transparent 60%),
        rgba(255,255,255,0.65);
    backdrop-filter: blur(16px) saturate(1.5);
    -webkit-backdrop-filter: blur(16px) saturate(1.5);
    box-shadow: 0 8px 32px rgba(14,165,233,0.08), 0 1px 2px rgba(15,23,42,0.04);
}

body.es-role-citizen .citizen-hero-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: linear-gradient(135deg, transparent 30%, rgba(56,189,248,0.06) 50%, transparent 70%);
    animation: citizenHeroSweep 8s ease-in-out infinite;
    pointer-events: none;
}

@keyframes citizenHeroSweep {
    0%, 100% { transform: translateX(-30%) rotate(25deg); opacity: 0; }
    50% { transform: translateX(30%) rotate(25deg); opacity: 1; }
}

body.es-role-citizen .citizen-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    box-shadow: 0 2px 10px rgba(14,165,233,0.3);
}

body.es-role-citizen .citizen-hero-title {
    margin: 1rem 0 .5rem;
    font-size: clamp(1.5rem, 2.8vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.15;
    background: linear-gradient(135deg, #0F172A 0%, #0EA5E9 60%, #6366F1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-citizen .citizen-hero-copy {
    margin: 0;
    color: #475569;
    font-size: .88rem;
    max-width: 42rem;
    line-height: 1.6;
}

body.es-role-citizen .citizen-progress-wrap {
    margin-top: 1.2rem;
}

body.es-role-citizen .citizen-progress-label {
    font-size: .72rem;
    color: #475569;
    font-weight: 600;
}

body.es-role-citizen .citizen-progress-value {
    font-size: .77rem;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
}

body.es-role-citizen .citizen-progress-bar {
    height: .55rem;
    border-radius: 999px;
    background: rgba(219,234,254,0.6);
    overflow: hidden;
}

body.es-role-citizen .citizen-progress-bar .progress-bar {
    border-radius: 999px;
    background: linear-gradient(90deg, #06B6D4 0%, #0EA5E9 40%, #6366F1 100%);
    position: relative;
    overflow: hidden;
    animation: citizenProgressGrow .8s cubic-bezier(.4,0,.2,1) forwards;
}

body.es-role-citizen .citizen-progress-bar .progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
    animation: citizenBarShimmer 2s ease-in-out infinite 1s;
}

@keyframes citizenProgressGrow {
    from { width: 0 !important; }
}

@keyframes citizenBarShimmer {
    0% { left: -100%; }
    100% { left: 200%; }
}

body.es-role-citizen .citizen-inline-alert {
    display: inline-flex;
    align-items: center;
    gap: .42rem;
    border-radius: 999px;
    padding: .34rem .72rem;
    font-size: .74rem;
    font-weight: 600;
    background: rgba(255,247,237,0.8);
    color: #9A3412;
    border: 1px solid #FED7AA;
    backdrop-filter: blur(4px);
}

body.es-role-citizen .citizen-inline-alert.is-success {
    background: rgba(236,253,245,0.8);
    border-color: #A7F3D0;
    color: #047857;
}

/* ── Hero panel (profile readiness) ── */
body.es-role-citizen .citizen-hero-panel {
    position: relative;
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 1.2rem;
    padding: 1.2rem;
    background: linear-gradient(150deg, rgba(224,242,254,0.5) 0%, rgba(238,242,255,0.6) 100%);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 12px 32px rgba(14,165,233,0.12), inset 0 1px 0 rgba(255,255,255,0.5);
    overflow: hidden;
}

body.es-role-citizen .citizen-hero-orb {
    position: absolute;
    border-radius: 999px;
    filter: blur(2px);
    animation: citizenFloat 6s ease-in-out infinite;
}

body.es-role-citizen .citizen-hero-orb-one {
    width: 7rem;
    height: 7rem;
    top: -3rem;
    right: -2.5rem;
    background: radial-gradient(circle, rgba(14,165,233,0.35) 0%, rgba(99,102,241,0.15) 100%);
}

body.es-role-citizen .citizen-hero-orb-two {
    width: 5.5rem;
    height: 5.5rem;
    bottom: -2.5rem;
    left: -1.8rem;
    background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, rgba(14,165,233,0.1) 100%);
    animation-delay: .8s;
}

@keyframes citizenFloat {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-10px) scale(1.06); }
}

body.es-role-citizen .citizen-hero-meta {
    position: relative;
    z-index: 2;
}

body.es-role-citizen .citizen-hero-meta-label {
    font-size: .7rem;
    color: #64748B;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
}

body.es-role-citizen .citizen-hero-meta-value {
    margin-top: .3rem;
    line-height: 1;
    font-size: 2.4rem;
    letter-spacing: -0.04em;
    font-weight: 800;
    background: linear-gradient(135deg, #0F172A 0%, #0EA5E9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-citizen .citizen-hero-meta-suffix {
    margin-top: .25rem;
    font-size: .77rem;
    color: #64748B;
    font-weight: 500;
}

/* ── KPI cards: each with unique gradient accent ── */
body.es-role-citizen .citizen-kpi-card {
    border-radius: 1rem;
    border: 1px solid rgba(255,255,255,0.5);
    position: relative;
    overflow: hidden;
}

body.es-role-citizen .citizen-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 1rem 1rem 0 0;
}

body.es-role-citizen .col-6:nth-child(1) .citizen-kpi-card::before {
    background: linear-gradient(90deg, #06B6D4, #0EA5E9);
}
body.es-role-citizen .col-6:nth-child(2) .citizen-kpi-card::before {
    background: linear-gradient(90deg, #F59E0B, #F97316);
}
body.es-role-citizen .col-6:nth-child(3) .citizen-kpi-card::before {
    background: linear-gradient(90deg, #10B981, #059669);
}
body.es-role-citizen .col-6:nth-child(4) .citizen-kpi-card::before {
    background: linear-gradient(90deg, #F43F5E, #E11D48);
}

body.es-role-citizen .citizen-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(14,165,233,0.1);
}

body.es-role-citizen .citizen-kpi-label {
    display: block;
    margin-bottom: .2rem;
    color: #64748B;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
}

body.es-role-citizen .citizen-kpi-value {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0F172A;
}

body.es-role-citizen .citizen-kpi-sub {
    margin-top: .25rem;
    font-size: .72rem;
    color: #94A3B8;
}

body.es-role-citizen .stat-card-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* ── Quick actions: glass cards with gradient hover ── */
body.es-role-citizen .citizen-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
}

body.es-role-citizen .citizen-action-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .55rem;
    border-radius: 1rem;
    padding: 1rem .8rem;
    border: 1px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(8px);
    color: #0F172A;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    transition: all .3s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-action-link::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 1rem;
    opacity: 0;
    background: linear-gradient(135deg, rgba(14,165,233,0.08) 0%, rgba(99,102,241,0.08) 100%);
    transition: opacity .3s ease;
}

body.es-role-citizen .citizen-action-link:hover {
    transform: translateY(-4px);
    border-color: rgba(14,165,233,0.3);
    box-shadow: 0 16px 32px rgba(14,165,233,0.12), 0 0 0 1px rgba(14,165,233,0.1);
    color: #0F172A;
}

body.es-role-citizen .citizen-action-link:hover::before {
    opacity: 1;
}

body.es-role-citizen .citizen-action-icon {
    width: 2.8rem;
    height: 2.8rem;
    border-radius: .85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #fff;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    box-shadow: 0 6px 16px rgba(14,165,233,0.3);
    flex-shrink: 0;
    transition: all .3s ease;
}

body.es-role-citizen .citizen-action-link:hover .citizen-action-icon {
    transform: scale(1.1);
    box-shadow: 0 8px 24px rgba(14,165,233,0.4);
}

body.es-role-citizen .citizen-action-title {
    display: block;
    font-size: .8rem;
    font-weight: 700;
    line-height: 1.2;
}

body.es-role-citizen .citizen-action-sub {
    display: block;
    margin-top: .1rem;
    font-size: .68rem;
    color: #94A3B8;
}

/* ── Active requests table ── */
body.es-role-citizen .citizen-table-wrap thead th {
    font-size: .66rem;
}

body.es-role-citizen .citizen-empty i {
    font-size: 2.2rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-empty .fw-semibold {
    font-size: .85rem;
}

body.es-role-citizen .citizen-empty .text-muted {
    font-size: .78rem;
}

/* ── Appointment cards ── */
body.es-role-citizen .citizen-appointment-card {
    border-radius: .85rem;
    border: 1px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(6px);
    padding: .72rem .82rem;
    transition: all .25s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(14,165,233,0.1);
    border-color: rgba(14,165,233,0.2);
}

body.es-role-citizen .citizen-appointment-card .fw-semibold {
    font-size: .81rem;
}

body.es-role-citizen .citizen-appointment-time {
    font-size: .72rem;
    color: #64748B;
}

body.es-role-citizen .citizen-appointment-empty,
body.es-role-citizen .citizen-note-empty {
    font-size: .84rem;
    color: #94A3B8;
}

/* ── Notification items ── */
body.es-role-citizen .citizen-note-item {
    transition: all .2s ease;
    border-radius: .5rem;
    padding: .65rem .2rem !important;
    margin: 0 -.2rem;
}

body.es-role-citizen .citizen-note-item:hover {
    background: rgba(224,242,254,0.3) !important;
    transform: translateX(2px);
}

body.es-role-citizen .citizen-note-title {
    font-size: .84rem;
}

body.es-role-citizen .citizen-note-time {
    font-size: .72rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-note-badge {
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff;
    border: none;
    font-size: .6rem;
    font-weight: 700;
    padding: .18rem .48rem;
    box-shadow: 0 2px 8px rgba(14,165,233,0.3);
    animation: citizenBadgePulse 2s ease-in-out infinite;
}

@keyframes citizenBadgePulse {
    0%, 100% { box-shadow: 0 2px 8px rgba(14,165,233,0.3); }
    50% { box-shadow: 0 2px 16px rgba(14,165,233,0.5); }
}

/* ── Responsive ── */
@media (max-width: 991.98px) {
    body.es-role-citizen .citizen-actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    body.es-role-citizen .citizen-actions-grid {
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
    }
    body.es-role-citizen .citizen-action-link {
        padding: .75rem .5rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    body.es-role-citizen .citizen-hero-orb,
    body.es-role-citizen .citizen-hero-card::before,
    body.es-role-citizen .citizen-progress-bar .progress-bar::after,
    body.es-role-citizen .citizen-note-badge {
        animation: none !important;
    }
}
</style>
@endpush
