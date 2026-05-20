@extends('layouts.app')
@section('title', $office->name)
@section('page-title', $office->name)

@section('content')
@php
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
    $feedbackFormHasErrors = $errors->has('office_id')
        || $errors->has('service_request_id')
        || $errors->has('rating')
        || $errors->has('comment')
        || $errors->has('feedback');
@endphp
<div class="citizen-office-hero citizen-reveal" data-citizen-reveal>
    <div class="citizen-office-hero-main">
        <div class="citizen-office-hero-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="citizen-office-hero-copy">
            <h4>{{ $office->name }}</h4>
            <div class="citizen-office-hero-meta">
                <span><i class="bi bi-geo-alt me-1"></i>{{ $office->municipality->name }}</span>
                @if($office->phone)
                    <span><i class="bi bi-telephone me-1"></i>{{ $office->phone }}</span>
                @endif
            </div>
        </div>
        @php $rating = $office->averageRating(); @endphp
        @if($rating)
            <div class="citizen-office-rating">
                <div class="citizen-office-rating-value">{{ number_format($rating, 1) }}</div>
                <div class="citizen-office-rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($rating) ? '-fill' : '' }}"></i>
                    @endfor
                </div>
            </div>
        @endif
    </div>

    @if($office->address)
        <div class="citizen-office-hero-address">
            <i class="bi bi-geo-alt"></i>
            <span>{{ $office->address }}</span>
        </div>
    @endif
</div>

<div class="citizen-office-detail-grid">
    <div>
        <h6 class="citizen-office-section-title">{{ __('Available Services') }}</h6>
        @php $grouped = $office->services->where('is_active', true)->groupBy('category_id'); @endphp
        @forelse($grouped as $categoryId => $services)
            @php $catName = $services->first()->category->name ?? __('General'); @endphp
            <div class="citizen-office-category-label">{{ $catName }}</div>
            @foreach($services as $svc)
                <article class="citizen-office-service-card citizen-reveal" data-citizen-reveal>
                    <div class="citizen-office-service-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="citizen-office-service-main">
                        <div class="citizen-office-service-title">{{ $svc->name }}</div>
                        @if($svc->description)
                            <div class="citizen-office-service-desc">{{ Str::limit($svc->description, 85) }}</div>
                        @endif
                        <div class="citizen-office-service-meta">
                            <span><i class="bi bi-clock me-1"></i>~{{ $svc->estimated_duration_days }} {{ __('day(s)') }}</span>
                            @if($svc->required_documents)
                                <span><i class="bi bi-paperclip me-1"></i>{{ count($svc->required_documents) }} {{ __('doc(s) required') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="citizen-office-service-cta">
                        <div class="citizen-office-service-price">{{ $svc->formatted_price }}</div>
                        <a href="{{ route('citizen.services.show', $svc) }}" class="btn btn-primary btn-sm">
                            {{ __('Apply') }}
                        </a>
                    </div>
                </article>
            @endforeach
        @empty
            <div class="card citizen-reveal" data-citizen-reveal>
                <div class="card-body text-center text-muted py-4">{{ __('No services available.') }}</div>
            </div>
        @endforelse
    </div>

    <div class="citizen-office-sidebar">
        @if($office->latitude && $office->longitude)
            <div class="card citizen-reveal" data-citizen-reveal>
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-geo-alt me-2 text-primary"></i>{{ __('Location') }}</span>
                </div>
                <div class="card-body p-0">
                    <div id="officeMap" class="citizen-office-map"></div>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->latitude }},{{ $office->longitude }}"
                    target="_blank"
                    class="btn btn-primary btn-sm w-100 mt-2">
                    <i class="bi bi-signpost-2"></i> {{ __('Get Directions') }}
                </a>
            </div>
        @endif

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-chat-heart me-2 text-primary"></i>{{ __('Share Your Experience') }}</span>
            </div>
            <div class="card-body">
                @if($feedbackFormHasErrors)
                    <div class="alert alert-danger" style="font-size:.8rem">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($eligibleFeedbackRequests->isNotEmpty())
                    <p class="citizen-office-feedback-copy">{{ __('Rate this office based on one of your completed requests.') }}</p>
                    <form action="{{ route('citizen.feedback.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="office_id" value="{{ $office->id }}">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Completed Request') }}</label>
                            <select name="service_request_id" class="form-select" required>
                                <option value="">{{ __('Select completed request') }}</option>
                                @foreach($eligibleFeedbackRequests as $eligibleRequest)
                                    <option value="{{ $eligibleRequest->id }}" @selected((string) old('service_request_id') === (string) $eligibleRequest->id)>
                                        {{ $eligibleRequest->resolved_service_name }} - {{ $eligibleRequest->reference_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Rating') }}</label>
                            <select name="rating" class="form-select" required>
                                <option value="">{{ __('Select rating') }}</option>
                                <option value="5" @selected(old('rating') == 5)>5 - {{ __('Excellent') }}</option>
                                <option value="4" @selected(old('rating') == 4)>4 - {{ __('Very Good') }}</option>
                                <option value="3" @selected(old('rating') == 3)>3 - {{ __('Good') }}</option>
                                <option value="2" @selected(old('rating') == 2)>2 - {{ __('Fair') }}</option>
                                <option value="1" @selected(old('rating') == 1)>1 - {{ __('Poor') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="{{ __('Tell others about your experience...') }}">{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i> {{ __('Submit Feedback') }}
                        </button>
                    </form>
                @elseif($citizenOfficeFeedbacks->isNotEmpty())
                    <div class="citizen-office-feedback-note">
                        <i class="bi bi-check-circle me-1"></i>{{ __('You have already reviewed all completed requests for this office.') }}
                    </div>
                    @php($latestFeedback = $citizenOfficeFeedbacks->first())
                    <div class="citizen-office-my-review">
                        <div class="citizen-office-review-head">
                            <span>{{ __('Your latest review') }}</span>
                            <div class="citizen-office-review-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $latestFeedback->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <div class="citizen-office-feedback-meta">
                            {{ $latestFeedback->request?->resolved_service_name ?? __('Completed request') }}
                            @if($latestFeedback->request?->reference_number)
                                - {{ $latestFeedback->request->reference_number }}
                            @endif
                        </div>
                        @if($latestFeedback->comment)
                            <p>{{ $latestFeedback->comment }}</p>
                        @endif
                    </div>
                @else
                    <div class="citizen-office-feedback-note">
                        <i class="bi bi-info-circle me-1"></i>{{ __('Feedback becomes available after you complete a service request with this office.') }}
                    </div>
                @endif
            </div>
        </div>

        @if($office->working_hours)
            <div class="card citizen-reveal" data-citizen-reveal>
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-clock me-2 text-primary"></i>{{ __('Working Hours') }}</span>
                </div>
                <div class="card-body">
                    @php $days = ['mon' => __('Mon'), 'tue' => __('Tue'), 'wed' => __('Wed'), 'thu' => __('Thu'), 'fri' => __('Fri'), 'sat' => __('Sat'), 'sun' => __('Sun')]; @endphp
                    @foreach($days as $key => $label)
                        @php $hours = $office->working_hours[$key] ?? null; @endphp
                        <div class="citizen-office-hours-row {{ !$loop->last ? 'with-border' : '' }}">
                            <span>{{ $label }}</span>
                            <strong class="{{ $hours === 'closed' ? 'is-closed' : 'is-open' }}">{{ $hours === 'closed' ? __('Closed') : ($hours ?? __('N/A')) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-star me-2" style="color:#F59E0B"></i>{{ __('Recent Reviews') }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($office->feedbacks->take(3) as $fb)
                    <div class="citizen-office-review-row {{ !$loop->last ? 'with-border' : '' }}">
                        <div class="citizen-office-review-head">
                            <span>{{ $fb->citizen->name }}</span>
                            <div class="citizen-office-review-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $fb->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        @if($fb->comment)
                            <p>{{ $fb->comment }}</p>
                        @endif
                    </div>
                @empty
                    <div class="citizen-panel-empty">
                        <i class="bi bi-chat-square-quote"></i>
                        <p>{{ __('No reviews yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-calendar-plus me-2 text-primary"></i>{{ __('Book Appointment') }}</span>
            </div>
            <div class="card-body">
                <p class="citizen-office-book-copy">{{ __('Schedule an in-person visit') }}</p>
                @if($citizenActionLocked)
                    <div class="alert alert-warning mb-3" style="font-size:.82rem;">
                        <i class="bi bi-lock me-1"></i>{{ __('Finish your profile verification to unlock appointment booking.') }}
                    </div>
                @endif
                <form action="{{ route('citizen.appointments.book') }}" method="POST"
                      data-slots-form data-slots-url="{{ route('citizen.offices.slots', $office) }}">
                    @csrf
                    <input type="hidden" name="office_id" value="{{ $office->id }}">
                    <div class="mb-2">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="date" name="appointment_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" required data-slots-date @disabled($citizenActionLocked)>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Available Time Slots') }}</label>
                        <select name="appointment_time" class="form-select" required data-slots-select disabled>
                            <option value="">{{ __('Pick a date first…') }}</option>
                        </select>
                        <div class="form-text" data-slots-status></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" @disabled($citizenActionLocked)>
                        <i class="bi {{ $citizenActionLocked ? 'bi-lock' : 'bi-calendar-check' }} me-1"></i> {{ $citizenActionLocked ? __('Profile Verification Required') : __('Book') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body.es-role-citizen .citizen-office-hero {
    border-radius: 1rem;
    padding: 1.35rem;
    margin-bottom: 1.1rem;
    background: linear-gradient(140deg, #0EA5E9 0%, #2563EB 55%, #1D4ED8 100%);
    box-shadow: 0 16px 34px rgba(37, 99, 235, 0.28);
}

body.es-role-citizen .citizen-office-hero-main {
    display: flex;
    align-items: center;
    gap: .95rem;
    flex-wrap: wrap;
}

body.es-role-citizen .citizen-office-hero-icon {
    width: 3.4rem;
    height: 3.4rem;
    border-radius: .9rem;
    background: rgba(255, 255, 255, .18);
    border: 1px solid rgba(255, 255, 255, .35);
    color: #fff;
    font-size: 1.45rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-office-hero-copy {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-office-hero-copy h4 {
    color: #fff;
    font-weight: 800;
    margin: 0 0 .2rem;
    font-size: 1.1rem;
}

body.es-role-citizen .citizen-office-hero-meta {
    color: rgba(255, 255, 255, .8);
    font-size: .78rem;
    display: flex;
    align-items: center;
    gap: .7rem;
    flex-wrap: wrap;
}

body.es-role-citizen .citizen-office-rating {
    text-align: center;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-office-rating-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

body.es-role-citizen .citizen-office-rating-stars {
    margin-top: .25rem;
    display: flex;
    justify-content: center;
    gap: 2px;
    color: #FBBF24;
    font-size: .72rem;
}

body.es-role-citizen .citizen-office-hero-address {
    margin-top: .85rem;
    color: rgba(255, 255, 255, .76);
    font-size: .77rem;
    display: flex;
    align-items: flex-start;
    gap: .35rem;
}

body.es-role-citizen .citizen-office-detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

body.es-role-citizen .citizen-office-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

body.es-role-citizen .citizen-office-section-title {
    font-size: .92rem;
    font-weight: 800;
    margin: 0 0 .75rem;
    color: #0F172A;
}

body.es-role-citizen .citizen-office-category-label {
    margin: .75rem 0 .5rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    color: #64748B;
    text-transform: uppercase;
}

body.es-role-citizen .citizen-office-service-card {
    background: #fff;
    border: 1px solid #DBEAFE;
    border-radius: .92rem;
    padding: .95rem;
    margin-bottom: .62rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

body.es-role-citizen .citizen-office-service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.16);
    border-color: #93C5FD;
}

body.es-role-citizen .citizen-office-service-icon {
    width: 2.45rem;
    height: 2.45rem;
    border-radius: .72rem;
    background: #E0F2FE;
    border: 1px solid #BAE6FD;
    color: #0369A1;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-office-service-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-office-service-title {
    font-size: .85rem;
    font-weight: 700;
    color: #0F172A;
}

body.es-role-citizen .citizen-office-service-desc {
    margin-top: .12rem;
    font-size: .75rem;
    color: #64748B;
}

body.es-role-citizen .citizen-office-service-meta {
    display: flex;
    gap: .7rem;
    flex-wrap: wrap;
    margin-top: .3rem;
    font-size: .72rem;
    color: #475569;
}

body.es-role-citizen .citizen-office-service-cta {
    text-align: right;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-office-service-price {
    font-size: .98rem;
    font-weight: 800;
    color: #0284C7;
    line-height: 1.1;
    margin-bottom: .35rem;
}

body.es-role-citizen .citizen-office-map {
    width: 100%;
    height: 13.8rem;
    border-radius: 0 0 .92rem .92rem;
}

body.es-role-citizen .citizen-office-hours-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .34rem 0;
    font-size: .8rem;
}

body.es-role-citizen .citizen-office-hours-row.with-border {
    border-bottom: 1px solid #E2E8F0;
}

body.es-role-citizen .citizen-office-hours-row strong.is-open {
    color: #047857;
}

body.es-role-citizen .citizen-office-hours-row strong.is-closed {
    color: #BE123C;
}

body.es-role-citizen .citizen-office-review-row {
    padding: .82rem 1rem;
}

body.es-role-citizen .citizen-office-review-row.with-border {
    border-bottom: 1px solid #E2E8F0;
}

body.es-role-citizen .citizen-office-review-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
}

body.es-role-citizen .citizen-office-review-head span {
    font-size: .8rem;
    font-weight: 700;
}

body.es-role-citizen .citizen-office-review-stars {
    display: inline-flex;
    gap: 2px;
    color: #F59E0B;
    font-size: .68rem;
}

body.es-role-citizen .citizen-office-review-row p {
    margin: .35rem 0 0;
    font-size: .77rem;
    color: #64748B;
    line-height: 1.5;
}

body.es-role-citizen .citizen-office-book-copy {
    font-size: .8rem;
    color: #64748B;
    margin-bottom: .85rem;
}

body.es-role-citizen .citizen-office-feedback-copy {
    font-size: .8rem;
    color: #64748B;
    margin-bottom: .85rem;
}

body.es-role-citizen .citizen-office-feedback-note {
    padding: .8rem .9rem;
    border-radius: .7rem;
    background: rgba(239,246,255,0.65);
    border: 1px solid rgba(147,197,253,0.45);
    color: #1D4ED8;
    font-size: .8rem;
    line-height: 1.5;
}

body.es-role-citizen .citizen-office-my-review {
    margin-top: .85rem;
    padding: .85rem .95rem;
    border-radius: .85rem;
    border: 1px solid #DBEAFE;
    background: rgba(248,250,252,0.85);
}

body.es-role-citizen .citizen-office-feedback-meta {
    margin-top: .25rem;
    font-size: .72rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-panel-empty {
    padding: 1.35rem 1rem;
    text-align: center;
    color: #64748B;
}

body.es-role-citizen .citizen-panel-empty i {
    font-size: 1.45rem;
    color: #94A3B8;
    margin-bottom: .35rem;
    display: block;
}

body.es-role-citizen .citizen-panel-empty p {
    margin: 0;
    font-size: .79rem;
}

@media (min-width: 768px) {
    body.es-role-citizen .citizen-office-detail-grid {
        grid-template-columns: 1fr 290px;
    }
}
</style>
@endpush

@push('scripts')
<script>
function initOfficeMap() {
    const lat = {{ $office->latitude ?? 0 }};
    const lng = {{ $office->longitude ?? 0 }};

    if (!lat || !lng) return;

    const officeMap = document.getElementById('officeMap');
    if (!officeMap) return;

    const map = new google.maps.Map(officeMap, {
        center: { lat, lng },
        zoom: 15,
    });

    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div style="min-width:180px;max-width:220px;padding:4px 2px;">
                <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:4px;">
                    {{ addslashes($office->name) }}
                </div>
                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">
                    {{ addslashes($office->municipality->name) }}
                </div>
                <div style="font-size:12px;color:#334155;line-height:1.4;">
                    {{ addslashes($office->address ?? '') }}
                </div>
            </div>
        `,
    });

    const marker = new google.maps.Marker({
        position: { lat, lng },
        map,
        title: @json($office->name),
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });
}
</script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initOfficeMap">
</script>

<script>
/* ── Appointment slot picker ─────────────────────────────── */
document.querySelectorAll('[data-slots-form]').forEach((form) => {
    const dateInput = form.querySelector('[data-slots-date]');
    const select    = form.querySelector('[data-slots-select]');
    const status    = form.querySelector('[data-slots-status]');
    const url       = form.dataset.slotsUrl;
    const setStatus = (text) => { if (status) status.textContent = text; };

    const loadSlots = async () => {
        const date = dateInput.value;
        if (!date) {
            select.innerHTML = '<option value="">Pick a date first…</option>';
            select.disabled = true;
            setStatus('');
            return;
        }
        select.disabled = true;
        select.innerHTML = '<option value="">Loading slots…</option>';
        setStatus('Checking availability…');

        try {
            const res = await fetch(`${url}?date=${encodeURIComponent(date)}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error('Failed to load slots');
            const data = await res.json();
            if (!data.slots || data.slots.length === 0) {
                select.innerHTML = '<option value="">No slots available on this date</option>';
                setStatus('The office is closed or fully booked on this date.');
                return;
            }
            select.innerHTML = '<option value="">Select a time…</option>' +
                data.slots.map(s => `<option value="${s}">${s}</option>`).join('');
            select.disabled = false;
            setStatus(`${data.slots.length} slot${data.slots.length === 1 ? '' : 's'} available.`);
        } catch (err) {
            select.innerHTML = '<option value="">Could not load slots</option>';
            setStatus('Something went wrong — please try again.');
        }
    };

    dateInput.addEventListener('change', loadSlots);
});
</script>
@endpush
