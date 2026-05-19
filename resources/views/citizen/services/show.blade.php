@extends('layouts.app')
@section('title', $service->name)
@section('page-title', $service->name)

@section('content')
@php
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
@endphp
<div class="citizen-service-shell citizen-reveal" data-citizen-reveal>
    <div class="card mb-3">
        <div class="citizen-service-hero">
            <div class="citizen-service-hero-icon">
                <i class="bi bi-file-earmark-check"></i>
            </div>
            <div class="citizen-service-hero-main">
                <h4>{{ $service->name }}</h4>
                <div class="citizen-service-hero-sub">{{ $service->office->name }} &middot; {{ $service->office->municipality->name }}</div>
            </div>
            <div class="citizen-service-hero-price">
                <div class="citizen-service-hero-amount">${{ number_format($service->price, 2) }}</div>
                <div class="citizen-service-hero-currency">{{ $service->currency }}</div>
            </div>
        </div>
        <div class="card-body">
            @if($service->description)
                <p class="citizen-service-desc">{{ $service->description }}</p>
            @endif

            <div class="citizen-service-tags">
                <div class="citizen-service-tag">
                    <i class="bi bi-clock"></i>
                    <span>~{{ $service->estimated_duration_days }} {{ __('business day(s)') }}</span>
                </div>
                @if($service->category)
                    <div class="citizen-service-tag">
                        <i class="bi bi-tag"></i>
                        <span>{{ $service->category->name }}</span>
                    </div>
                @endif
            </div>

            @if(isset($convertedPrices) && count($convertedPrices) > 0)
                <div class="citizen-service-converted">
                    <div class="citizen-service-converted-label">
                        <i class="bi bi-currency-exchange"></i> {{ __('Also equivalent to:') }}
                    </div>
                    <div class="citizen-service-converted-list">
                        @foreach($convertedPrices as $currency => $amount)
                            <span class="citizen-service-converted-chip">
                                @if($currency === 'USD')$@elseif($currency === 'EUR')&euro;@elseif($currency === 'LBP')LBP @endif{{ number_format($amount, $currency === 'LBP' ? 0 : 2) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($service->required_documents && count($service->required_documents) > 0)
        <div class="card mb-3 citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-paperclip me-2 text-primary"></i>{{ __('Required Documents') }}</span>
            </div>
            <div class="card-body">
                <div class="citizen-service-doc-list">
                    @foreach($service->required_documents as $doc)
                        <div class="citizen-service-doc-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $doc }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="citizen-service-doc-help">
                    <i class="bi bi-info-circle"></i> {{ __('Prepare these documents before submitting your request.') }}
                </p>
            </div>
        </div>
    @endif

    <div class="card citizen-reveal" data-citizen-reveal>
        <div class="card-header">
            <span class="card-title"><i class="bi bi-send me-2 text-primary"></i>{{ __('Submit Request') }}</span>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($citizenActionLocked)
                <div class="alert alert-warning mb-3" style="font-size:.82rem;">
                    <i class="bi bi-lock me-1"></i>{{ __('Finish your profile verification to unlock new request submission.') }}
                </div>
            @endif

            <form action="{{ route('citizen.requests.submit', $service) }}" method="POST" enctype="multipart/form-data" id="submitForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Additional Notes') }} <span class="citizen-service-muted">({{ __('optional') }})</span></label>
                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                        @disabled($citizenActionLocked)
                        placeholder="{{ __('Any additional information or special requirements...') }}"
                    >{{ old('notes') }}</textarea>
                </div>

                @if($service->required_documents && count($service->required_documents) > 0)
                    <div class="mb-3">
                        <label class="form-label">{{ __('Upload Required Documents') }} <span class="citizen-service-required">*</span></label>
                        <p class="citizen-service-upload-help">{{ __('Upload clear scans or photos. Accepted: PDF, JPG, PNG (max 5MB each)') }}</p>

                        @foreach($service->required_documents as $i => $docName)
                            <div class="citizen-service-upload-item">
                                <label class="citizen-service-upload-label">
                                    {{ $docName }} <span class="citizen-service-required">*</span>
                                </label>
                                <div class="upload-zone-sm citizen-upload-zone" id="zone_{{ $i }}" onclick="document.getElementById('doc_{{ $i }}').click()">
                                    <i class="bi bi-cloud-upload"></i>
                                    <div id="zone_label_{{ $i }}" class="citizen-upload-zone-label">{{ __('Tap to upload') }} {{ $docName }}</div>
                                    <input
                                        type="file"
                                        id="doc_{{ $i }}"
                                        name="documents[]"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        @disabled($citizenActionLocked)
                                        required
                                        onchange="handleUpload({{ $i }}, this)"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">{{ __('Supporting Documents') }} <span class="citizen-service-muted">({{ __('optional') }})</span></label>
                        <div class="upload-zone-sm citizen-upload-zone" onclick="document.getElementById('docsFree').click()">
                            <i class="bi bi-cloud-upload"></i>
                            <div id="docsFreeLabel" class="citizen-upload-zone-label">{{ __('Tap to upload documents (PDF, JPG, PNG)') }}</div>
                            <input type="file" id="docsFree" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple @disabled($citizenActionLocked)>
                        </div>
                    </div>
                @endif

                <div class="citizen-service-summary">
                    <div class="citizen-service-summary-title">{{ __('Request Summary') }}</div>
                    <div class="citizen-service-summary-row">
                        <span>{{ __('Service fee') }}</span>
                        <strong>${{ number_format($service->price, 2) }}</strong>
                    </div>
                    <div class="citizen-service-summary-row">
                        <span>{{ __('Processing time') }}</span>
                        <strong>~{{ $service->estimated_duration_days }} {{ __('day(s)') }}</strong>
                    </div>
                    <div class="citizen-service-summary-note">
                        <i class="bi bi-info-circle"></i> {{ __('Payment is collected after your request is approved.') }}
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 citizen-submit-btn" id="submitBtn" @disabled($citizenActionLocked)>
                    <i class="bi {{ $citizenActionLocked ? 'bi-lock' : 'bi-send-fill' }} me-1"></i>
                    {{ $citizenActionLocked ? __('Profile Verification Required') : __('Submit Request') }}
                </button>
                <a href="{{ route('citizen.offices.show', $service->office) }}" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Office') }}
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body.es-role-citizen .citizen-service-shell {
    max-width: 680px;
}

body.es-role-citizen .citizen-service-hero {
    background: linear-gradient(140deg, #0EA5E9 0%, #2563EB 60%, #1D4ED8 100%);
    padding: 1.4rem 1.5rem;
    border-radius: .9rem .9rem 0 0;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

body.es-role-citizen .citizen-service-hero-icon {
    width: 3.1rem;
    height: 3.1rem;
    border-radius: .8rem;
    background: rgba(255, 255, 255, .15);
    border: 1px solid rgba(255, 255, 255, .28);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.22rem;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-service-hero-main {
    flex: 1;
}

body.es-role-citizen .citizen-service-hero-main h4 {
    color: #fff;
    font-weight: 800;
    margin: 0 0 .3rem;
    font-size: 1.05rem;
}

body.es-role-citizen .citizen-service-hero-sub {
    color: rgba(255, 255, 255, .75);
    font-size: .78rem;
}

body.es-role-citizen .citizen-service-hero-price {
    text-align: right;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-service-hero-amount {
    color: #fff;
    font-size: 1.38rem;
    font-weight: 800;
    line-height: 1.1;
}

body.es-role-citizen .citizen-service-hero-currency {
    color: rgba(255, 255, 255, .7);
    font-size: .72rem;
}

body.es-role-citizen .citizen-service-desc {
    font-size: .85rem;
    color: #334155;
    line-height: 1.6;
    margin-bottom: 1rem;
}

body.es-role-citizen .citizen-service-tags {
    display: flex;
    flex-wrap: wrap;
    gap: .62rem;
}

body.es-role-citizen .citizen-service-tag {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: #F8FAFC;
    border-radius: .55rem;
    border: 1px solid #E2E8F0;
    padding: .42rem .72rem;
    font-size: .78rem;
    font-weight: 600;
    color: #334155;
}

body.es-role-citizen .citizen-service-tag i {
    color: #0EA5E9;
    font-size: .84rem;
}

body.es-role-citizen .citizen-service-converted {
    margin-top: 1rem;
    border-top: 1px solid #E2E8F0;
    padding-top: .82rem;
}

body.es-role-citizen .citizen-service-converted-label {
    font-size: .72rem;
    color: #64748B;
    margin-bottom: .5rem;
    display: flex;
    align-items: center;
    gap: .3rem;
}

body.es-role-citizen .citizen-service-converted-list {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

body.es-role-citizen .citizen-service-converted-chip {
    background: #ECFDF5;
    color: #166534;
    border: 1px solid #A7F3D0;
    font-size: .78rem;
    font-weight: 600;
    padding: .3rem .68rem;
    border-radius: .5rem;
}

body.es-role-citizen .citizen-service-doc-list {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}

body.es-role-citizen .citizen-service-doc-item {
    display: flex;
    align-items: center;
    gap: .6rem;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: .55rem;
    padding: .6rem .85rem;
    font-size: .82rem;
    color: #334155;
}

body.es-role-citizen .citizen-service-doc-item i {
    color: #16A34A;
    font-size: .84rem;
}

body.es-role-citizen .citizen-service-doc-help {
    font-size: .75rem;
    color: #64748B;
    margin: .72rem 0 0;
    display: flex;
    align-items: center;
    gap: .3rem;
}

body.es-role-citizen .citizen-service-muted {
    color: #94A3B8;
    font-weight: 400;
}

body.es-role-citizen .citizen-service-required {
    color: #E11D48;
}

body.es-role-citizen .citizen-service-upload-help {
    font-size: .75rem;
    color: #64748B;
    margin-bottom: .6rem;
}

body.es-role-citizen .citizen-service-upload-item {
    margin-bottom: .65rem;
}

body.es-role-citizen .citizen-service-upload-label {
    font-size: .78rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: .32rem;
    display: block;
}

body.es-role-citizen .citizen-upload-zone {
    border: 2px dashed #E2E8F0;
    border-radius: .58rem;
    padding: .78rem;
    text-align: center;
    cursor: pointer;
    transition: all .15s ease;
    background: #F8FAFC;
}

body.es-role-citizen .citizen-upload-zone:hover {
    border-color: #7DD3FC;
    background: #F0F9FF;
}

body.es-role-citizen .citizen-upload-zone.is-filled {
    border-color: #0EA5E9;
    background: #EFF6FF;
}

body.es-role-citizen .citizen-upload-zone i {
    color: #94A3B8;
    font-size: 1.25rem;
    display: block;
    margin-bottom: .25rem;
}

body.es-role-citizen .citizen-upload-zone.is-filled i {
    color: #0EA5E9;
}

body.es-role-citizen .citizen-upload-zone-label {
    font-size: .75rem;
    color: #64748B;
}

body.es-role-citizen .citizen-upload-zone.is-filled .citizen-upload-zone-label {
    color: #0369A1;
    font-weight: 600;
}

body.es-role-citizen .citizen-upload-zone input {
    display: none;
}

body.es-role-citizen .citizen-service-summary {
    background: #E0F2FE;
    border: 1px solid #BAE6FD;
    border-radius: .68rem;
    padding: 1rem;
    margin-bottom: 1.05rem;
}

body.es-role-citizen .citizen-service-summary-title {
    font-size: .76rem;
    font-weight: 700;
    color: #0369A1;
    margin-bottom: .6rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}

body.es-role-citizen .citizen-service-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: .82rem;
    color: #334155;
    margin-bottom: .34rem;
}

body.es-role-citizen .citizen-service-summary-row strong {
    color: #0F172A;
}

body.es-role-citizen .citizen-service-summary-note {
    border-top: 1px solid rgba(14, 165, 233, .3);
    margin-top: .6rem;
    padding-top: .6rem;
    font-size: .75rem;
    color: #0369A1;
    display: flex;
    align-items: center;
    gap: .3rem;
}

body.es-role-citizen .citizen-submit-btn {
    padding: .65rem;
}

@media (max-width: 575.98px) {
    body.es-role-citizen .citizen-service-hero {
        padding: 1.1rem;
        gap: .75rem;
        flex-wrap: wrap;
    }

    body.es-role-citizen .citizen-service-hero-icon {
        width: 2.75rem;
        height: 2.75rem;
        font-size: 1.06rem;
    }

    body.es-role-citizen .citizen-service-hero-main h4 {
        font-size: .95rem;
    }

    body.es-role-citizen .citizen-service-hero-price {
        width: 100%;
        text-align: left;
    }
}
</style>
@endpush

@push('scripts')
<script>
const submitForm = document.getElementById('submitForm');
const submitBtn = document.getElementById('submitBtn');

submitForm?.addEventListener('submit', () => {
    submitBtn.disabled = true;
    submitBtn.setAttribute('aria-busy', 'true');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Submitting...';
});

function handleUpload(idx, input) {
    const zone = document.getElementById(`zone_${idx}`);
    const label = document.getElementById(`zone_label_${idx}`);
    if (!zone || !label) return;

    if (input.files.length > 0) {
        zone.classList.add('is-filled');
        label.textContent = input.files[0].name;
    } else {
        zone.classList.remove('is-filled');
    }
}

const docsFree = document.getElementById('docsFree');
const docsFreeLabel = document.getElementById('docsFreeLabel');
const docsFreeZone = docsFree?.closest('.citizen-upload-zone');

docsFree?.addEventListener('change', () => {
    if (!docsFreeLabel || !docsFreeZone) return;
    if (docsFree.files.length > 0) {
        docsFreeZone.classList.add('is-filled');
        docsFreeLabel.textContent = `${docsFree.files.length} file(s) selected`;
    } else {
        docsFreeZone.classList.remove('is-filled');
        docsFreeLabel.textContent = 'Tap to upload documents (PDF, JPG, PNG)';
    }
});
</script>
@endpush
