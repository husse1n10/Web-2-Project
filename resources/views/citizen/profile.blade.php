@extends('layouts.app')
@section('title', __('My Profile'))
@section('page-title', __('My Profile'))

@section('content')
@php
    $missingFields = $user->missingCitizenProfileFields();
    $allRequests = $user->serviceRequests;
    $totalRequests = $allRequests->count();
    $completedRequests = $allRequests->where('status', 'completed')->count();

    // WhatsApp sandbox opt-in deep link + QR (lets user scan from another device)
    $waJoinCode    = 'join percent-weight';
    $waSandboxNum  = '14155238886';
    $waOptInUrl    = 'https://wa.me/' . $waSandboxNum . '?text=' . rawurlencode($waJoinCode);
    $waOptInQrSvg  = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(160)->margin(0)->generate($waOptInUrl);
    $pendingRequests = $allRequests->whereIn('status', ['pending', 'in_review', 'missing_documents', 'approved'])->count();
    $paidRequests = $paidRequests
        ?? $user->serviceRequests()->with(['service', 'office'])->where('payment_status', 'paid')->latest('updated_at')->take(5)->get();
    $identityStatus = $user->id_document ? ($user->citizen_verification_status ?? 'pending') : 'missing';
    $identityBadgeClass = match($identityStatus) {
        'approved' => 'is-success',
        'rejected', 'missing' => 'is-danger',
        default => 'is-warning',
    };
    $identityLabel = match($identityStatus) {
        'missing'  => __('No document'),
        'approved' => __('Approved'),
        'rejected' => __('Rejected'),
        default    => __('Pending'),
    };
    $canUpdateIdentityDetails = $user->isCitizen() && !$user->isCitizenIdentityApproved();
@endphp

<div class="citizen-profile-grid">
    @if(!empty($missingFields))
        <div class="card citizen-profile-alert citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <div class="citizen-profile-alert-title">{{ __('Complete your profile to submit requests') }}</div>
                    <div class="citizen-profile-alert-copy">
                        {{ __('Missing') }}: {{ implode(', ', $missingFields) }}. {{ __('Fill the fields below, then save.') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(empty($missingFields) && !$user->hasVerifiedCitizenIdentity())
        <div class="card citizen-profile-alert citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <i class="bi bi-shield-exclamation"></i>
                <div>
                    <div class="citizen-profile-alert-title">{{ __('Identity validation required') }}</div>
                    <div class="citizen-profile-alert-copy">
                        @if($user->isCitizenIdentityRejected())
                            {{ __('Your National ID document was rejected. Upload a corrected document and wait for admin approval.') }}
                        @else
                            {{ __('Your National ID document is pending admin validation. Citizen services unlock after approval.') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card citizen-profile-head citizen-reveal" data-citizen-reveal>
        <div class="card-body">
            <div class="citizen-profile-avatar-wrap">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="citizen-profile-avatar-img" referrerpolicy="no-referrer">
                @else
                    <div class="citizen-profile-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <label class="citizen-avatar-edit" for="avatar-input" title="{{ __('Change photo') }}">
                    <i class="bi bi-camera-fill"></i>
                </label>
                <form id="avatar-form" action="{{ route('citizen.profile.avatar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" hidden>
                </form>
            </div>
            <div class="citizen-profile-main">
                <h2 class="citizen-profile-name">{{ $user->name }}</h2>
                <div class="citizen-profile-email">{{ $user->email }}</div>
                <div class="citizen-profile-badges">
                    <span class="citizen-badge is-primary"><i class="bi bi-person-check me-1"></i>{{ __('Citizen Account') }}</span>
                    @if($user->phone)
                        <span class="citizen-badge"><i class="bi bi-phone me-1"></i>{{ $user->phone }}</span>
                    @endif
                    <span class="citizen-badge"><i class="bi bi-calendar3 me-1"></i>{{ __('Joined') }} {{ $user->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="citizen-stats-mini">
        <div class="card citizen-mini-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="citizen-mini-icon is-info"><i class="bi bi-file-text"></i></div>
                <div class="citizen-mini-value" data-citizen-counter="{{ $totalRequests }}">{{ $totalRequests }}</div>
                <div class="citizen-mini-label">{{ __('Total Requests') }}</div>
            </div>
        </div>
        <div class="card citizen-mini-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="citizen-mini-icon is-success"><i class="bi bi-check-circle"></i></div>
                <div class="citizen-mini-value" data-citizen-counter="{{ $completedRequests }}">{{ $completedRequests }}</div>
                <div class="citizen-mini-label">{{ __('Completed') }}</div>
            </div>
        </div>
        <div class="card citizen-mini-card citizen-reveal" data-citizen-reveal>
            <div class="card-body">
                <div class="citizen-mini-icon is-amber"><i class="bi bi-hourglass-split"></i></div>
                <div class="citizen-mini-value" data-citizen-counter="{{ $pendingRequests }}">{{ $pendingRequests }}</div>
                <div class="citizen-mini-label">{{ __('In Progress') }}</div>
            </div>
        </div>
    </div>

    <div class="citizen-profile-cols">
        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-gear me-2 text-primary"></i>{{ __('Account Information') }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('citizen.profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="citizen-form-grid">
                        <div>
                            <label class="form-label">{{ __('Full Name') }}</label>
                            <div class="citizen-input-wrap">
                                <i class="bi bi-person citizen-input-icon"></i>
                                <input
                                    type="text"
                                    id="profile_name"
                                    name="{{ $canUpdateIdentityDetails ? 'name' : '' }}"
                                    class="form-control {{ $canUpdateIdentityDetails ? '' : 'citizen-disabled-input' }}"
                                    value="{{ old('name', $user->name) }}"
                                    @disabled(!$canUpdateIdentityDetails)
                                >
                            </div>
                            <div class="form-text">
                                {{ $canUpdateIdentityDetails
                                    ? __('OCR can update this from your National ID document before admin approval.')
                                    : __('Name cannot be changed. Contact support if needed.') }}
                            </div>
                            @error('name')
                                <div class="text-danger" style="font-size:.75rem">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Email Address') }}</label>
                            <div class="citizen-input-wrap">
                                <i class="bi bi-envelope citizen-input-icon"></i>
                                <input type="email" class="form-control citizen-disabled-input" value="{{ $user->email }}" disabled>
                            </div>
                            <div class="form-text">{{ __('Email cannot be changed. Contact support if needed.') }}</div>
                        </div>
                        <div>
                            <label class="form-label">{{ __('National ID Number') }}</label>
                            <div class="citizen-input-wrap">
                                <i class="bi bi-credit-card-2-front citizen-input-icon"></i>
                                <input
                                    type="text"
                                    id="national_id"
                                    name="{{ $canUpdateIdentityDetails ? 'national_id' : '' }}"
                                    class="form-control citizen-mono {{ $canUpdateIdentityDetails ? '' : 'citizen-disabled-input' }}"
                                    value="{{ old('national_id', $user->national_id) }}"
                                    placeholder="{{ __('Not set') }}"
                                    @disabled(!$canUpdateIdentityDetails)
                                >
                            </div>
                            <div class="form-text">
                                {{ $canUpdateIdentityDetails
                                    ? __('OCR can update this from your National ID document before admin approval.')
                                    : __('National ID cannot be changed. Contact support if needed.') }}
                            </div>
                            @error('national_id')
                                <div class="text-danger" style="font-size:.75rem">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('National ID Document') }}</label>
                            @if($user->id_document && $user->isCitizenIdentityApproved())
                                <div class="citizen-id-verified">
                                    <i class="bi bi-patch-check-fill"></i>
                                    <span>{{ __('ID document approved by admin') }}</span>
                                </div>
                            @else
                                @if($user->id_document)
                                    <div class="citizen-id-{{ $user->isCitizenIdentityRejected() ? 'missing' : 'pending' }} mb-2">
                                        <i class="bi bi-{{ $user->isCitizenIdentityRejected() ? 'x-circle-fill' : 'hourglass-split' }}"></i>
                                        <span>
                                            @if($user->isCitizenIdentityRejected())
                                                {{ __('ID document rejected. Upload a corrected document.') }}
                                            @else
                                                {{ __('ID document uploaded. Waiting for admin validation.') }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                <label class="citizen-upload-zone" id="idUploadZone" for="national_id_doc">
                                    <input type="file" id="national_id_doc" name="national_id_document" accept=".jpg,.jpeg,.png,.pdf">
                                    <span class="citizen-upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                    <span class="citizen-upload-title">{{ $user->id_document ? __('Upload replacement document') : __('Upload national ID document') }}</span>
                                    <span class="citizen-upload-sub">{{ __('JPG, PNG or PDF, max 5 MB') }}</span>
                                </label>
                                <div id="uploadPreview" class="citizen-upload-preview" style="display:none">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <span id="uploadName"></span>
                                </div>
                                <div class="form-text">{{ __('Select the file, then press Save Changes. Admin approval is required before using citizen services.') }}</div>
                                <div id="ocrStatus" class="citizen-ocr-status" role="status" aria-live="polite"></div>
                                @error('national_id_document')
                                    <div class="text-danger" style="font-size:.75rem">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                        {{-- Phone Verification (Twilio WhatsApp OTP) --}}
                        <div>
                            <label class="form-label d-flex align-items-center gap-2">
                                {{ __('Phone Number') }}
                                @if($user->phone_verified_at)
                                    <span class="citizen-badge is-success" style="font-size:.65rem"><i class="bi bi-patch-check-fill me-1"></i>{{ __('Verified') }}</span>
                                @elseif($user->phone)
                                    <span class="citizen-badge" style="font-size:.65rem;color:#B45309;background:#FFFBEB;border-color:#FDE68A"><i class="bi bi-exclamation-circle me-1"></i>{{ __('Not verified') }}</span>
                                @endif
                            </label>

                            {{-- Step 1: Enter phone + Send Code --}}
                            <div id="fb-step-1">
                                <div class="d-flex gap-2">
                                    <div class="citizen-input-wrap flex-grow-1">
                                        <i class="bi bi-phone citizen-input-icon"></i>
                                        <input type="tel" id="fb-phone-input" class="form-control"
                                               value="{{ $user->phone }}" placeholder="+96170551180" maxlength="25" autocomplete="tel">
                                    </div>
                                <button type="button" id="fb-send-btn" class="btn btn-outline-primary btn-sm text-nowrap">
                                        <i class="bi bi-whatsapp me-1"></i>{{ __('Send via WhatsApp') }}
                                    </button>
                                </div>
                                <div class="form-text mb-2">
                                    {{ __('Include country code, no spaces — e.g.') }} <code>+961XXXXXXXX</code>.
                                </div>

                                <details class="citizen-wa-optin">
                                    <summary>
                                        <i class="bi bi-info-circle me-1"></i>{{ __('First-time setup — opt in to WhatsApp') }}
                                    </summary>
                                    <div class="citizen-wa-optin-body">
                                        <div class="citizen-wa-optin-qr">
                                            {!! $waOptInQrSvg !!}
                                            <div class="citizen-wa-optin-qr-cap">{{ __('Scan with phone camera') }}</div>
                                        </div>
                                        <div class="citizen-wa-optin-info">
                                            <p class="mb-2">
                                                {{ __('Before your first verification, opt in to our WhatsApp sandbox so messages can reach you.') }}
                                            </p>
                                            <ol class="mb-2 ps-3" style="font-size:.78rem">
                                                <li>{{ __('Scan the QR with your phone (or tap the button below on mobile)') }}</li>
                                                <li>{{ __('WhatsApp opens with the message pre-filled') }}</li>
                                                <li>{!! __('Hit <strong>Send</strong> — you\'ll get a confirmation reply') !!}</li>
                                            </ol>
                                            <a href="{{ $waOptInUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                                                <i class="bi bi-whatsapp me-1"></i>{{ __('Open WhatsApp') }}
                                            </a>
                                            <div class="citizen-wa-optin-manual">
                                                {!! __('Or manually send <code>join percent-weight</code> to <code>+1 415 523 8886</code>.') !!}
                                            </div>
                                        </div>
                                    </div>
                                </details>
                                <div id="fb-send-error" class="text-danger mt-1" style="font-size:.75rem;display:none"></div>
                            </div>

                            {{-- Step 2: Enter OTP --}}
                            <div id="fb-step-2" class="citizen-otp-box d-none">
                                <div class="citizen-otp-info">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>{{ __('Code sent on WhatsApp! Enter the 6-digit code below.') }}</span>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <input type="text" id="fb-otp-input" class="form-control citizen-otp-input"
                                           maxlength="6" placeholder="_ _ _ _ _ _" autocomplete="one-time-code" inputmode="numeric">
                                    <button type="button" id="fb-verify-btn" class="btn btn-success btn-sm text-nowrap">
                                        <i class="bi bi-check2 me-1"></i>{{ __('Verify') }}
                                    </button>
                                </div>
                                <div id="fb-otp-error" class="text-danger mt-1" style="font-size:.75rem;display:none"></div>
                                <button type="button" id="fb-restart-btn" style="font-size:.73rem;color:#64748B;background:none;border:none;padding:0;margin-top:.3rem;cursor:pointer">
                                    {{ __('Wrong number? Start over') }}
                                </button>
                            </div>

                            {{-- Step 3: Success flash --}}
                            <div id="fb-step-3" class="citizen-id-verified d-none">
                                <i class="bi bi-patch-check-fill"></i>
                                <span>{{ __('Phone verified! Refreshing...') }}</span>
                            </div>
                        </div>
                        <div class="citizen-password-zone">
                            <label class="form-label citizen-password-label">{{ __('Change Password') }}</label>
                            <div class="mb-2">
                                <label class="form-label">{{ __('Current Password') }}</label>
                                <input type="password" name="current_password" class="form-control" placeholder="{{ __('Enter your current password') }}" autocomplete="current-password">
                                @error('current_password')
                                    <div class="text-danger" style="font-size:.75rem">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="citizen-password-grid">
                                <div>
                                    <label class="form-label">{{ __('New Password') }}</label>
                                    <input type="password" name="password" class="form-control" placeholder="{{ __('At least 8 characters') }}" autocomplete="new-password">
                                    @error('password')
                                        <div class="text-danger" style="font-size:.75rem">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="form-label">{{ __('Confirm New Password') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Repeat password') }}" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-card-text me-2 text-primary"></i>{{ __('Identity Information') }}</span>
            </div>
            <div class="card-body">
                <div class="citizen-info-row">
                    <span class="citizen-info-label">{{ __('National ID') }}</span>
                    <span class="citizen-info-value citizen-mono">{{ $user->national_id ?? '-' }}</span>
                </div>
                <div class="citizen-info-row">
                    <span class="citizen-info-label">{{ __('Account Type') }}</span>
                    <span class="citizen-info-value">{{ __('Citizen') }}</span>
                </div>
                <div class="citizen-info-row">
                    <span class="citizen-info-label">{{ __('Account Status') }}</span>
                    <span class="citizen-badge {{ $user->is_active ? 'is-success' : 'is-muted' }}">{{ $user->is_active ? __('Active') : __('Inactive') }}</span>
                </div>
                <div class="citizen-info-row">
                    <span class="citizen-info-label">{{ __('Member Since') }}</span>
                    <span class="citizen-info-value">{{ $user->created_at->format('F d, Y') }}</span>
                </div>
                <div class="citizen-info-row">
                    <span class="citizen-info-label">{{ __('ID Document') }}</span>
                    <span class="citizen-badge {{ $identityBadgeClass }}"><i class="bi bi-shield-check me-1"></i>{{ $identityLabel }}</span>
                </div>
                @if($user->citizen_verification_notes)
                    <div class="citizen-info-row">
                        <span class="citizen-info-label">{{ __('Admin Note') }}</span>
                        <span class="citizen-info-value">{{ $user->citizen_verification_notes }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-credit-card me-2 text-primary"></i>{{ __('Payment History') }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($paidRequests as $pr)
                    <a href="{{ route('citizen.requests.show', $pr) }}" class="citizen-activity-link">
                        <div class="citizen-activity-icon is-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="citizen-activity-main">
                            <div class="citizen-activity-title">{{ $pr->resolved_service_name }}</div>
                            <div class="citizen-activity-sub">{{ $pr->office->name }} &middot; {{ ucfirst($pr->payment_method ?? 'card') }} &middot; {{ $pr->updated_at->format('M d, Y') }}</div>
                        </div>
                        <div class="citizen-activity-amount">{{ $pr->formatted_recorded_amount }}</div>
                    </a>
                @empty
                    <div class="citizen-panel-empty">
                        <i class="bi bi-credit-card"></i>
                        <p>{{ __('No payments yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Recent Activity') }}</span>
                <a href="{{ route('citizen.requests') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                @forelse($requests as $req)
                    <a href="{{ route('citizen.requests.show', $req) }}" class="citizen-activity-link">
                        <div class="citizen-activity-icon is-primary">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="citizen-activity-main">
                            <div class="citizen-activity-title">{{ $req->resolved_service_name }}</div>
                            <div class="citizen-activity-sub">{{ $req->office->name }} &middot; {{ $req->created_at->diffForHumans() }}</div>
                        </div>
                        <x-status-pill :status="$req->status" />
                    </a>
                @empty
                    <div class="citizen-panel-empty">
                        <i class="bi bi-clock-history"></i>
                        <p>{{ __('No recent activity.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CITIZEN PROFILE — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

body.es-role-citizen .citizen-profile-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

body.es-role-citizen .citizen-profile-alert {
    border-color: rgba(252,211,77,0.5);
    background: rgba(255,251,235,0.7);
    backdrop-filter: blur(8px);
}

body.es-role-citizen .citizen-profile-alert .card-body {
    display: flex;
    align-items: flex-start;
    gap: .68rem;
}

body.es-role-citizen .citizen-profile-alert i {
    color: #B45309;
    font-size: 1rem;
    margin-top: .05rem;
}

body.es-role-citizen .citizen-profile-alert-title {
    font-size: .9rem;
    font-weight: 700;
    color: #92400E;
    margin-bottom: .18rem;
}

body.es-role-citizen .citizen-profile-alert-copy {
    font-size: .79rem;
    color: #9A3412;
}

body.es-role-citizen .citizen-profile-head .card-body {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    flex-wrap: wrap;
    padding: 1.5rem;
}

/* ── Avatar with animated gradient ring ── */
body.es-role-citizen .citizen-profile-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-profile-avatar-wrap::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 999px;
    background: conic-gradient(from 0deg, #0EA5E9, #6366F1, #8B5CF6, #EC4899, #F97316, #0EA5E9);
    animation: citizenAvatarSpin 4s linear infinite;
    z-index: 0;
}

@keyframes citizenAvatarSpin {
    to { transform: rotate(360deg); }
}

body.es-role-citizen .citizen-profile-avatar {
    width: 5rem;
    height: 5rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 100%);
    border: 3px solid #fff;
    box-shadow: 0 12px 28px rgba(14,165,233,0.25);
    position: relative;
    z-index: 1;
}

body.es-role-citizen .citizen-profile-avatar-img {
    width: 5rem;
    height: 5rem;
    border-radius: 999px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 12px 28px rgba(14,165,233,0.25);
    position: relative;
    z-index: 1;
}

body.es-role-citizen .citizen-avatar-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff;
    font-size: .7rem;
    cursor: pointer;
    border: 2.5px solid #fff;
    box-shadow: 0 3px 10px rgba(14,165,233,0.3);
    transition: all .25s cubic-bezier(.4,0,.2,1);
    z-index: 2;
}

body.es-role-citizen .citizen-avatar-edit:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 16px rgba(14,165,233,0.4);
}

body.es-role-citizen .citizen-profile-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-profile-name {
    margin: 0 0 .15rem;
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    background: linear-gradient(135deg, #0F172A 0%, #0EA5E9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-citizen .citizen-profile-email {
    font-size: .84rem;
    color: #64748B;
    margin-bottom: .65rem;
}

body.es-role-citizen .citizen-profile-badges {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}

body.es-role-citizen .citizen-badge {
    display: inline-flex;
    align-items: center;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .69rem;
    font-weight: 600;
    color: #475569;
    background: rgba(238,242,255,0.6);
    border: 1px solid rgba(226,232,240,0.6);
    backdrop-filter: blur(4px);
}

body.es-role-citizen .citizen-badge.is-primary {
    color: #0284C7;
    background: rgba(224,242,254,0.5);
    border-color: rgba(14,165,233,0.2);
}

body.es-role-citizen .citizen-badge.is-success {
    color: #047857;
    background: rgba(236,253,245,0.5);
    border-color: rgba(16,185,129,0.2);
}

body.es-role-citizen .citizen-badge.is-muted {
    color: #64748B;
    background: rgba(241,245,249,0.5);
    border-color: rgba(226,232,240,0.5);
}

body.es-role-citizen .citizen-badge.is-warning {
    color: #B45309;
    background: rgba(255,251,235,0.65);
    border-color: rgba(251,191,36,0.28);
}

body.es-role-citizen .citizen-badge.is-danger {
    color: #B91C1C;
    background: rgba(254,242,242,0.65);
    border-color: rgba(248,113,113,0.28);
}

/* ── Stat cards with gradient icons ── */
body.es-role-citizen .citizen-stats-mini {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
}

body.es-role-citizen .citizen-mini-card .card-body {
    padding: 1rem;
    text-align: center;
}

body.es-role-citizen .citizen-mini-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(14,165,233,0.1);
}

body.es-role-citizen .citizen-mini-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: .7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    margin-bottom: .55rem;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

body.es-role-citizen .citizen-mini-icon.is-info {
    background: linear-gradient(135deg, #0EA5E9, #0284C7);
}

body.es-role-citizen .citizen-mini-icon.is-success {
    background: linear-gradient(135deg, #10B981, #059669);
}

body.es-role-citizen .citizen-mini-icon.is-amber {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

body.es-role-citizen .citizen-mini-value {
    font-size: 1.8rem;
    line-height: 1;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0F172A;
}

body.es-role-citizen .citizen-mini-label {
    margin-top: .28rem;
    font-size: .72rem;
    color: #94A3B8;
    font-weight: 500;
}

body.es-role-citizen .citizen-profile-cols {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

body.es-role-citizen .citizen-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: .95rem;
}

body.es-role-citizen .citizen-input-wrap {
    position: relative;
}

body.es-role-citizen .citizen-input-icon {
    position: absolute;
    left: .78rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: .83rem;
    pointer-events: none;
    transition: color .2s ease;
}

body.es-role-citizen .citizen-input-wrap:focus-within .citizen-input-icon {
    color: #0EA5E9;
}

body.es-role-citizen .citizen-input-wrap .form-control {
    padding-left: 2.2rem;
}

body.es-role-citizen .citizen-disabled-input {
    background: rgba(248,250,252,0.7) !important;
    cursor: not-allowed;
    opacity: .75;
}

body.es-role-citizen .citizen-upload-zone {
    border: 1.5px dashed rgba(191,219,254,0.8);
    border-radius: .85rem;
    padding: 1.1rem;
    text-align: center;
    cursor: pointer;
    background: rgba(248,250,255,0.5);
    backdrop-filter: blur(4px);
    transition: all .25s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-upload-zone:hover,
body.es-role-citizen .citizen-upload-zone.drag {
    border-color: #60A5FA;
    background: rgba(239,246,255,0.6);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(14,165,233,0.08);
}

body.es-role-citizen .citizen-upload-zone input {
    display: none;
}

body.es-role-citizen .citizen-upload-icon {
    font-size: 1.35rem;
    color: #0EA5E9;
    margin-bottom: .3rem;
}

body.es-role-citizen .citizen-upload-title {
    font-size: .8rem;
    font-weight: 700;
    color: #0F172A;
}

body.es-role-citizen .citizen-upload-sub {
    font-size: .72rem;
    color: #64748B;
}

body.es-role-citizen .citizen-upload-preview {
    display: none;
    align-items: center;
    gap: .5rem;
    margin-top: .55rem;
    padding: .5rem .7rem;
    border-radius: .62rem;
    border: 1px solid rgba(16,185,129,0.2);
    background: rgba(236,253,245,0.6);
    color: #047857;
    font-size: .76rem;
}

body.es-role-citizen .citizen-ocr-status {
    display: none;
    margin-top: .55rem;
    border-radius: .62rem;
    padding: .5rem .7rem;
    font-size: .76rem;
    font-weight: 600;
}

body.es-role-citizen .citizen-password-zone {
    border-top: 1px solid rgba(226,232,240,0.5);
    padding-top: 1rem;
}

body.es-role-citizen .citizen-password-label {
    margin-bottom: .55rem;
    font-size: .82rem;
    color: #475569;
}

body.es-role-citizen .citizen-password-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: .7rem;
}

body.es-role-citizen .citizen-otp-box {
    background: rgba(240,253,244,0.6);
    border: 1px solid rgba(16,185,129,0.2);
    border-radius: .7rem;
    padding: .8rem;
    backdrop-filter: blur(4px);
}

/* WhatsApp opt-in helper (collapsible) */
body.es-role-citizen .citizen-wa-optin {
    border: 1px solid rgba(37,211,102,0.25);
    background: rgba(240,253,244,0.5);
    border-radius: .7rem;
    padding: 0;
    margin-top: .35rem;
    overflow: hidden;
}
body.es-role-citizen .citizen-wa-optin > summary {
    list-style: none;
    cursor: pointer;
    padding: .55rem .8rem;
    font-size: .76rem;
    font-weight: 600;
    color: #047857;
    user-select: none;
    display: flex;
    align-items: center;
    transition: background .15s;
}
body.es-role-citizen .citizen-wa-optin > summary::-webkit-details-marker { display: none; }
body.es-role-citizen .citizen-wa-optin > summary::after {
    content: '\F282'; /* bi-chevron-down */
    font-family: 'bootstrap-icons';
    margin-left: auto;
    transition: transform .2s;
    font-size: .8rem;
}
body.es-role-citizen .citizen-wa-optin[open] > summary::after { transform: rotate(180deg); }
body.es-role-citizen .citizen-wa-optin > summary:hover { background: rgba(37,211,102,0.08); }
body.es-role-citizen .citizen-wa-optin-body {
    display: flex;
    gap: 1rem;
    padding: .85rem .8rem .9rem;
    border-top: 1px solid rgba(37,211,102,0.15);
    align-items: flex-start;
}
body.es-role-citizen .citizen-wa-optin-qr {
    flex-shrink: 0;
    text-align: center;
}
body.es-role-citizen .citizen-wa-optin-qr svg {
    width: 110px;
    height: 110px;
    background: #fff;
    padding: 6px;
    border-radius: .5rem;
    border: 1px solid rgba(15,23,42,0.06);
}
body.es-role-citizen .citizen-wa-optin-qr-cap {
    margin-top: .3rem;
    font-size: .65rem;
    color: #64748B;
}
body.es-role-citizen .citizen-wa-optin-info {
    flex: 1;
    min-width: 0;
    font-size: .8rem;
    color: #334155;
}
body.es-role-citizen .citizen-wa-optin-info code {
    background: rgba(15,23,42,0.06);
    padding: .05rem .3rem;
    border-radius: .25rem;
    font-size: .72rem;
    color: #0F172A;
}
body.es-role-citizen .citizen-wa-optin-manual {
    margin-top: .55rem;
    font-size: .7rem;
    color: #64748B;
}
@media (max-width: 575.98px) {
    body.es-role-citizen .citizen-wa-optin-body {
        flex-direction: column;
        align-items: stretch;
    }
    body.es-role-citizen .citizen-wa-optin-qr {
        align-self: center;
    }
}

body.es-role-citizen .citizen-otp-info {
    display: flex;
    align-items: center;
    gap: .45rem;
    font-size: .78rem;
    color: #047857;
    font-weight: 600;
}

body.es-role-citizen .citizen-otp-input {
    font-size: 1.2rem;
    letter-spacing: .3em;
    font-weight: 700;
    text-align: center;
    max-width: 160px;
}

body.es-role-citizen .citizen-id-verified,
body.es-role-citizen .citizen-id-missing,
body.es-role-citizen .citizen-id-pending {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .65rem .9rem;
    border-radius: .7rem;
    font-size: .8rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
}

body.es-role-citizen .citizen-id-verified {
    background: rgba(236,253,245,0.6);
    border: 1px solid rgba(16,185,129,0.2);
    color: #047857;
}

body.es-role-citizen .citizen-id-missing {
    background: rgba(255,247,237,0.6);
    border: 1px solid rgba(234,88,12,0.15);
    color: #9A3412;
}

body.es-role-citizen .citizen-id-pending {
    background: rgba(255,251,235,0.65);
    border: 1px solid rgba(251,191,36,0.22);
    color: #B45309;
}

body.es-role-citizen .citizen-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
    padding: .55rem 0;
    border-bottom: 1px solid rgba(226,232,240,0.5);
}

body.es-role-citizen .citizen-info-row:last-child {
    border-bottom: 0;
}

body.es-role-citizen .citizen-info-label {
    font-size: .77rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-info-value {
    font-size: .8rem;
    color: #0F172A;
    font-weight: 600;
}

body.es-role-citizen .citizen-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    letter-spacing: .04em;
}

/* ── Activity links ── */
body.es-role-citizen .citizen-activity-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .82rem 1rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    text-decoration: none;
    color: inherit;
    transition: all .22s cubic-bezier(.4,0,.2,1);
}

body.es-role-citizen .citizen-activity-link:last-child {
    border-bottom: 0;
}

body.es-role-citizen .citizen-activity-link:hover {
    background: rgba(224,242,254,0.25);
    color: inherit;
    transform: translateX(3px);
}

body.es-role-citizen .citizen-activity-icon {
    width: 2.3rem;
    height: 2.3rem;
    border-radius: .7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(0,0,0,0.06);
}

body.es-role-citizen .citizen-activity-icon.is-success {
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    color: #047857;
    border: 1px solid rgba(16,185,129,0.15);
}

body.es-role-citizen .citizen-activity-icon.is-primary {
    background: linear-gradient(135deg, #E0F2FE, #BAE6FD);
    color: #0369A1;
    border: 1px solid rgba(14,165,233,0.15);
}

body.es-role-citizen .citizen-activity-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-activity-title {
    font-size: .82rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-activity-sub {
    margin-top: .15rem;
    font-size: .72rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-activity-amount {
    font-size: .88rem;
    font-weight: 800;
    color: #059669;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-panel-empty {
    padding: 2.2rem 1rem;
    text-align: center;
    color: #94A3B8;
}

body.es-role-citizen .citizen-panel-empty i {
    font-size: 2rem;
    color: #CBD5E1;
    display: block;
    margin-bottom: .5rem;
}

body.es-role-citizen .citizen-panel-empty p {
    margin: 0;
    font-size: .82rem;
}

@media (min-width: 640px) {
    body.es-role-citizen .citizen-password-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (min-width: 992px) {
    body.es-role-citizen .citizen-profile-cols {
        grid-template-columns: 1fr 1fr;
    }

    body.es-role-citizen .citizen-profile-cols .card:last-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 767.98px) {
    body.es-role-citizen .citizen-stats-mini {
        grid-template-columns: 1fr;
    }
}

@media (prefers-reduced-motion: reduce) {
    body.es-role-citizen .citizen-profile-avatar-wrap::before {
        animation: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Avatar auto-upload
const avatarInput = document.getElementById('avatar-input');
if (avatarInput) {
    avatarInput.addEventListener('change', () => {
        if (avatarInput.files.length) {
            document.getElementById('avatar-form').submit();
        }
    });
}

const zone = document.getElementById('idUploadZone');
const input = document.getElementById('national_id_doc');
const preview = document.getElementById('uploadPreview');
const nameEl = document.getElementById('uploadName');
const ocrStatus = document.getElementById('ocrStatus');
const profileNameInput = document.getElementById('profile_name');
const nationalIdInput = document.getElementById('national_id');
const extractEndpoint = '{{ route('citizen.profile.id-extract') }}';
const csrfToken = '{{ csrf_token() }}';

let extractionRunId = 0;

function setStatus(message, type = 'info') {
    if (!ocrStatus) return;

    const styles = {
        info: ['#EFF6FF', '#1E4080'],
        success: ['#ECFDF5', '#0D7A4E'],
        warn: ['#FFF7ED', '#9A3412'],
        error: ['#FFF1F2', '#9F1239'],
    };

    const [bg, color] = styles[type] || styles.info;
    ocrStatus.style.display = 'block';
    ocrStatus.style.background = bg;
    ocrStatus.style.color = color;
    ocrStatus.textContent = message;
}

async function runExtraction(file) {
    if (!file) return;

    const myRunId = ++extractionRunId;
    setStatus('Reading ID data...', 'info');

    const formData = new FormData();
    formData.append('national_id_document', file);

    try {
        const response = await fetch(extractEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => ({}));
        if (myRunId !== extractionRunId) return;

        if (!response.ok) {
            setStatus(payload.message || 'Could not extract fields from this ID file.', 'warn');
            return;
        }

        const extractedName = [
            payload?.data?.first_name,
            payload?.data?.last_name,
        ].filter(Boolean).join(' ').trim();

        if (extractedName && profileNameInput && !profileNameInput.disabled) {
            profileNameInput.value = extractedName;
        }

        if (payload?.data?.national_id && nationalIdInput && !nationalIdInput.disabled) {
            nationalIdInput.value = payload.data.national_id;
        }

        setStatus(payload.message || 'ID fields extracted successfully.', 'success');
    } catch (error) {
        setStatus('Extraction request failed. Please check your connection and try again.', 'error');
    }
}

function handleSelectedFile(file) {
    if (!file) return;

    if (nameEl && preview) {
        nameEl.textContent = file.name;
        preview.style.display = 'flex';
    }

    runExtraction(file);
}

input?.addEventListener('change', () => {
    handleSelectedFile(input.files[0]);
});

zone?.addEventListener('dragover', (event) => {
    event.preventDefault();
    zone.classList.add('drag');
});

zone?.addEventListener('dragleave', () => {
    zone.classList.remove('drag');
});

zone?.addEventListener('drop', (event) => {
    event.preventDefault();
    zone.classList.remove('drag');

    if (event.dataTransfer.files[0]) {
        input.files = event.dataTransfer.files;
        handleSelectedFile(event.dataTransfer.files[0]);
    }
});
</script>
@endpush

@push('scripts')
{{-- Phone verification via Twilio SMS OTP --}}
@php
    $smsDefaultCountryCode = (string) config('services.sms.default_country_code', '+961');
    $smsDefaultCountryCode = str_starts_with($smsDefaultCountryCode, '+')
        ? $smsDefaultCountryCode
        : '+' . $smsDefaultCountryCode;
@endphp
<script>
(function () {
    const SEND_URL   = '{{ route("citizen.profile.phone.send") }}';
    const VERIFY_URL = '{{ route("citizen.profile.phone.verify") }}';
    const CSRF       = '{{ csrf_token() }}';
    const DEFAULT_COUNTRY_CODE = @json($smsDefaultCountryCode);

    const step1   = document.getElementById('fb-step-1');
    const step2   = document.getElementById('fb-step-2');
    const step3   = document.getElementById('fb-step-3');
    const sendBtn = document.getElementById('fb-send-btn');
    const verBtn  = document.getElementById('fb-verify-btn');
    const restart = document.getElementById('fb-restart-btn');
    const phoneIn = document.getElementById('fb-phone-input');
    const otpIn   = document.getElementById('fb-otp-input');
    const sendErr = document.getElementById('fb-send-error');
    const otpErr  = document.getElementById('fb-otp-error');

    if (!sendBtn || !verBtn) return;

    const showErr = (el, msg) => { el.textContent = msg; el.style.display = 'block'; };
    const hideErr = (el) => { el.style.display = 'none'; };
    const setBtn  = (btn, html, disabled) => { btn.disabled = disabled; btn.innerHTML = html; };
    const normalizePhone = (raw) => {
        const value = String(raw ?? '').trim();
        if (!value || !/^[\d\s()+-]+$/.test(value)) return null;
        if ((value.match(/\+/g) ?? []).length > 1) return null;
        if (value.slice(1).includes('+')) return null;

        let clean = value.replace(/[^\d+]/g, '');
        if (!clean) return null;

        if (clean.startsWith('00')) {
            clean = `+${clean.slice(2)}`;
        }

        if (!clean.startsWith('+')) {
            if (clean.startsWith('0')) clean = clean.slice(1);
            clean = `${DEFAULT_COUNTRY_CODE}${clean}`;
        }

        return /^\+\d{8,15}$/.test(clean) ? clean : null;
    };

    sendBtn.addEventListener('click', async () => {
        hideErr(sendErr);
        const rawPhone = (phoneIn?.value ?? '').trim();
        if (!rawPhone) { showErr(sendErr, 'Please enter your phone number.'); return; }

        const phone = normalizePhone(rawPhone);
        if (!phone) { showErr(sendErr, 'Enter a valid phone number, for example +96171123456.'); return; }

        setBtn(sendBtn, '<span class="spinner-border spinner-border-sm me-1"></span>Sending...', true);

        try {
            const resp = await fetch(SEND_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone }),
            });
            const data = await resp.json().catch(() => null);

            if (!resp.ok || !data?.sent) {
                const msg = data?.errors?.phone?.[0] || data?.message || 'Could not send code. Check the number format and try again.';
                throw new Error(msg);
            }

            step1.classList.add('d-none');
            step2.classList.remove('d-none');
            if (phoneIn) phoneIn.value = data.phone || phone;
            otpIn?.focus();
            window.showToast?.(data.message || 'Verification code sent via WhatsApp.', 'success');
        } catch (err) {
            setBtn(sendBtn, '<i class="bi bi-send me-1"></i>Send Code', false);
            showErr(sendErr, err.message || 'Enter a valid phone number, for example +96171123456.');
        }
    });

    verBtn.addEventListener('click', async () => {
        hideErr(otpErr);
        const code = (otpIn?.value ?? '').trim();
        if (code.length !== 6) { showErr(otpErr, 'Enter the 6-digit code.'); return; }

        setBtn(verBtn, '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...', true);

        try {
            const resp = await fetch(VERIFY_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ otp: code }),
            });
            const data = await resp.json().catch(() => null);

            if (!resp.ok || !data?.verified) {
                const msg = data?.errors?.otp?.[0] || 'Invalid or expired code. Please try again.';
                throw new Error(msg);
            }

            step2.classList.add('d-none');
            step3.classList.remove('d-none');
            setTimeout(() => window.location.reload(), 1200);
        } catch (err) {
            setBtn(verBtn, '<i class="bi bi-check2 me-1"></i>Verify', false);
            showErr(otpErr, err.message || 'Invalid code. Please try again.');
        }
    });

    restart?.addEventListener('click', () => {
        step2.classList.add('d-none');
        step1.classList.remove('d-none');
        if (otpIn) otpIn.value = '';
        setBtn(sendBtn, '<i class="bi bi-send me-1"></i>Send Code', false);
    });
})();
</script>
@endpush

@endsection
