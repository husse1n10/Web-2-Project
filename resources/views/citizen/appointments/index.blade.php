@extends('layouts.app')
@section('title', 'My Appointments')
@section('page-title', 'My Appointments')

@section('content')
<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div>
            <span class="card-title">My Appointments</span>
            <div class="citizen-appt-subtitle">Scheduled visits and timings for your service requests</div>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                        <tr>
                            <td class="fw-semibold">{{ $appt->office->name }}</td>
                            <td class="text-muted" style="font-size:.84rem;">
                                {{ $appt->request?->service?->name ?? '—' }}
                            </td>
                            <td>
                                <i class="bi bi-calendar-event me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}
                            </td>
                            <td>
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}
                            </td>
                            <td><x-status-pill :status="$appt->status" /></td>
                            <td class="text-muted" style="font-size:.79rem; max-width:14rem;">
                                {{ Str::limit($appt->notes, 50) ?? '—' }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($appt->request)
                                        <a href="{{ route('citizen.requests.show', $appt->request) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View Request
                                        </a>
                                    @endif
                                    @if(!in_array($appt->status, ['cancelled', 'completed'], true))
                                        <form action="{{ route('citizen.appointments.cancel', $appt) }}" method="POST" onsubmit="return confirm('Cancel this appointment? This cannot be undone.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle me-1"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4">
                                <x-empty-state
                                    icon="bi-calendar-x"
                                    title="No appointments yet"
                                    message="Appointments are created when you submit a service request."
                                    :action-url="route('citizen.offices')"
                                    action-label="Browse Services"
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
        @forelse($appointments as $appt)
            <div class="citizen-appt-mobile-item">
                <div class="citizen-appt-mobile-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="citizen-appt-mobile-main">
                    <div class="citizen-appt-mobile-title">{{ $appt->office->name }}</div>
                    <div class="citizen-appt-mobile-sub">{{ $appt->request?->service?->name ?? 'General Visit' }}</div>
                    <div class="citizen-appt-mobile-time">
                        <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}
                        <span class="mx-1">&middot;</span>
                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}
                    </div>
                </div>
                <div class="citizen-appt-mobile-status">
                    <x-status-pill :status="$appt->status" />
                    @if(!in_array($appt->status, ['cancelled', 'completed'], true))
                        <form action="{{ route('citizen.appointments.cancel', $appt) }}" method="POST" onsubmit="return confirm('Cancel this appointment? This cannot be undone.');" class="mt-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-4">
                <x-empty-state
                    icon="bi-calendar-x"
                    title="No appointments yet"
                    message="Appointments are created when you submit a service request."
                    :action-url="route('citizen.offices')"
                    action-label="Browse Services"
                />
            </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
        <div class="citizen-appt-pagination">{{ $appointments->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CITIZEN APPOINTMENTS — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

body.es-role-citizen .citizen-appt-subtitle {
    margin-top: .18rem;
    font-size: .73rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-appt-mobile-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem .95rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    transition: all .22s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-appt-mobile-item:hover {
    background: rgba(224,242,254,0.2);
    transform: translateX(3px);
}

body.es-role-citizen .citizen-appt-mobile-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E0F2FE, #EDE9FE);
    border: 1px solid rgba(14,165,233,0.15);
    color: #0284C7;
    flex-shrink: 0;
    font-size: 1rem;
    box-shadow: 0 3px 8px rgba(14,165,233,0.1);
}

body.es-role-citizen .citizen-appt-mobile-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-appt-mobile-title {
    font-size: .83rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-appt-mobile-sub {
    margin-top: .1rem;
    color: #64748B;
    font-size: .72rem;
}

body.es-role-citizen .citizen-appt-mobile-time {
    margin-top: .22rem;
    font-size: .68rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-appt-mobile-status {
    flex-shrink: 0;
}

body.es-role-citizen .citizen-appt-pagination {
    border-top: 1px solid rgba(226,232,240,0.5);
    padding: .9rem 1rem;
}
</style>
@endpush
