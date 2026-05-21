@extends('layouts.app')
@section('title', 'Office Profile')
@section('page-title', 'Office Profile')

@section('content')
<div class="office-profile-wrap">
    <div class="card office-profile-head office-reveal" data-office-reveal>
        <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <span class="office-profile-kicker">Office Settings</span>
                <h2 class="office-profile-title">{{ $office->name }}</h2>
                <p class="office-profile-sub">Update contact details, map coordinates, and operating hours for citizens.</p>
            </div>
            <div class="office-profile-logo-box">
                @if($office->logo)
                    <img src="{{ Storage::url($office->logo) }}" alt="{{ $office->name }} logo" class="office-profile-logo">
                @else
                    <span class="office-profile-logo-placeholder">{{ strtoupper(substr($office->name, 0, 1)) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card office-reveal" data-office-reveal>
        <div class="card-header">
            <span class="card-title">Edit Office Details</span>
        </div>
        <div class="card-body">
            <form action="{{ route('office.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="office-profile-grid">
                    <div class="office-profile-full">
                        <label class="form-label">Office Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}" required>
                    </div>
                    <div class="office-profile-full">
                        <label class="form-label">Address *</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $office->phone) }}" placeholder="+961 1 ...">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $office->email) }}">
                    </div>
                    <div>
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', $office->website) }}" placeholder="https://...">
                    </div>
                    <div>
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div>
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $office->latitude) }}" placeholder="33.8938">
                    </div>
                    <div>
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $office->longitude) }}" placeholder="35.5018">
                    </div>
                </div>

                <div class="office-profile-section">
                    <label class="form-label">Working Hours</label>
                    @php
                        $wh = $office->working_hours ?? [];
                        $days = [
                            'mon' => 'Monday',
                            'tue' => 'Tuesday',
                            'wed' => 'Wednesday',
                            'thu' => 'Thursday',
                            'fri' => 'Friday',
                            'sat' => 'Saturday',
                            'sun' => 'Sunday',
                        ];
                    @endphp
                    <div class="office-hours-grid">
                        @foreach($days as $key => $label)
                            <div class="office-hours-item">
                                <span class="office-hours-day">{{ substr($label, 0, 3) }}</span>
                                <input
                                    type="text"
                                    name="working_hours[{{ $key }}]"
                                    class="form-control form-control-sm"
                                    value="{{ $wh[$key] ?? '' }}"
                                    placeholder="08:00-16:00 or closed"
                                >
                            </div>
                        @endforeach
                    </div>
                    <p class="form-text mb-0">Format: 08:00-16:00 or type "closed".</p>
                </div>

                @if($office->latitude && $office->longitude)
                    @php
                        $mapsKey = config('services.google_maps.api_key');
                        $mapQuery = rawurlencode($office->latitude . ',' . $office->longitude);
                        $mapUrl = $mapsKey
                            ? "https://www.google.com/maps/embed/v1/place?key={$mapsKey}&q={$mapQuery}&zoom=15"
                            : null;
                    @endphp
                    <div class="office-profile-section">
                        <label class="form-label">Map Preview</label>
                        @if($mapUrl)
                            <div class="office-map-wrap">
                                <iframe
                                    title="Office location map"
                                    src="{{ $mapUrl }}"
                                    width="100%"
                                    height="260"
                                    class="office-map-frame"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen>
                                </iframe>
                            </div>
                            <p class="form-text mb-0">Showing map for coordinates {{ $office->latitude }}, {{ $office->longitude }}.</p>
                        @else
                            <div class="office-map-fallback">
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $office->latitude }}, {{ $office->longitude }}</span>
                            </div>
                            <p class="form-text mb-0">Set <code>GOOGLE_MAPS_API_KEY</code> in <code>.env</code> and clear config cache.</p>
                        @endif
                    </div>
                @endif

                <div class="office-profile-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE PROFILE — PREMIUM GLASSMORPHISM
   ═══════════════════════════════════════════════════════ */

body.es-role-office_user .office-profile-wrap { display: flex; flex-direction: column; gap: 1rem; max-width: 880px; }

/* Header — glass with sweep */
body.es-role-office_user .office-profile-head {
    border: 1px solid rgba(37,99,235,0.12) !important;
    background: rgba(255,255,255,0.55) !important;
    backdrop-filter: blur(16px) saturate(1.6);
    -webkit-backdrop-filter: blur(16px) saturate(1.6);
    position: relative; overflow: hidden;
}
body.es-role-office_user .office-profile-kicker {
    display: inline-flex; padding: .24rem .62rem; border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(37,99,235,0.22); border: none;
}
body.es-role-office_user .office-profile-title {
    margin: .72rem 0 .18rem; font-size: clamp(1.2rem, 2.1vw, 1.5rem); font-weight: 800; letter-spacing: -0.02em;
    background: linear-gradient(135deg, #1E3A8A, #2563EB, #0EA5E9);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
body.es-role-office_user .office-profile-sub { margin: 0; color: #475569; font-size: .83rem; }

/* Logo box — gradient */
body.es-role-office_user .office-profile-logo-box {
    width: 3.3rem; height: 3.3rem; border-radius: 1rem;
    border: none;
    background: linear-gradient(155deg, #2563EB, #0EA5E9);
    display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    box-shadow: 0 12px 24px rgba(37,99,235,0.2);
}
body.es-role-office_user .office-profile-logo { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
body.es-role-office_user .office-profile-logo-placeholder { font-size: 1.2rem; font-weight: 800; color: #fff; }

/* Form grid */
body.es-role-office_user .office-profile-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .78rem; }
body.es-role-office_user .office-profile-full { grid-column: 1 / -1; }
body.es-role-office_user .office-profile-section { margin-top: .95rem; }

/* Hours — glass items */
body.es-role-office_user .office-hours-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .52rem; }
body.es-role-office_user .office-hours-item {
    display: flex; align-items: center; gap: .5rem; border-radius: .72rem;
    border: 1px solid rgba(37,99,235,0.08);
    background: rgba(255,255,255,0.45);
    backdrop-filter: blur(6px);
    padding: .5rem .68rem;
    transition: border-color .22s ease;
}
body.es-role-office_user .office-hours-item:focus-within { border-color: rgba(37,99,235,0.25); }
body.es-role-office_user .office-hours-day {
    width: 2.1rem; flex-shrink: 0; color: #334155;
    font-size: .72rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase;
}

/* Map — glass wrapper */
body.es-role-office_user .office-map-wrap {
    border-radius: .86rem; overflow: hidden;
    border: 1px solid rgba(37,99,235,0.1);
    box-shadow: 0 8px 24px rgba(37,99,235,0.08);
}
body.es-role-office_user .office-map-frame { border: 0; display: block; }
body.es-role-office_user .office-map-fallback {
    min-height: 11.2rem; border-radius: .86rem;
    border: 1px solid rgba(37,99,235,0.08);
    background: rgba(255,255,255,0.4);
    backdrop-filter: blur(8px);
    color: #64748B; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem;
}
body.es-role-office_user .office-map-fallback i { font-size: 1.35rem; }
body.es-role-office_user .office-profile-actions { margin-top: 1.05rem; }

@media (max-width: 767.98px) {
    body.es-role-office_user .office-profile-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
