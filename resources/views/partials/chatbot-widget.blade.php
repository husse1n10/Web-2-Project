{{-- Arzi — CedarGov AI assistant (citizens only) --}}
<div id="cgChatbot" class="cg-chatbot" data-endpoint="{{ route('citizen.chatbot.ask') }}">
    <button type="button" id="cgChatbotToggle" class="cg-chatbot-fab" aria-label="Open Arzi, the CedarGov assistant" title="Ask Arzi">
        <img src="{{ asset('assets/img/ai_bot/Arzi.png') }}" alt="Arzi" class="cg-chatbot-fab-img">
    </button>

    <div id="cgChatbotPanel" class="cg-chatbot-panel" role="dialog" aria-label="Arzi assistant" hidden>
        <div class="cg-chatbot-header">
            <div class="cg-chatbot-header-main">
                <span class="cg-chatbot-avatar">
                    <img src="{{ asset('assets/img/ai_bot/Arzi.png') }}" alt="Arzi">
                </span>
                <div>
                    <div class="cg-chatbot-title">Arzi</div>
                    <div class="cg-chatbot-sub">Your CedarGov assistant</div>
                </div>
            </div>
            <button type="button" id="cgChatbotClose" class="cg-chatbot-close" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="cgChatbotMessages" class="cg-chatbot-messages">
            <div class="cg-chatbot-msg cg-chatbot-msg-bot">Hi! I'm Arzi 🌲 — pick a question below or ask me anything about CedarGov.</div>
            <div class="cg-chatbot-suggestions" id="cgChatbotSuggestions">
                <button type="button" class="cg-chatbot-chip">How do I submit a request?</button>
                <button type="button" class="cg-chatbot-chip">How can I pay for a service?</button>
                <button type="button" class="cg-chatbot-chip">How do I track my request?</button>
                <button type="button" class="cg-chatbot-chip">How do I verify my phone?</button>
                <button type="button" class="cg-chatbot-chip">How do I book an appointment?</button>
                <button type="button" class="cg-chatbot-chip">Where do I upload my ID?</button>
            </div>
        </div>

        <form id="cgChatbotForm" class="cg-chatbot-form" autocomplete="off">
            <input type="text" id="cgChatbotInput" name="message" maxlength="2000"
                   placeholder="Type your question..." class="cg-chatbot-input" required>
            <button type="submit" class="cg-chatbot-send" aria-label="Send">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
</div>

<style>
.cg-chatbot { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 1080; font-family: inherit; }

.cg-chatbot-fab {
    width: 64px; height: 64px; padding: 0;
    border: 2px solid #BFE9FF;
    border-radius: 999px;
    background: linear-gradient(145deg, #FFFFFF, #EAF8FF);
    box-shadow: 0 12px 30px rgba(0, 120, 180, 0.18);
    cursor: pointer; overflow: hidden;
    display: inline-flex; align-items: center; justify-content: center;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.cg-chatbot-fab:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 38px rgba(0, 120, 180, 0.26);
    border-color: #009FE3;
}
.cg-chatbot-fab-img {
    width: 88%; height: 88%;
    object-fit: contain; object-position: center;
    pointer-events: none;
}
.cg-chatbot.is-open .cg-chatbot-fab { display: none; }

.cg-chatbot-panel {
    width: 360px; max-width: calc(100vw - 2rem);
    height: 520px; max-height: calc(100vh - 4rem);
    background: #fff; border: 1px solid #E2E8F0; border-radius: 1rem;
    box-shadow: 0 24px 60px rgba(15,23,42,0.22);
    display: flex; flex-direction: column; overflow: hidden;
    animation: cg-chatbot-pop .22s ease-out;
}
@keyframes cg-chatbot-pop {
    0% { opacity: 0; transform: translateY(12px) scale(.97); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

.cg-chatbot-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; padding: .85rem 1rem;
    background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 100%);
    color: #fff;
}
.cg-chatbot-header-main { display: flex; align-items: center; gap: .65rem; min-width: 0; }
.cg-chatbot-avatar {
    width: 38px; height: 38px; border-radius: 999px;
    background: linear-gradient(145deg, #FFFFFF, #EAF8FF);
    border: 2px solid rgba(255,255,255,0.85);
    overflow: hidden; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
}
.cg-chatbot-avatar img {
    width: 88%; height: 88%; object-fit: contain; object-position: center;
}
.cg-chatbot-title { font-weight: 700; font-size: .92rem; line-height: 1.1; }
.cg-chatbot-sub { font-size: .7rem; opacity: .85; margin-top: .15rem; }
.cg-chatbot-close {
    background: rgba(255,255,255,0.15); border: none; color: #fff;
    width: 30px; height: 30px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0; transition: background .2s;
}
.cg-chatbot-close:hover { background: rgba(255,255,255,0.3); }

.cg-chatbot-messages {
    flex: 1; padding: 1rem; overflow-y: auto;
    display: flex; flex-direction: column; gap: .6rem;
    background: #F8FAFC;
}
.cg-chatbot-msg {
    padding: .55rem .8rem; border-radius: .85rem;
    font-size: .85rem; line-height: 1.45;
    max-width: 85%; word-wrap: break-word; white-space: pre-wrap;
}
.cg-chatbot-msg-bot { background: #fff; border: 1px solid #E2E8F0; align-self: flex-start; }
.cg-chatbot-msg-user {
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff; align-self: flex-end; border: none;
}
.cg-chatbot-msg-error { background: #FEE2E2; border-color: #FCA5A5; color: #991B1B; align-self: flex-start; }
.cg-chatbot-suggestions {
    display: flex; flex-wrap: wrap; gap: .35rem;
    margin-top: .25rem;
}
.cg-chatbot-suggestions.is-hidden { display: none; }
.cg-chatbot-chip {
    padding: .35rem .7rem; font-size: .76rem; line-height: 1.2;
    background: #fff; border: 1px solid #CBD5E1; border-radius: 999px;
    color: #0369A1; cursor: pointer; text-align: left;
    transition: all .18s ease;
}
.cg-chatbot-chip:hover {
    background: #EFF6FF; border-color: #0EA5E9;
    color: #075985; transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(14,165,233,0.15);
}
.cg-chatbot-chip:active { transform: translateY(0); }

.cg-chatbot-typing {
    background: #fff; border: 1px solid #E2E8F0; align-self: flex-start;
    padding: .65rem .9rem; border-radius: .85rem;
    display: inline-flex; align-items: center; gap: .25rem;
}
.cg-chatbot-typing span {
    width: 6px; height: 6px; border-radius: 50%; background: #94A3B8;
    animation: cg-chatbot-bounce 1s infinite ease-in-out;
}
.cg-chatbot-typing span:nth-child(2) { animation-delay: .15s; }
.cg-chatbot-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes cg-chatbot-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: .5; }
    30% { transform: translateY(-4px); opacity: 1; }
}

.cg-chatbot-form {
    display: flex; gap: .4rem; padding: .65rem;
    border-top: 1px solid #E2E8F0; background: #fff;
}
.cg-chatbot-input {
    flex: 1; padding: .55rem .8rem; font-size: .85rem;
    border: 1px solid #CBD5E1; border-radius: .55rem; outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.cg-chatbot-input:focus { border-color: #0EA5E9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }
.cg-chatbot-send {
    width: 38px; border: none; border-radius: .55rem;
    background: linear-gradient(135deg, #0EA5E9, #6366F1);
    color: #fff; cursor: pointer; transition: opacity .2s;
}
.cg-chatbot-send:hover { opacity: .9; }
.cg-chatbot-send:disabled { opacity: .5; cursor: not-allowed; }

@media (max-width: 575.98px) {
    .cg-chatbot { right: .8rem; bottom: .8rem; }
    .cg-chatbot-fab-label { display: none; }
    .cg-chatbot-panel { width: calc(100vw - 1.6rem); height: calc(100vh - 5rem); }
}
</style>

@push('scripts')
<script>
(function(){
    const root = document.getElementById('cgChatbot');
    if (!root) return;

    const toggleBtn = document.getElementById('cgChatbotToggle');
    const closeBtn = document.getElementById('cgChatbotClose');
    const panel = document.getElementById('cgChatbotPanel');
    const messagesEl = document.getElementById('cgChatbotMessages');
    const form = document.getElementById('cgChatbotForm');
    const input = document.getElementById('cgChatbotInput');
    const sendBtn = form.querySelector('.cg-chatbot-send');
    const suggestions = document.getElementById('cgChatbotSuggestions');
    const endpoint = root.dataset.endpoint;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const history = []; // {role: 'user'|'assistant', content: string}

    function open() { panel.hidden = false; root.classList.add('is-open'); setTimeout(() => input.focus(), 50); }
    function close() { panel.hidden = true; root.classList.remove('is-open'); }
    toggleBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) close(); });

    function appendMsg(role, content) {
        const div = document.createElement('div');
        let cls = 'cg-chatbot-msg ';
        if (role === 'user') cls += 'cg-chatbot-msg-user';
        else if (role === 'error') cls += 'cg-chatbot-msg-error';
        else cls += 'cg-chatbot-msg-bot';
        div.className = cls;
        div.textContent = content;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return div;
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'cg-chatbot-typing';
        div.id = 'cgChatbotTyping';
        div.innerHTML = '<span></span><span></span><span></span>';
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    function hideTyping() {
        document.getElementById('cgChatbotTyping')?.remove();
    }

    suggestions?.addEventListener('click', (e) => {
        const chip = e.target.closest('.cg-chatbot-chip');
        if (!chip) return;
        input.value = chip.textContent.trim();
        form.requestSubmit();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        // Hide suggestions after first send
        suggestions?.classList.add('is-hidden');

        appendMsg('user', text);
        history.push({ role: 'user', content: text });
        if (history.length > 20) history.splice(0, history.length - 20);

        input.value = '';
        input.disabled = true; sendBtn.disabled = true;
        showTyping();

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text, history: history.slice(0, -1) }),
            });
            const data = await res.json().catch(() => ({}));
            hideTyping();

            if (res.ok && data.ok && data.reply) {
                appendMsg('assistant', data.reply);
                history.push({ role: 'assistant', content: data.reply });
            } else if (res.status === 429) {
                appendMsg('error', 'You are sending messages too fast — try again in a minute.');
            } else {
                appendMsg('error', data.error || 'The assistant is unavailable right now. Please try again or open a support ticket.');
            }
        } catch (err) {
            hideTyping();
            appendMsg('error', 'Network error — please check your connection and try again.');
        } finally {
            input.disabled = false; sendBtn.disabled = false;
            input.focus();
        }
    });
})();
</script>
@endpush
