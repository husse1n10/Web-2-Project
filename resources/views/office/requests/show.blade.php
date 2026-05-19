@extends('layouts.app')
@section('title', 'Request ' . $serviceRequest->reference_number)
@section('page-title', 'Request Details')

@section('content')
<div class="card office-request-head-card office-reveal" data-office-reveal>
    <div class="card-body">
        <div class="office-request-head-wrap">
            <div class="office-request-head-main">
                <span class="office-request-kicker">Service Request</span>
                <h5 class="office-request-title">{{ $serviceRequest->service->name }}</h5>
                <div class="office-request-meta">
                    <span><i class="bi bi-person me-1"></i>{{ $serviceRequest->citizen->name }}</span>
                    <span class="office-request-dot"></span>
                    <code>{{ $serviceRequest->reference_number }}</code>
                </div>
            </div>
            <div class="office-request-head-pills">
                <x-status-pill :status="$serviceRequest->status" />
                <x-status-pill :status="$serviceRequest->payment_status === 'paid' ? 'paid' : 'unpaid'" />
            </div>
        </div>

        @if($serviceRequest->notes)
            <div class="office-request-citizen-note">
                <strong>Citizen Note:</strong> {{ $serviceRequest->notes }}
            </div>
        @endif
    </div>
</div>

<div class="office-request-detail-grid">
    <div class="office-request-left-col">
        <div class="card office-reveal" data-office-reveal>
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <span class="card-title"><i class="bi bi-person-check me-2 text-primary"></i>Assignment & SLA</span>
                @if($serviceRequest->isOverdue())
                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Overdue</span>
                @endif
            </div>
            <div class="card-body">
                @error('assigned_to')<div class="alert alert-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                <form action="{{ route('office.requests.assign', $serviceRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="office-status-grid">
                        <div>
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">— Unassigned —</option>
                                @foreach($officeStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ (int) $serviceRequest->assigned_to === (int) $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_at" class="form-control"
                                   value="{{ optional($serviceRequest->due_at)->format('Y-m-d') }}"
                                   min="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="office-request-actions">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-check2 me-1"></i> Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Update Status</span>
            </div>
            <div class="card-body">
                <form action="{{ route('office.requests.status', $serviceRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="office-status-grid">
                        <div>
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select" required>
                                @foreach(['in_review', 'missing_documents', 'approved', 'rejected', 'completed'] as $status)
                                    <option value="{{ $status }}" {{ $serviceRequest->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Comment (visible to citizen)</label>
                            <input type="text" name="comment" class="form-control" placeholder="Optional update for the citizen">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Internal Office Note (private)</label>
                        <textarea name="office_notes" class="form-control" rows="3" placeholder="Private internal notes...">{{ $serviceRequest->office_notes }}</textarea>
                    </div>
                    <div class="office-request-actions">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-circle me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-paperclip me-2 text-primary"></i>Submitted Documents</span>
            </div>
            <div class="card-body p-0">
                @forelse($serviceRequest->documents as $doc)
                    <div class="office-doc-row">
                        <div class="office-doc-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="office-doc-main">
                            <div class="office-doc-name">{{ $doc->original_name }}</div>
                            <div class="office-doc-sub">Uploaded by {{ ucfirst($doc->uploaded_by) }}</div>
                        </div>
                        <a
                            href="{{ Storage::url($doc->file_path) }}"
                            target="_blank"
                            class="btn btn-sm btn-outline-secondary"
                            aria-label="View document {{ $doc->original_name }}"
                        >
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                @empty
                    <x-empty-state
                        icon="bi-folder2-open"
                        title="No documents uploaded"
                        message="Citizen attachments will appear here once submitted."
                        class="py-4"
                    />
                @endforelse
            </div>
        </div>

        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-chat-dots me-2 text-primary"></i>Chat with Citizen</span>
            </div>
            <div class="chat-box office-chat-box" id="chatBox">
                @if($serviceRequest->messages->isEmpty())
                    <div class="office-chat-empty">No messages yet. Start the conversation.</div>
                @else
                    @foreach($serviceRequest->messages as $msg)
                        @php
                            $mine = $msg->sender_id === auth()->id();
                            $senderName = $mine ? 'You' : ($msg->sender?->name ?? 'Citizen');
                        @endphp
                        <div class="msg {{ $mine ? 'mine' : 'theirs' }}">
                            <div class="msg-av {{ $mine ? 'av-me' : 'av-other' }}">
                                {{ strtoupper(substr($msg->sender?->name ?? 'C', 0, 1)) }}
                            </div>
                            <div class="msg-bubble">
                                <div class="msg-name">{{ $senderName }}</div>
                                <p>{{ $msg->body }}</p>
                                <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="office-chat-input-wrap">
                <div class="d-flex gap-2">
                    <input type="text" id="chatInput" class="form-control form-control-sm" placeholder="Message citizen..." style="flex:1">
                    <button id="sendBtn" class="btn btn-primary btn-sm" style="flex-shrink:0">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="office-request-right-col">
        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Timeline</span>
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
                    <div class="office-timeline-item">
                        <div class="office-timeline-rail">
                            <div class="office-timeline-dot">
                                <i class="bi {{ $statusIcons[$log->to_status] ?? 'bi-arrow-right' }}"></i>
                            </div>
                            @if(!$loop->last)
                                <div class="office-timeline-line"></div>
                            @endif
                        </div>
                        <div class="office-timeline-content">
                            <div class="office-timeline-title">{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</div>
                            <div class="office-timeline-time">
                                {{ $log->changedBy?->name ?? 'System' }} - {{ $log->created_at->diffForHumans() }}
                            </div>
                            @if($log->comment)
                                <div class="office-timeline-note">{{ $log->comment }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        icon="bi-clock-history"
                        title="No timeline updates"
                        message="Status changes will appear here."
                        class="py-3"
                    />
                @endforelse
            </div>
        </div>

        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>Payment</span>
            </div>
            <div class="card-body">
                <div class="office-side-row">
                    <span>Amount</span>
                    <strong>${{ number_format($serviceRequest->service->price, 2) }}</strong>
                </div>
                <div class="office-side-row">
                    <span>Method</span>
                    <strong>{{ ucfirst($serviceRequest->payment_method ?? 'N/A') }}</strong>
                </div>
                <div class="office-side-row">
                    <span>Status</span>
                    <x-status-pill :status="$serviceRequest->payment_status === 'paid' ? 'paid' : 'unpaid'" />
                </div>
                @if($serviceRequest->transaction_id)
                    <div class="office-transaction-box">
                        <div class="office-transaction-label">Transaction ID</div>
                        <code>{{ $serviceRequest->transaction_id }}</code>
                    </div>
                @endif
            </div>
        </div>

        <div class="card office-reveal" data-office-reveal>
            <div class="card-header">
                <span class="card-title"><i class="bi bi-person-vcard me-2 text-primary"></i>Citizen</span>
            </div>
            <div class="card-body">
                <div class="office-citizen-row">
                    <div class="office-citizen-avatar">{{ strtoupper(substr($serviceRequest->citizen->name, 0, 1)) }}</div>
                    <div>
                        <div class="office-citizen-name">{{ $serviceRequest->citizen->name }}</div>
                        <div class="office-citizen-email">{{ $serviceRequest->citizen->email }}</div>
                    </div>
                </div>
                @if($serviceRequest->citizen->phone)
                    <div class="office-citizen-phone">
                        <i class="bi bi-telephone me-1"></i>{{ $serviceRequest->citizen->phone }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE REQUEST DETAIL — PREMIUM GLASSMORPHISM
   ═══════════════════════════════════════════════════════ */

/* Header card — glass with sweep */
body.es-role-office_user .office-request-head-card {
    margin-bottom: 1rem;
    border: 1px solid rgba(37,99,235,0.12) !important;
    background: rgba(255,255,255,0.55) !important;
    backdrop-filter: blur(16px) saturate(1.6);
    -webkit-backdrop-filter: blur(16px) saturate(1.6);
    position: relative;
    overflow: hidden;
}
body.es-role-office_user .office-request-head-wrap {
    display: flex; justify-content: space-between; align-items: flex-start; gap: .8rem; flex-wrap: wrap;
}
body.es-role-office_user .office-request-kicker {
    display: inline-flex; padding: .22rem .58rem; border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(37,99,235,0.22); border: none;
}
body.es-role-office_user .office-request-title {
    margin: .62rem 0 .2rem; font-size: 1.02rem; font-weight: 800;
    background: linear-gradient(135deg, #1E3A8A, #2563EB);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
body.es-role-office_user .office-request-meta {
    display: flex; align-items: center; gap: .48rem; flex-wrap: wrap; font-size: .78rem; color: #64748B;
}
body.es-role-office_user .office-request-dot { width: .24rem; height: .24rem; border-radius: 999px; background: #CBD5E1; }
body.es-role-office_user .office-request-head-pills { display: inline-flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
body.es-role-office_user .office-request-citizen-note {
    margin-top: .82rem; border-radius: .74rem;
    border: 1px solid rgba(37,99,235,0.1);
    background: rgba(239,246,255,0.5);
    backdrop-filter: blur(8px);
    color: #1E3A8A; font-size: .79rem; line-height: 1.45; padding: .7rem .82rem;
}

/* Detail grid */
body.es-role-office_user .office-request-detail-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
body.es-role-office_user .office-request-left-col,
body.es-role-office_user .office-request-right-col { display: flex; flex-direction: column; gap: 1rem; }
body.es-role-office_user .office-status-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .7rem; }
body.es-role-office_user .office-request-actions { margin-top: .85rem; }

/* Document rows — glass hover */
body.es-role-office_user .office-doc-row {
    display: flex; align-items: center; gap: .72rem; padding: .82rem 1rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    transition: background .22s ease, transform .22s ease;
}
body.es-role-office_user .office-doc-row:last-child { border-bottom: 0; }
body.es-role-office_user .office-doc-row:hover {
    background: rgba(224,242,254,0.15); transform: translateX(3px);
}
body.es-role-office_user .office-doc-icon {
    width: 2.1rem; height: 2.1rem; border-radius: .65rem;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; flex-shrink: 0; box-shadow: 0 3px 8px rgba(37,99,235,0.18);
}
body.es-role-office_user .office-doc-main { flex: 1; min-width: 0; }
body.es-role-office_user .office-doc-name { font-size: .82rem; font-weight: 700; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
body.es-role-office_user .office-doc-sub { font-size: .72rem; color: #64748B; }

/* Chat — glass */
body.es-role-office_user .office-chat-box {
    max-height: 20rem; overflow: auto; padding: 1rem;
    background: rgba(255,255,255,0.3);
    backdrop-filter: blur(8px);
}
body.es-role-office_user .office-chat-empty { text-align: center; color: #64748B; font-size: .8rem; }
body.es-role-office_user .msg { display: flex; gap: .55rem; margin-bottom: .7rem; }
body.es-role-office_user .msg:last-child { margin-bottom: 0; }
body.es-role-office_user .msg.mine { justify-content: flex-end; }
body.es-role-office_user .msg.theirs { justify-content: flex-start; }
body.es-role-office_user .msg-av {
    width: 1.85rem; height: 1.85rem; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 700; flex-shrink: 0;
}
body.es-role-office_user .msg-av.av-me {
    background: linear-gradient(135deg, #2563EB, #0EA5E9); color: #fff; border: none; order: 2;
    box-shadow: 0 2px 6px rgba(37,99,235,0.2);
}
body.es-role-office_user .msg-av.av-other {
    background: linear-gradient(135deg, #14B8A6, #10B981); color: #fff; border: none;
    box-shadow: 0 2px 6px rgba(20,184,166,0.2);
}
body.es-role-office_user .msg-bubble {
    max-width: min(80%, 27rem); border-radius: .85rem; padding: .55rem .66rem;
    border: 1px solid rgba(226,232,240,0.5);
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(6px);
}
body.es-role-office_user .msg.mine .msg-bubble {
    background: rgba(224,242,254,0.5); border-color: rgba(186,230,253,0.5); order: 1;
}
body.es-role-office_user .msg-name { font-size: .68rem; font-weight: 700; color: #475569; margin-bottom: .15rem; }
body.es-role-office_user .msg-bubble p { margin: 0; font-size: .79rem; color: #0F172A; line-height: 1.45; white-space: pre-wrap; }
body.es-role-office_user .msg-time { margin-top: .2rem; font-size: .66rem; color: #64748B; text-align: right; }
body.es-role-office_user .office-chat-input-wrap { border-top: 1px solid rgba(226,232,240,0.5); padding: .75rem 1rem; }

/* Timeline — gradient dots */
body.es-role-office_user .office-timeline-item { display: flex; gap: .74rem; margin-bottom: .85rem; }
body.es-role-office_user .office-timeline-item:last-child { margin-bottom: 0; }
body.es-role-office_user .office-timeline-rail { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
body.es-role-office_user .office-timeline-dot {
    width: 1.65rem; height: 1.65rem; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; border: none; font-size: .7rem;
    box-shadow: 0 3px 8px rgba(37,99,235,0.2);
}
body.es-role-office_user .office-timeline-line {
    width: 2px; flex: 1; margin-top: .2rem;
    background: linear-gradient(180deg, rgba(37,99,235,0.3), rgba(37,99,235,0.05));
}
body.es-role-office_user .office-timeline-title { font-size: .82rem; font-weight: 700; color: #0F172A; }
body.es-role-office_user .office-timeline-time { margin-top: .12rem; font-size: .72rem; color: #64748B; }
body.es-role-office_user .office-timeline-note {
    margin-top: .2rem; font-size: .76rem; color: #334155;
    border-left: 2px solid rgba(37,99,235,0.2); padding-left: .5rem;
}

/* Side rows */
body.es-role-office_user .office-side-row { display: flex; justify-content: space-between; align-items: center; padding: .28rem 0; font-size: .8rem; color: #475569; }
body.es-role-office_user .office-side-row strong { color: #0F172A; }
body.es-role-office_user .office-transaction-box {
    margin-top: .62rem; padding: .58rem .64rem; border-radius: .64rem;
    border: 1px solid rgba(37,99,235,0.1);
    background: rgba(248,250,255,0.5); backdrop-filter: blur(6px);
}
body.es-role-office_user .office-transaction-label { color: #64748B; font-size: .67rem; margin-bottom: .1rem; }

/* Citizen avatar — gradient */
body.es-role-office_user .office-citizen-row { display: flex; align-items: center; gap: .68rem; margin-bottom: .74rem; }
body.es-role-office_user .office-citizen-avatar {
    width: 2.3rem; height: 2.3rem; border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: .82rem; font-weight: 700; flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(37,99,235,0.2); border: none;
}
body.es-role-office_user .office-citizen-name { font-size: .84rem; font-weight: 700; color: #0F172A; }
body.es-role-office_user .office-citizen-email { font-size: .73rem; color: #64748B; }
body.es-role-office_user .office-citizen-phone { font-size: .78rem; color: #475569; }

@media (min-width: 992px) {
    body.es-role-office_user .office-request-detail-grid { grid-template-columns: minmax(0, 1fr) 320px; }
}
@media (max-width: 767.98px) {
    body.es-role-office_user .office-status-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
    const chatBox = document.getElementById('chatBox');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const myInitial = @json(strtoupper(substr(auth()->user()->name, 0, 1)));

    async function sendMsg() {
        const body = chatInput?.value?.trim();
        if (!body) return;

        chatInput.disabled = true;
        sendBtn.disabled = true;

        try {
            const response = await fetch('{{ route('office.messages.send', $serviceRequest) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    Accept: 'application/json',
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
            const res = await fetch('{{ route('office.messages.get', $serviceRequest) }}', {
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
</script>
@endpush
