@extends('layouts.app')
@section('title', 'Office Dashboard')
@section('page-title', 'Office Dashboard')

@section('content')
<div class="card office-hero-card office-reveal" data-office-reveal>
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-xl-8">
                <span class="office-hero-eyebrow">Office Workspace</span>
                <h2 class="office-hero-title">{{ $office->name }}</h2>
                <p class="office-hero-copy">
                    Manage incoming citizen requests, appointments, and service quality from one focused workspace.
                </p>
            </div>
            <div class="col-12 col-xl-4">
                <div class="office-hero-panel">
                    <div class="office-hero-panel-label">Completed This Month</div>
                    <div class="office-hero-panel-value" data-office-counter="{{ $stats['completed_this_month'] }}">{{ $stats['completed_this_month'] }}</div>
                    <div class="office-hero-panel-sub">Revenue: {{ $stats['revenue_breakdown'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card office-kpi-card office-reveal" data-office-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="office-kpi-label">Pending</span>
                        <h3 class="office-kpi-value" data-office-counter="{{ $stats['pending'] }}">{{ $stats['pending'] }}</h3>
                    </div>
                    <span class="stat-card-icon bg-amber"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <div class="office-kpi-sub">Waiting review</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card office-kpi-card office-reveal" data-office-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="office-kpi-label">In Review</span>
                        <h3 class="office-kpi-value" data-office-counter="{{ $stats['in_review'] }}">{{ $stats['in_review'] }}</h3>
                    </div>
                    <span class="stat-card-icon bg-sky"><i class="bi bi-search"></i></span>
                </div>
                <div class="office-kpi-sub">Currently processed</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card office-kpi-card office-reveal" data-office-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="office-kpi-label">Today Incoming</span>
                        <h3 class="office-kpi-value" data-office-counter="{{ $stats['pending_today'] }}">{{ $stats['pending_today'] }}</h3>
                    </div>
                    <span class="stat-card-icon bg-teal"><i class="bi bi-inbox"></i></span>
                </div>
                <div class="office-kpi-sub">New today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card office-kpi-card office-reveal" data-office-reveal>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="office-kpi-label">Avg Rating</span>
                        <h3 class="office-kpi-value">{{ number_format($stats['avg_rating'], 1) }}</h3>
                    </div>
                    <span class="stat-card-icon bg-violet"><i class="bi bi-star"></i></span>
                </div>
                <div class="office-kpi-sub">Citizen feedback</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card office-reveal" data-office-reveal>
            <div class="card-body py-3">
                <div class="office-actions-grid">
                    <a href="{{ route('office.requests') }}" class="office-action-link">
                        <span class="office-action-icon"><i class="bi bi-inbox"></i></span>
                        <span>
                            <span class="office-action-title">Open Requests</span>
                            <span class="office-action-sub">Review incoming submissions</span>
                        </span>
                    </a>
                    <a href="{{ route('office.services') }}" class="office-action-link">
                        <span class="office-action-icon"><i class="bi bi-grid"></i></span>
                        <span>
                            <span class="office-action-title">Manage Services</span>
                            <span class="office-action-sub">Edit pricing and durations</span>
                        </span>
                    </a>
                    <a href="{{ route('office.appointments') }}" class="office-action-link">
                        <span class="office-action-icon"><i class="bi bi-calendar-check"></i></span>
                        <span>
                            <span class="office-action-title">Appointments</span>
                            <span class="office-action-sub">Handle visit scheduling</span>
                        </span>
                    </a>
                    <a href="{{ route('office.feedback') }}" class="office-action-link">
                        <span class="office-action-icon"><i class="bi bi-chat-left-dots"></i></span>
                        <span>
                            <span class="office-action-title">Feedback</span>
                            <span class="office-action-sub">Respond to citizen reviews</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100 office-reveal" data-office-reveal>
            <div class="card-header">
                <h6 class="card-title">Pending & In-Review Requests</h6>
                <small class="text-muted">Requests requiring office action</small>
            </div>
            <div class="card-body p-0">
                @if($recentRequests->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Citizen</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $request)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $request->reference_number }}</span></td>
                                        <td>{{ $request->citizen->name }}</td>
                                        <td>{{ $request->resolved_service_name }}</td>
                                        <td><x-status-pill :status="$request->status" /></td>
                                        <td class="text-end">
                                            <a href="{{ route('office.requests.show', $request) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state
                        icon="bi-inbox"
                        title="No pending requests"
                        message="Everything is clear right now."
                        class="py-4"
                    />
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card h-100 office-reveal" data-office-reveal>
            <div class="card-header">
                <h6 class="card-title">Today's Appointments</h6>
                <small class="text-muted">Scheduled citizen visits</small>
            </div>
            <div class="card-body">
                @if($todayAppointments->count())
                    <div class="d-flex flex-column gap-2">
                        @foreach($todayAppointments as $appointment)
                            <div class="office-appointment-card">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $appointment->citizen->name }}</span>
                                    <x-status-pill :status="$appointment->status" />
                                </div>
                                <div class="office-appointment-time text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state
                        icon="bi-calendar-x"
                        title="No appointments today"
                        message="New bookings will appear here."
                        class="py-4"
                    />
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <h6 class="card-title">Recent Citizen Feedback</h6>
                <small class="text-muted">Latest comments and ratings</small>
            </div>
            <div class="card-body p-0">
                @if($recentFeedback->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Citizen</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFeedback as $feedback)
                                    <tr>
                                        <td class="fw-semibold">{{ $feedback->citizen->name }}</td>
                                        <td>
                                            <span class="badge rounded-pill bg-warning-subtle border border-warning-subtle">{{ $feedback->rating }}/5</span>
                                        </td>
                                        <td class="office-feedback-comment">{{ $feedback->comment ?: 'No comment provided.' }}</td>
                                        <td class="text-muted">{{ $feedback->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state
                        icon="bi-chat-left-dots"
                        title="No feedback yet"
                        message="Citizen reviews will appear here."
                        class="py-4"
                    />
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE DASHBOARD — PREMIUM GLASSMORPHISM STYLES
   ═══════════════════════════════════════════════════════ */

/* Hero card — glass with animated light sweep */
body.es-role-office_user .office-hero-card {
    margin-bottom: 1rem;
    border: 1px solid rgba(37,99,235,0.12) !important;
    background: rgba(255,255,255,0.55) !important;
    backdrop-filter: blur(16px) saturate(1.6);
    -webkit-backdrop-filter: blur(16px) saturate(1.6);
    position: relative;
    overflow: hidden;
}
body.es-role-office_user .office-hero-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -60%;
    width: 200%;
    height: 200%;
    background: linear-gradient(105deg, transparent 40%, rgba(37,99,235,0.06) 45%, rgba(14,165,233,0.08) 50%, transparent 55%);
    animation: officeHeroSweep 6s ease-in-out infinite;
    pointer-events: none;
}
@keyframes officeHeroSweep {
    0%, 100% { transform: translateX(-30%) rotate(12deg); }
    50%      { transform: translateX(30%) rotate(12deg); }
}

body.es-role-office_user .office-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: .24rem .62rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(37,99,235,0.22);
    border: none;
}

body.es-role-office_user .office-hero-title {
    margin: .86rem 0 .34rem;
    font-size: clamp(1.28rem, 2.4vw, 1.75rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #1E3A8A, #2563EB, #0EA5E9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-office_user .office-hero-copy {
    margin: 0;
    color: #475569;
    font-size: .88rem;
}

/* Hero panel — glass with gradient border */
body.es-role-office_user .office-hero-panel {
    border-radius: .95rem;
    border: 1px solid rgba(37,99,235,0.15);
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: .95rem;
    position: relative;
    overflow: hidden;
}
body.es-role-office_user .office-hero-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #2563EB, #0EA5E9);
    border-radius: 3px 3px 0 0;
}

body.es-role-office_user .office-hero-panel-label {
    color: #475569;
    font-size: .72rem;
    font-weight: 600;
}

body.es-role-office_user .office-hero-panel-value {
    margin-top: .22rem;
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    background: linear-gradient(135deg, #1E3A8A, #2563EB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-office_user .office-hero-panel-sub {
    margin-top: .24rem;
    color: #475569;
    font-size: .77rem;
}

/* KPI cards — glass with unique gradient accent per card */
body.es-role-office_user .office-kpi-card {
    border-radius: .88rem;
    border: 1px solid rgba(37,99,235,0.08) !important;
    background: rgba(255,255,255,0.5) !important;
    backdrop-filter: blur(12px) saturate(1.4);
    -webkit-backdrop-filter: blur(12px) saturate(1.4);
    position: relative;
    overflow: hidden;
    transition: transform .28s cubic-bezier(.4,0,.2,1), box-shadow .28s cubic-bezier(.4,0,.2,1);
}
body.es-role-office_user .office-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    opacity: .6;
    transition: opacity .28s ease;
}
body.es-role-office_user .office-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(37,99,235,0.1);
}
body.es-role-office_user .office-kpi-card:hover::before { opacity: 1; }

body.es-role-office_user .col-6:nth-child(1) .office-kpi-card::before { background: linear-gradient(90deg, #F59E0B, #EF4444); }
body.es-role-office_user .col-6:nth-child(2) .office-kpi-card::before { background: linear-gradient(90deg, #0EA5E9, #2563EB); }
body.es-role-office_user .col-6:nth-child(3) .office-kpi-card::before { background: linear-gradient(90deg, #14B8A6, #10B981); }
body.es-role-office_user .col-6:nth-child(4) .office-kpi-card::before { background: linear-gradient(90deg, #8B5CF6, #6366F1); }

body.es-role-office_user .office-kpi-label {
    display: block;
    margin-bottom: .16rem;
    color: #64748B;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
}

body.es-role-office_user .office-kpi-value {
    margin: 0;
    font-size: 1.58rem;
    font-weight: 800;
    color: #0F172A;
}

body.es-role-office_user .office-kpi-sub {
    margin-top: .18rem;
    font-size: .75rem;
    color: #64748B;
}

/* Actions grid — glass cards with gradient icons */
body.es-role-office_user .office-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .66rem;
}

body.es-role-office_user .office-action-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .55rem;
    border-radius: .9rem;
    padding: 1rem .8rem;
    border: 1px solid rgba(37,99,235,0.08);
    background: rgba(255,255,255,0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #0F172A;
    text-decoration: none;
    transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s ease, border-color .25s ease;
}

body.es-role-office_user .office-action-link:hover {
    transform: translateY(-4px);
    border-color: rgba(37,99,235,0.2);
    box-shadow: 0 16px 32px rgba(37,99,235,0.1);
    color: #0F172A;
}

body.es-role-office_user .office-action-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37,99,235,0.2);
}
body.es-role-office_user .office-action-link:nth-child(1) .office-action-icon { background: linear-gradient(135deg, #2563EB, #0EA5E9); }
body.es-role-office_user .office-action-link:nth-child(2) .office-action-icon { background: linear-gradient(135deg, #8B5CF6, #6366F1); }
body.es-role-office_user .office-action-link:nth-child(3) .office-action-icon { background: linear-gradient(135deg, #14B8A6, #10B981); }
body.es-role-office_user .office-action-link:nth-child(4) .office-action-icon { background: linear-gradient(135deg, #F59E0B, #EA580C); }

body.es-role-office_user .office-action-title {
    display: block;
    font-size: .8rem;
    font-weight: 700;
    line-height: 1.1;
}

body.es-role-office_user .office-action-sub {
    display: block;
    margin-top: .14rem;
    font-size: .68rem;
    color: #64748B;
}

/* Appointment cards — glass */
body.es-role-office_user .office-appointment-card {
    border-radius: .8rem;
    border: 1px solid rgba(37,99,235,0.08);
    background: rgba(255,255,255,0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    padding: .72rem .78rem;
    transition: transform .22s ease, box-shadow .22s ease;
}
body.es-role-office_user .office-appointment-card:hover {
    transform: translateX(4px);
    box-shadow: 0 6px 16px rgba(37,99,235,0.08);
}

body.es-role-office_user .office-appointment-card .fw-semibold {
    font-size: .81rem;
}

body.es-role-office_user .office-appointment-time {
    font-size: .74rem;
}

body.es-role-office_user .office-feedback-comment {
    max-width: 320px;
}

@media (max-width: 991.98px) {
    body.es-role-office_user .office-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 575.98px) {
    body.es-role-office_user .office-actions-grid {
        grid-template-columns: 1fr;
    }
}

@media (prefers-reduced-motion: reduce) {
    body.es-role-office_user .office-hero-card::before { animation: none; }
    body.es-role-office_user .office-kpi-card,
    body.es-role-office_user .office-action-link,
    body.es-role-office_user .office-appointment-card { transition: none; }
}
</style>
@endpush
