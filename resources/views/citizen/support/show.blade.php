@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id)
@section('page-title', 'Support Ticket')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">

        <div class="card citizen-reveal mb-3" data-citizen-reveal>
            <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div style="min-width:0">
                    <h5 class="mb-1 text-truncate">{{ $ticket->subject }}</h5>
                    <div class="text-muted" style="font-size:.8rem">
                        Ticket #{{ $ticket->id }} · opened {{ $ticket->created_at->diffForHumans() }}
                    </div>
                </div>
                @php
                    $cls = match($ticket->status) {
                        'open'     => 'bg-warning-subtle text-warning-emphasis',
                        'answered' => 'bg-success-subtle text-success-emphasis',
                        'closed'   => 'bg-secondary-subtle text-secondary-emphasis',
                        default    => 'bg-light text-muted',
                    };
                @endphp
                <span class="badge {{ $cls }}" id="ticketStatusBadge" style="font-size:.8rem">{{ ucfirst($ticket->status) }}</span>
            </div>
        </div>

        <div class="card citizen-reveal mb-3" data-citizen-reveal>
            <div class="card-body p-0">
                <div class="support-thread" id="supportThread"
                     data-ticket-id="{{ $ticket->id }}"
                     data-current-user-id="{{ auth()->id() }}">
                    @foreach($ticket->messages as $msg)
                        @php $isMine = $msg->sender_id === auth()->id(); @endphp
                        <div class="support-msg {{ $isMine ? 'support-msg-mine' : 'support-msg-other' }}">
                            <div class="support-msg-meta">
                                <strong>{{ $isMine ? 'You' : ($msg->sender->name . ($msg->sender->isAdmin() ? ' (Admin)' : '')) }}</strong>
                                <span class="text-muted">· {{ $msg->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="support-msg-body">{{ $msg->body }}</div>
                            @if($msg->attachment)
                                <a class="support-msg-attachment" href="{{ $msg->attachment_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-paperclip"></i>
                                    <span>{{ $msg->attachment_name ?: 'Attachment' }}</span>
                                    @if($msg->attachment_size)
                                        <span class="text-muted">({{ number_format($msg->attachment_size / 1024, 0) }} KB)</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($ticket->status === 'closed')
            <div class="alert alert-secondary" id="ticketClosedAlert">
                This ticket is closed. Open a new one if you need further help.
                <a href="{{ route('citizen.support.create') }}" class="alert-link">New ticket</a>
            </div>
        @else
            <div class="card citizen-reveal" id="replyCard" data-citizen-reveal>
                <div class="card-header"><span class="card-title">Reply</span></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('citizen.support.reply', $ticket) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                                      maxlength="5000" required placeholder="Type your reply...">{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <input type="file" name="attachment"
                                   class="form-control form-control-sm @error('attachment') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                            <div class="form-text">Optional · max 5 MB</div>
                            @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('citizen.support') }}" class="btn btn-link p-0">
                <i class="bi bi-arrow-left me-1"></i> Back to all tickets
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
.support-thread { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: .85rem; }
.support-msg { padding: .75rem 1rem; border-radius: .65rem; max-width: 85%; }
.support-msg-meta { font-size: .75rem; margin-bottom: .35rem; }
.support-msg-body { white-space: pre-wrap; word-wrap: break-word; font-size: .9rem; line-height: 1.45; }
.support-msg-mine { background: #E0F2FE; align-self: flex-end; border: 1px solid #BAE6FD; }
.support-msg-other { background: #F8FAFC; align-self: flex-start; border: 1px solid #E2E8F0; }
.support-msg-attachment {
    display: inline-flex; align-items: center; gap: .35rem;
    margin-top: .55rem; padding: .35rem .65rem;
    background: rgba(255,255,255,0.7); border: 1px solid rgba(203,213,225,0.6);
    border-radius: .4rem; font-size: .8rem; color: #0369A1; text-decoration: none;
}
.support-msg-attachment:hover { background: #fff; color: #075985; }
.support-msg.is-new { animation: support-msg-pop .35s ease-out; }
@keyframes support-msg-pop {
    0% { opacity: 0; transform: translateY(8px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
(function(){
    const thread = document.getElementById('supportThread');
    if (!thread || typeof window.Echo === 'undefined') return;

    const ticketId = thread.dataset.ticketId;
    const meId = parseInt(thread.dataset.currentUserId, 10);

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function formatDate(iso) {
        try {
            const d = new Date(iso);
            return d.toLocaleString(undefined, { month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
        } catch(e) { return ''; }
    }

    function appendMessage(payload) {
        const isMine = payload.sender_id === meId;
        const wrap = document.createElement('div');
        wrap.className = 'support-msg is-new ' + (isMine ? 'support-msg-mine' : 'support-msg-other');

        const senderLabel = isMine
            ? 'You'
            : escapeHtml(payload.sender?.name || 'User') + (payload.sender?.is_admin ? ' (Admin)' : '');

        let attachmentHtml = '';
        if (payload.attachment_url) {
            attachmentHtml = `<a class="support-msg-attachment" href="${payload.attachment_url}" target="_blank" rel="noopener">
                <i class="bi bi-paperclip"></i><span>${escapeHtml(payload.attachment_name || 'Attachment')}</span>
            </a>`;
        }

        wrap.innerHTML = `
            <div class="support-msg-meta">
                <strong>${senderLabel}</strong>
                <span class="text-muted">· ${escapeHtml(formatDate(payload.created_at))}</span>
            </div>
            <div class="support-msg-body">${escapeHtml(payload.body)}</div>
            ${attachmentHtml}
        `;
        thread.appendChild(wrap);
        wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Flip status badge if admin replied
        if (!isMine && payload.sender?.is_admin) {
            const badge = document.getElementById('ticketStatusBadge');
            if (badge) {
                badge.textContent = 'Answered';
                badge.className = 'badge bg-success-subtle text-success-emphasis';
                badge.style.fontSize = '.8rem';
            }
        }
    }

    window.Echo.private('support-ticket.' + ticketId)
        .listen('.support-ticket.message.sent', appendMessage);
})();
</script>
@endpush
@endsection
