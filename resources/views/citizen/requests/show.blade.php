@extends('layouts.app')
@section('title', __('Request') . ' ' . $serviceRequest->reference_number)
@section('page-title', __('Request Details'))

@section('content')
@php
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
    $feedbackFormHasErrors = $errors->has('office_id')
        || $errors->has('service_request_id')
        || $errors->has('rating')
        || $errors->has('comment')
        || $errors->has('feedback');
@endphp
<div class="card mb-3 citizen-reveal" data-citizen-reveal>
    <div class="card-body citizen-request-head">
        <div>
            <span class="citizen-request-head-kicker">{{ __('Service Request') }}</span>
            <h5 class="citizen-request-head-title">{{ $serviceRequest->resolved_service_name }}</h5>
            <div class="citizen-request-head-sub">{{ $serviceRequest->office->name }}</div>
            <code>{{ $serviceRequest->reference_number }}</code>
        </div>
        <x-status-pill :status="$serviceRequest->status" />
    </div>
</div>

<div class="citizen-request-detail-grid">
    <div class="citizen-request-left-col">
        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Status History') }}</span>
            </div>
            <div class="card-body">
                @php
                    $statusIcons = [
                        'pending' => 'bi-hourglass-split',
                        'in_review' => 'bi-search',
                        'missing_documents' => 'bi-exclamation-triangle',
                        'approved' => 'bi-check2-circle',
                        'rejected' => 'bi-x-circle',
                        'completed' => 'bi-check-circle-fill',
                    ];
                @endphp

                @forelse($serviceRequest->statusLogs as $log)
                    <div class="citizen-timeline-item">
                        <div class="citizen-timeline-rail">
                            <div class="citizen-timeline-dot">
                                <i class="bi {{ $statusIcons[$log->to_status] ?? 'bi-arrow-right' }}"></i>
                            </div>
                            @if(!$loop->last)
                                <div class="citizen-timeline-line"></div>
                            @endif
                        </div>
                        <div class="citizen-timeline-content">
                            <div class="citizen-timeline-title">{{ __(ucfirst(str_replace('_', ' ', $log->to_status))) }}</div>
                            <div class="citizen-timeline-time">{{ $log->created_at->diffForHumans() }}</div>
                            @if($log->comment)
                                <div class="citizen-timeline-note">{{ $log->comment }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="citizen-muted-note m-0">{{ __('No updates yet.') }}</p>
                @endforelse
            </div>
        </div>

        @if(in_array($serviceRequest->status, ['missing_documents', 'rejected'], true))
            <div class="card citizen-reveal citizen-resubmit-card" data-citizen-reveal>
                <div class="card-header">
                    <span class="card-title">
                        <i class="bi bi-arrow-counterclockwise me-2 text-warning"></i>
                        @if($serviceRequest->status === 'missing_documents')
                            {{ __('Action Needed — Upload Missing Documents') }}
                        @else
                            {{ __('Request Rejected — Resubmit with Corrections') }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if($serviceRequest->office_notes)
                        <div class="citizen-resubmit-note">
                            <strong><i class="bi bi-chat-left-text me-1"></i>{{ __('Office note:') }}</strong>
                            <p class="mb-0 mt-1">{{ $serviceRequest->office_notes }}</p>
                        </div>
                    @endif

                    @error('resubmit')
                        <div class="alert alert-danger" style="font-size:.8rem">{{ $message }}</div>
                    @enderror
                    @error('documents')
                        <div class="alert alert-danger" style="font-size:.8rem">{{ $message }}</div>
                    @enderror
                    @error('documents.*')
                        <div class="alert alert-danger" style="font-size:.8rem">{{ $message }}</div>
                    @enderror

                    <form action="{{ route('citizen.requests.resubmit', $serviceRequest) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('Replacement Documents') }} <span class="text-danger">*</span></label>
                            <input type="file" name="documents[]" class="form-control" multiple
                                   accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="form-text">{{ __('JPG, PNG, or PDF — max 10 MB each.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Note to the office (optional)') }}</label>
                            <textarea name="comment" class="form-control" rows="2" maxlength="1000"
                                      placeholder="{{ __('Explain what was corrected or attached…') }}"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-upload me-1"></i> {{ __('Resubmit for Review') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-paperclip me-2 text-primary"></i>{{ __('Documents') }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($serviceRequest->documents as $doc)
                    <div class="citizen-doc-row">
                        <div class="citizen-doc-icon">
                            <i class="bi bi-file-earmark"></i>
                        </div>
                        <div class="citizen-doc-main">
                            <div class="citizen-doc-name">{{ $doc->original_name }}</div>
                            <div class="citizen-doc-sub">{{ __('Uploaded by') }} {{ __(ucfirst($doc->uploaded_by)) }}</div>
                        </div>
                        <a href="{{ route('citizen.documents.download', [$serviceRequest, $doc->id]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @empty
                    <div class="citizen-panel-empty">
                        <i class="bi bi-folder2-open"></i>
                        <p>{{ __('No documents uploaded.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-chat-dots me-2 text-primary"></i>{{ __('Messages') }}</span>
            </div>
            <div class="chat-box" id="chatBox">
                @if($serviceRequest->messages->isEmpty())
                    <div class="citizen-chat-empty">{{ __('No messages yet. Start the conversation.') }}</div>
                @else
                    @foreach($serviceRequest->messages as $msg)
                        @php $mine = $msg->sender_id === auth()->id(); @endphp
                        <div class="msg {{ $mine ? 'mine' : 'theirs' }}">
                            <div class="msg-av {{ $mine ? 'av-me' : 'av-other' }}">
                                {{ strtoupper(substr($msg->sender->name, 0, 1)) }}
                            </div>
                            <div class="msg-bubble">
                                <div class="msg-name">{{ $mine ? __('You') : $msg->sender->name }}</div>
                                <p>{{ $msg->body }}</p>
                                <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="citizen-chat-input-wrap">
                <div class="d-flex gap-2">
                    <input type="text" id="chatInput" class="form-control form-control-sm" placeholder="{{ __('Type a message...') }}" style="flex:1">
                    <button id="sendBtn" class="btn btn-primary btn-sm" style="flex-shrink:0"><i class="bi bi-send"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="citizen-request-right-col">
        @if($serviceRequest->qr_code)
            <div class="card text-center citizen-reveal" data-citizen-reveal>
                <div class="card-body">
                    <div class="citizen-side-card-title">{{ __('Track via QR Code') }}</div>
                    <img src="{{ Storage::disk('public')->url($serviceRequest->qr_code) }}" alt="QR Code" class="citizen-qr-image">
                    <div class="citizen-muted-note mt-2">{{ __('Scan to check request status.') }}</div>
                </div>
            </div>
        @endif

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title">{{ __('Payment') }}</span>
            </div>
            <div class="card-body">
                <div class="citizen-side-row">
                    <span>{{ __('Amount Due') }}</span>
                    <strong>{{ $serviceRequest->formatted_service_price }}</strong>
                </div>
                <div class="citizen-side-row">
                    <span>{{ __('Status') }}</span>
                    <x-status-pill :status="$serviceRequest->payment_status === 'paid' ? 'paid' : 'unpaid'" />
                </div>
                @if($serviceRequest->transaction_id)
                    <div class="citizen-side-row">
                        <span>{{ __('Method') }}</span>
                        <strong>{{ ucfirst($serviceRequest->payment_method ?? '-') }}</strong>
                    </div>
                @endif
                @if($serviceRequest->payment_status !== 'paid')
                    @if($citizenActionLocked)
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" disabled>
                            <i class="bi bi-lock me-1"></i> {{ __('Profile Verification Required') }}
                        </button>
                    @else
                        <a href="{{ route('citizen.payment', $serviceRequest) }}" class="btn btn-primary w-100 mt-2">
                            <i class="bi bi-credit-card me-1"></i> {{ __('Pay Now') }}
                        </a>
                    @endif
                @else
                    <div class="citizen-paid-ok"><i class="bi bi-check-circle me-1"></i>{{ __('Payment complete') }}</div>
                    <a href="{{ route('citizen.requests.receipt', $serviceRequest) }}" class="btn btn-outline-success w-100">
                        <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Download Receipt') }}
                    </a>
                @endif
                @if($serviceRequest->canDownloadApprovalLetter())
                    <a href="{{ route('citizen.requests.pdf', [$serviceRequest, 'approval']) }}" class="btn btn-outline-primary w-100 mt-2">
                        <i class="bi bi-patch-check me-1"></i> {{ __('Download Approval Letter') }}
                    </a>
                @endif
                @if($serviceRequest->canDownloadCertificate())
                    <a href="{{ route('citizen.requests.pdf', [$serviceRequest, 'certificate']) }}" class="btn btn-outline-primary w-100 mt-2">
                        <i class="bi bi-award me-1"></i> {{ __('Download Certificate') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="card citizen-reveal" data-citizen-reveal>
            <div class="card-header">
                <span class="card-title">{{ __('Appointment') }}</span>
            </div>
            <div class="card-body">
                @if($serviceRequest->appointment)
                    <div class="citizen-side-row">
                        <span>{{ __('Date') }}</span>
                        <strong>{{ \Carbon\Carbon::parse($serviceRequest->appointment->appointment_date)->format('M d, Y') }}</strong>
                    </div>
                    <div class="citizen-side-row">
                        <span>{{ __('Time') }}</span>
                        <strong>{{ \Carbon\Carbon::parse($serviceRequest->appointment->appointment_time)->format('g:i A') }}</strong>
                    </div>
                    <x-status-pill :status="$serviceRequest->appointment->status" />
                @else
                    <p class="citizen-muted-note">{{ __('No appointment scheduled yet.') }}</p>
                    <button class="btn btn-outline-primary w-100" @disabled($citizenActionLocked) data-bs-toggle="modal" data-bs-target="#aptModal">
                        <i class="bi {{ $citizenActionLocked ? 'bi-lock' : 'bi-calendar-plus' }} me-1"></i>
                        {{ $citizenActionLocked ? __('Profile Verification Required') : __('Book Appointment') }}
                    </button>
                @endif
            </div>
        </div>

        @if($serviceRequest->status === 'completed')
            <div class="card citizen-reveal" data-citizen-reveal>
                <div class="card-header">
                    <span class="card-title"><i class="bi bi-star me-2 text-primary"></i>{{ __('Feedback') }}</span>
                </div>
                <div class="card-body">
                    @if($serviceRequest->feedback)
                        <div class="citizen-feedback-summary">
                            <div class="citizen-feedback-summary-head">
                                <span>{{ __('Your Review') }}</span>
                                <div class="citizen-feedback-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $serviceRequest->feedback->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            @if($serviceRequest->feedback->comment)
                                <p>{{ $serviceRequest->feedback->comment }}</p>
                            @else
                                <p class="mb-0">{{ __('You rated this office without leaving a written comment.') }}</p>
                            @endif

                            @if($serviceRequest->feedback->office_reply)
                                <div class="citizen-feedback-reply">
                                    <div class="citizen-feedback-reply-title">
                                        {{ __('Office Reply') }}
                                        <span>{{ $serviceRequest->feedback->reply_is_public ? __('Public') : __('Private to you') }}</span>
                                    </div>
                                    <p>{{ $serviceRequest->feedback->office_reply }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                    @if($feedbackFormHasErrors)
                        <div class="alert alert-danger" style="font-size:.8rem">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('citizen.feedback.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="office_id" value="{{ $serviceRequest->office_id }}">
                        <input type="hidden" name="service_request_id" value="{{ $serviceRequest->id }}">

                        <div class="mb-3">
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
                            <textarea name="comment" class="form-control" rows="3" placeholder="{{ __('Write your feedback...') }}">{{ old('comment') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-send me-1"></i> {{ __('Submit Feedback') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="aptModal" tabindex="-1" aria-labelledby="aptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content citizen-apt-modal">
            <div class="modal-header border-0 pb-1">
                <h6 class="modal-title fw-bold" id="aptModalLabel">{{ __('Book Appointment') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('citizen.appointments.book') }}" method="POST"
                  data-slots-form data-slots-url="{{ route('citizen.offices.slots', $serviceRequest->office_id) }}">
                @csrf
                <input type="hidden" name="office_id" value="{{ $serviceRequest->office_id }}">
                <input type="hidden" name="service_request_id" value="{{ $serviceRequest->id }}">
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Preferred Date') }}</label>
                        <input type="date" name="appointment_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" required data-slots-date @disabled($citizenActionLocked)>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Available Time Slots') }}</label>
                        <select name="appointment_time" class="form-select" required data-slots-select disabled>
                            <option value="">{{ __('Pick a date first…') }}</option>
                        </select>
                        <div class="form-text" data-slots-status></div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Any specific notes...') }}" @disabled($citizenActionLocked)></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
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
   CITIZEN REQUEST DETAIL — PREMIUM STYLES
   ═══════════════════════════════════════════════════════ */

body.es-role-citizen .citizen-request-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    flex-wrap: wrap;
}

body.es-role-citizen .citizen-request-head-kicker {
    display: inline-flex;
    padding: .22rem .6rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    box-shadow: 0 2px 8px rgba(14,165,233,0.3);
}

body.es-role-citizen .citizen-request-head-title {
    margin: .6rem 0 .2rem;
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #0F172A 0%, #0EA5E9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

body.es-role-citizen .citizen-request-head-sub {
    color: #64748B;
    font-size: .79rem;
    margin-bottom: .3rem;
}

body.es-role-citizen .citizen-request-detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

body.es-role-citizen .citizen-request-left-col,
body.es-role-citizen .citizen-request-right-col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ── Timeline with gradient line ── */
body.es-role-citizen .citizen-timeline-item {
    display: flex;
    gap: .8rem;
    margin-bottom: .9rem;
}

body.es-role-citizen .citizen-timeline-item:last-child {
    margin-bottom: 0;
}

body.es-role-citizen .citizen-timeline-rail {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-timeline-dot {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E0F2FE, #EDE9FE);
    color: #0284C7;
    border: 1.5px solid rgba(14,165,233,0.2);
    font-size: .72rem;
    box-shadow: 0 3px 8px rgba(14,165,233,0.12);
}

body.es-role-citizen .citizen-timeline-line {
    width: 2px;
    flex: 1;
    background: linear-gradient(180deg, rgba(14,165,233,0.2) 0%, rgba(99,102,241,0.1) 100%);
    margin-top: .2rem;
    border-radius: 1px;
}

body.es-role-citizen .citizen-timeline-title {
    font-size: .84rem;
    font-weight: 700;
    color: #0F172A;
}

body.es-role-citizen .citizen-timeline-time {
    font-size: .72rem;
    color: #94A3B8;
    margin-top: .1rem;
}

body.es-role-citizen .citizen-timeline-note {
    margin-top: .25rem;
    font-size: .78rem;
    color: #475569;
    padding: .35rem .55rem;
    background: rgba(241,245,249,0.5);
    border-radius: .4rem;
    border-left: 2px solid rgba(14,165,233,0.3);
}

body.es-role-citizen .citizen-muted-note {
    color: #94A3B8;
    font-size: .79rem;
}

/* ── Document rows ── */
body.es-role-citizen .citizen-doc-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem 1rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    transition: all .2s ease;
}

body.es-role-citizen .citizen-doc-row:last-child {
    border-bottom: 0;
}

body.es-role-citizen .citizen-doc-row:hover {
    background: rgba(224,242,254,0.15);
}

body.es-role-citizen .citizen-doc-icon {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: .7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E0F2FE, #EDE9FE);
    color: #0284C7;
    border: 1px solid rgba(14,165,233,0.15);
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(14,165,233,0.08);
}

body.es-role-citizen .citizen-doc-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-doc-name {
    font-size: .82rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-doc-sub {
    font-size: .72rem;
    color: #94A3B8;
}

/* ── Chat: glass style ── */
body.es-role-citizen .chat-box {
    max-height: 22rem;
    overflow: auto;
    padding: 1rem;
    background: rgba(248,250,255,0.5);
}

body.es-role-citizen .citizen-chat-empty {
    text-align: center;
    color: #94A3B8;
    font-size: .8rem;
}

body.es-role-citizen .msg {
    display: flex;
    gap: .55rem;
    margin-bottom: .7rem;
}

body.es-role-citizen .msg:last-child {
    margin-bottom: 0;
}

body.es-role-citizen .msg.mine {
    justify-content: flex-end;
}

body.es-role-citizen .msg.theirs {
    justify-content: flex-start;
}

body.es-role-citizen .msg-av {
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .7rem;
    font-weight: 700;
    flex-shrink: 0;
}

body.es-role-citizen .msg-av.av-me {
    background: linear-gradient(135deg, #DBEAFE, #E0F2FE);
    color: #1D4ED8;
    border: 1px solid rgba(37,99,235,0.15);
    order: 2;
}

body.es-role-citizen .msg-av.av-other {
    background: linear-gradient(135deg, #ECFEFF, #D1FAE5);
    color: #0F766E;
    border: 1px solid rgba(15,118,110,0.15);
}

body.es-role-citizen .msg-bubble {
    max-width: min(80%, 27rem);
    border-radius: .9rem;
    padding: .6rem .72rem;
    border: 1px solid rgba(226,232,240,0.5);
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(4px);
}

body.es-role-citizen .msg.mine .msg-bubble {
    background: linear-gradient(135deg, rgba(224,242,254,0.7) 0%, rgba(219,234,254,0.7) 100%);
    border-color: rgba(14,165,233,0.15);
    order: 1;
}

body.es-role-citizen .msg-name {
    font-size: .67rem;
    font-weight: 700;
    color: #94A3B8;
    margin-bottom: .15rem;
}

body.es-role-citizen .msg-bubble p {
    margin: 0;
    font-size: .79rem;
    color: #0F172A;
    line-height: 1.5;
    white-space: pre-wrap;
}

body.es-role-citizen .msg-time {
    margin-top: .2rem;
    font-size: .65rem;
    color: #94A3B8;
    text-align: right;
}

body.es-role-citizen .citizen-chat-input-wrap {
    border-top: 1px solid rgba(226,232,240,0.5);
    padding: .8rem 1rem;
    background: rgba(255,255,255,0.3);
}

/* ── Sidebar cards ── */
body.es-role-citizen .citizen-side-card-title {
    font-size: .84rem;
    font-weight: 700;
    margin-bottom: .7rem;
    color: #0F172A;
}

body.es-role-citizen .citizen-qr-image {
    max-width: 10rem;
    border-radius: .7rem;
    border: 1px solid rgba(203,213,225,0.5);
    box-shadow: 0 4px 12px rgba(15,23,42,0.06);
}

body.es-role-citizen .citizen-side-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .8rem;
    color: #64748B;
    padding: .35rem 0;
    border-bottom: 1px solid rgba(226,232,240,0.3);
}

body.es-role-citizen .citizen-side-row:last-of-type {
    border-bottom: 0;
}

body.es-role-citizen .citizen-side-row strong {
    color: #0F172A;
    font-weight: 700;
}

body.es-role-citizen .citizen-paid-ok {
    text-align: center;
    margin: .7rem 0;
    color: #059669;
    font-size: .8rem;
    font-weight: 700;
    padding: .45rem;
    background: rgba(236,253,245,0.5);
    border-radius: .5rem;
    border: 1px solid rgba(16,185,129,0.15);
}

body.es-role-citizen .citizen-feedback-summary {
    padding: .9rem 1rem;
    border-radius: .85rem;
    border: 1px solid rgba(191,219,254,0.8);
    background: rgba(248,250,252,0.8);
}

body.es-role-citizen .citizen-feedback-summary-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    margin-bottom: .55rem;
    font-size: .84rem;
    font-weight: 700;
    color: #0F172A;
}

body.es-role-citizen .citizen-feedback-stars {
    display: inline-flex;
    gap: 2px;
    color: #F59E0B;
    font-size: .75rem;
}

body.es-role-citizen .citizen-feedback-summary p {
    margin: 0;
    font-size: .8rem;
    color: #475569;
    line-height: 1.55;
}

body.es-role-citizen .citizen-feedback-reply {
    margin-top: .8rem;
    padding: .72rem .85rem;
    border-radius: .72rem;
    border-left: 3px solid #10B981;
    background: rgba(236,253,245,0.7);
}

body.es-role-citizen .citizen-feedback-reply-title {
    display: flex;
    justify-content: space-between;
    gap: .5rem;
    font-size: .72rem;
    font-weight: 700;
    color: #047857;
    margin-bottom: .25rem;
}

body.es-role-citizen .citizen-feedback-reply-title span {
    color: #94A3B8;
    font-weight: 600;
}

body.es-role-citizen .citizen-panel-empty {
    padding: 1.6rem 1rem;
    text-align: center;
    color: #94A3B8;
}

body.es-role-citizen .citizen-panel-empty i {
    font-size: 1.6rem;
    color: #CBD5E1;
    display: block;
    margin-bottom: .4rem;
}

body.es-role-citizen .citizen-panel-empty p {
    margin: 0;
    font-size: .8rem;
}

body.es-role-citizen .citizen-resubmit-card {
    border: 1px solid rgba(245, 158, 11, 0.35);
    background: linear-gradient(135deg, rgba(255, 251, 235, 0.7) 0%, rgba(255, 255, 255, 0.95) 60%);
}

body.es-role-citizen .citizen-resubmit-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(245, 158, 11, 0.2);
}

body.es-role-citizen .citizen-resubmit-note {
    padding: .6rem .8rem;
    background: rgba(254, 243, 199, 0.55);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: .55rem;
    font-size: .82rem;
    color: #92400E;
    margin-bottom: .85rem;
}

body.es-role-citizen .citizen-resubmit-note p {
    color: #78350F;
    font-size: .78rem;
}

body.es-role-citizen .citizen-apt-modal {
    border: 1px solid rgba(219,234,254,0.5);
    border-radius: 1.1rem;
    box-shadow: 0 24px 56px rgba(15,23,42,0.2);
    backdrop-filter: blur(8px);
}

@media (min-width: 768px) {
    body.es-role-citizen .citizen-request-detail-grid {
        grid-template-columns: 1fr 320px;
    }
}
</style>
@endpush

@push('scripts')
<script>
    const chatBox = document.getElementById('chatBox');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');

    async function sendMsg() {
        const body = chatInput.value.trim();
        if (!body) return;

        chatInput.disabled = true;
        sendBtn.disabled = true;

        try {
            const response = await fetch('{{ route('citizen.messages.send', $serviceRequest) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ body }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert(data.message || 'Failed to send message');
                return;
            }

            chatInput.value = '';
            await loadMessages();
        } catch (error) {
            alert('Something went wrong while sending the message.');
        } finally {
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();
        }
    }

    async function loadMessages() {
        try {
            const res = await fetch('{{ route('citizen.messages.get', $serviceRequest) }}', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            chatBox.innerHTML = '';

            if (!data.messages.length) {
                chatBox.innerHTML = `
                    <div style="padding:1rem;text-align:center;color:#9ca3af;font-size:.82rem">
                        No messages yet. Start the conversation.
                    </div>
                `;
                return;
            }

            data.messages.forEach(msg => {
                const mine = msg.sender_id === {{ auth()->id() }};

                const div = document.createElement('div');
                div.className = 'msg ' + (mine ? 'mine' : 'theirs');

                div.innerHTML = `
                    <div class="msg-av ${mine ? 'av-me' : 'av-other'}">
                        ${msg.sender.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="msg-bubble">
                        <div style="font-size:.7rem;font-weight:700;margin-bottom:.2rem;color:#6b7280">
                            ${mine ? 'You' : msg.sender.name}
                        </div>
                        <p></p>
                        <div class="msg-time">
                            ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                `;

                div.querySelector('p').textContent = msg.body;
                chatBox.appendChild(div);
            });

            chatBox.scrollTop = chatBox.scrollHeight;

        } catch (e) {
            console.error('Error loading messages', e);
        }
    }

    sendBtn.addEventListener('click', sendMsg);

    chatInput.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMsg();
        }
    });

    loadMessages();
    setInterval(loadMessages, 2000);
    chatBox.scrollTop = chatBox.scrollHeight;

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
