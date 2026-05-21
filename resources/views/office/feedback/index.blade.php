@extends('layouts.app')
@section('title', 'Feedback')
@section('page-title', 'Citizen Feedback')

@section('content')
<div class="card office-reveal" data-office-reveal>
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <span class="card-title">Reviews & Ratings</span>
        <div class="office-feedback-head-score">
            <i class="bi bi-star-fill"></i>
            <span class="office-feedback-score">{{ number_format($averageRating, 1) }}</span>
            <span class="office-feedback-count">({{ $feedback->total() }} reviews)</span>
        </div>
    </div>

    <div class="card-body p-0">
        @forelse($feedback as $fb)
            <div class="office-feedback-item">
                <div class="office-feedback-top">
                    <div class="office-feedback-user">
                        <div class="office-feedback-avatar">{{ strtoupper(substr($fb->citizen->name, 0, 1)) }}</div>
                        <div>
                            <div class="office-feedback-name">{{ $fb->citizen->name }}</div>
                            <div class="office-feedback-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $fb->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <span class="office-feedback-time">{{ $fb->created_at->diffForHumans() }}</span>
                </div>

                @if($fb->comment)
                    <p class="office-feedback-comment">{{ $fb->comment }}</p>
                @endif

                @if($fb->office_reply)
                    <div class="office-feedback-reply">
                        <div class="office-feedback-reply-title">
                            Office Reply
                            <span>({{ $fb->reply_is_public ? 'Public' : 'Private' }})</span>
                        </div>
                        <p>{{ $fb->office_reply }}</p>
                    </div>
                @else
                    <form action="{{ route('office.feedback.reply', $fb) }}" method="POST" class="office-feedback-form">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="office_reply" class="form-control form-control-sm" placeholder="Write a reply..." required>
                        <select name="reply_is_public" class="form-select form-select-sm office-feedback-visibility">
                            <option value="1">Public</option>
                            <option value="0">Private</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary office-feedback-reply-btn">
                            <i class="bi bi-reply me-1"></i> Reply
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <x-empty-state
                icon="bi-star"
                title="No feedback yet"
                message="Citizen ratings will appear here."
                class="py-5"
            />
        @endforelse
    </div>

    @if($feedback->hasPages())
        <div class="office-feedback-pagination">{{ $feedback->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE FEEDBACK — PREMIUM GLASSMORPHISM
   ═══════════════════════════════════════════════════════ */

body.es-role-office_user .office-feedback-head-score { display: inline-flex; align-items: center; gap: .38rem; }
body.es-role-office_user .office-feedback-head-score i { color: #F59E0B; font-size: .84rem; }
body.es-role-office_user .office-feedback-score { font-weight: 800; font-size: .9rem; color: #0F172A; }
body.es-role-office_user .office-feedback-count { font-size: .75rem; color: #94A3B8; }

/* Feedback items — glass hover */
body.es-role-office_user .office-feedback-item {
    padding: 1.05rem 1.2rem;
    border-bottom: 1px solid rgba(226,232,240,0.5);
    transition: background .22s ease;
}
body.es-role-office_user .office-feedback-item:last-child { border-bottom: 0; }
body.es-role-office_user .office-feedback-item:hover { background: rgba(224,242,254,0.1); }

body.es-role-office_user .office-feedback-top {
    display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem; flex-wrap: wrap;
}
body.es-role-office_user .office-feedback-user { display: flex; align-items: center; gap: .65rem; }

/* Avatar — gradient */
body.es-role-office_user .office-feedback-avatar {
    width: 2.25rem; height: 2.25rem; border-radius: 999px;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: .82rem; font-weight: 700; flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(37,99,235,0.18);
}
body.es-role-office_user .office-feedback-name { font-weight: 700; font-size: .85rem; }
body.es-role-office_user .office-feedback-stars { display: inline-flex; gap: 1px; color: #F59E0B; font-size: .75rem; }
body.es-role-office_user .office-feedback-time { font-size: .72rem; color: #94A3B8; }
body.es-role-office_user .office-feedback-comment { font-size: .82rem; color: #334155; margin: .72rem 0 .42rem; line-height: 1.5; }

/* Reply — glass with gradient left border */
body.es-role-office_user .office-feedback-reply {
    background: rgba(236,253,245,0.5); backdrop-filter: blur(6px);
    border-radius: .56rem; padding: .65rem .9rem; margin-top: .55rem;
    border-left: 3px solid #10B981;
}
body.es-role-office_user .office-feedback-reply-title { font-size: .7rem; font-weight: 700; color: #16A34A; margin-bottom: .2rem; }
body.es-role-office_user .office-feedback-reply-title span { color: #94A3B8; font-weight: 400; }
body.es-role-office_user .office-feedback-reply p { font-size: .8rem; color: #334155; margin: 0; }

/* Reply form — glass input */
body.es-role-office_user .office-feedback-form { margin-top: .65rem; display: flex; gap: .5rem; flex-wrap: wrap; }
body.es-role-office_user .office-feedback-form .form-control { flex: 1; min-width: 180px; }
body.es-role-office_user .office-feedback-visibility { max-width: 110px; }
body.es-role-office_user .office-feedback-reply-btn { flex-shrink: 0; }
body.es-role-office_user .office-feedback-pagination { padding: .75rem 1rem; border-top: 1px solid rgba(226,232,240,0.5); }

@media (max-width: 575.98px) {
    body.es-role-office_user .office-feedback-visibility,
    body.es-role-office_user .office-feedback-reply-btn { width: 100%; max-width: none; }
}
</style>
@endpush
