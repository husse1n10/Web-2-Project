@extends('layouts.app')
@section('title', __('My Appointments'))
@section('page-title', __('My Appointments'))

@section('content')
@php
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
@endphp
<div class="card citizen-reveal" data-citizen-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div>
            <span class="card-title">{{ __('My Appointments') }}</span>
            <div class="citizen-appt-subtitle">{{ __('Scheduled visits and timings for your service requests') }}</div>
        </div>
    </div>

    @if($requestsNeedingAppointments->isNotEmpty())
        <div class="citizen-appt-request-panel">
            <div class="citizen-appt-request-head">
                <div>
                    <div class="citizen-appt-request-title">{{ __('Requests Needing Appointments') }}</div>
                    <div class="citizen-appt-subtitle">{{ __('Book a visit for requests that do not have an active appointment.') }}</div>
                </div>
                <span class="citizen-appt-count">{{ $requestsNeedingAppointments->count() }}</span>
            </div>
            <div class="citizen-appt-request-list">
                @foreach($requestsNeedingAppointments as $requestItem)
                    <div class="citizen-appt-request-row">
                        <div class="citizen-appt-request-main">
                            <div class="citizen-appt-request-service">{{ $requestItem->service->name }}</div>
                            <div class="citizen-appt-request-meta">
                                {{ $requestItem->office->name }} &middot; {{ $requestItem->reference_number }}
                            </div>
                        </div>
                        <x-status-pill :status="$requestItem->status" />
                        <div class="citizen-appt-request-actions">
                            <a href="{{ route('citizen.requests.show', $requestItem) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> {{ __('View') }}
                            </a>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                @disabled($citizenActionLocked)
                                data-book-appointment
                                data-office-id="{{ $requestItem->office_id }}"
                                data-request-id="{{ $requestItem->id }}"
                                data-request-label="{{ $requestItem->service->name }} - {{ $requestItem->reference_number }}"
                            >
                                <i class="bi {{ $citizenActionLocked ? 'bi-lock' : 'bi-calendar-plus' }} me-1"></i> {{ $citizenActionLocked ? __('Profile Verification Required') : __('Book') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Desktop table --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Office') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Time') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
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
                                            <i class="bi bi-eye me-1"></i> {{ __('View Request') }}
                                        </a>
                                    @endif
                                    @if(!in_array($appt->status, ['cancelled', 'completed'], true))
                                        <form action="{{ route('citizen.appointments.cancel', $appt) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this appointment? This cannot be undone.') }}');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}
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
                                    :title="__('No appointments yet')"
                                    :message="$requestsNeedingAppointments->isNotEmpty() ? __('Use the requests above to book your first appointment.') : __('Submit a service request first, then book an appointment from here.')"
                                    :action-url="$requestsNeedingAppointments->isEmpty() ? route('citizen.offices') : null"
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
                    message="{{ $requestsNeedingAppointments->isNotEmpty() ? 'Use the requests above to book your first appointment.' : 'Submit a service request first, then book an appointment from here.' }}"
                    :action-url="$requestsNeedingAppointments->isEmpty() ? route('citizen.offices') : null"
                    action-label="Browse Services"
                />
            </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
        <div class="citizen-appt-pagination">{{ $appointments->links() }}</div>
    @endif
</div>

<div class="modal fade" id="appointmentBookingModal" tabindex="-1" aria-labelledby="appointmentBookingTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content citizen-appointment-modal">
            <div class="modal-header border-0 pb-1">
                <div>
                    <h6 class="modal-title fw-bold" id="appointmentBookingTitle">Book Appointment</h6>
                    <div class="citizen-appt-modal-request" id="appointmentBookingRequest"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('citizen.appointments.book') }}" method="POST">
                @csrf
                <input type="hidden" name="office_id" id="appointmentOfficeId">
                <input type="hidden" name="service_request_id" id="appointmentRequestId">
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label">Preferred Date</label>
                        <input type="date" name="appointment_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" required @disabled($citizenActionLocked)>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preferred Time</label>
                        <input type="time" name="appointment_time" class="form-control" required @disabled($citizenActionLocked)>
                    </div>
                    <div>
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any specific notes..." @disabled($citizenActionLocked)></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" @disabled($citizenActionLocked)>{{ $citizenActionLocked ? __('Profile Verification Required') : __('Confirm Booking') }}</button>
                </div>
            </form>
        </div>
    </div>
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

body.es-role-citizen .citizen-appt-request-panel {
    padding: 1rem 1.4rem 1.1rem;
    border-top: 1px solid rgba(226,232,240,0.65);
    border-bottom: 1px solid rgba(226,232,240,0.65);
    background: rgba(248,250,252,0.45);
}

body.es-role-citizen .citizen-appt-request-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .8rem;
}

body.es-role-citizen .citizen-appt-request-title {
    font-size: .88rem;
    font-weight: 800;
    color: #0F172A;
}

body.es-role-citizen .citizen-appt-count {
    min-width: 1.7rem;
    height: 1.7rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff;
    font-size: .78rem;
    font-weight: 800;
}

body.es-role-citizen .citizen-appt-request-list {
    display: grid;
    gap: .55rem;
}

body.es-role-citizen .citizen-appt-request-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto;
    align-items: center;
    gap: .75rem;
    padding: .75rem .85rem;
    border: 1px solid rgba(191,219,254,0.55);
    border-radius: .85rem;
    background: rgba(255,255,255,0.7);
}

body.es-role-citizen .citizen-appt-request-main {
    min-width: 0;
}

body.es-role-citizen .citizen-appt-request-service {
    font-size: .84rem;
    font-weight: 750;
    color: #0F172A;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-appt-request-meta,
body.es-role-citizen .citizen-appt-modal-request {
    color: #64748B;
    font-size: .73rem;
}

body.es-role-citizen .citizen-appt-request-actions {
    display: inline-flex;
    gap: .4rem;
    justify-content: flex-end;
}

body.es-role-citizen .citizen-appointment-modal {
    border: 1px solid rgba(219,234,254,0.5);
    border-radius: 1.1rem;
    box-shadow: 0 24px 56px rgba(15,23,42,0.2);
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

@media (max-width: 767.98px) {
    body.es-role-citizen .citizen-appt-request-panel {
        padding: .9rem;
    }

    body.es-role-citizen .citizen-appt-request-row {
        grid-template-columns: 1fr;
        align-items: stretch;
    }

    body.es-role-citizen .citizen-appt-request-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-book-appointment]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('appointmentOfficeId').value = button.dataset.officeId;
        document.getElementById('appointmentRequestId').value = button.dataset.requestId;
        document.getElementById('appointmentBookingRequest').textContent = button.dataset.requestLabel || '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('appointmentBookingModal')).show();
    });
});
</script>
@endpush
