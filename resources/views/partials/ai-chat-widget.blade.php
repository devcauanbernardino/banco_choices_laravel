@php
    $bcChatMascoteKey = auth()->user()->mascote ?? null;
    $bcChatMascoteFiles = [
        'robo' => 'robo-choice.png',
        'fantasma' => 'fantasma-choice.png',
        'gato' => 'gato-choice.png',
    ];
    $bcChatMascoteFile = $bcChatMascoteFiles[$bcChatMascoteKey] ?? null;
    $bcChatTitle = $bcChatMascoteKey ? __('mascote.'.$bcChatMascoteKey.'.nome') : __('ia_chat.title');
    $bcChatWelcome = $bcChatMascoteKey ? __('ia_chat.welcome', ['nome' => $bcChatTitle]) : __('ia_chat.welcome_generic');
@endphp
<div id="bcAiChatWidget" style="position:fixed; right:20px; bottom:20px; z-index:1050; font-family:'Inter',system-ui,sans-serif;">
    <button type="button" id="bcAiChatToggle" aria-label="{{ __('ia_chat.open_aria') }}"
            style="width:76px; height:76px; border-radius:50%; border:none; background:transparent; padding:0; display:flex; align-items:center; justify-content:center; cursor:pointer;">
        @if($bcChatMascoteFile)
            <img id="bcAiChatIconClosed" src="{{ asset('assets/img/mascots/'.$bcChatMascoteFile) }}" alt=""
                 width="76" height="76" style="width:100%; height:100%; object-fit:contain;">
        @else
            <span id="bcAiChatIconClosed" style="width:100%; height:100%; border-radius:50%; background:linear-gradient(135deg,#8b1fb8,#6a0392); box-shadow:0 8px 24px rgba(106,3,146,.35); display:flex; align-items:center; justify-content:center;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
            </span>
        @endif
        <span id="bcAiChatIconClose" style="display:none; width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#8b1fb8,#6a0392); box-shadow:0 8px 24px rgba(106,3,146,.35); align-items:center; justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </span>
    </button>

    <div id="bcAiChatPanel" class="d-none" style="position:absolute; bottom:88px; right:0; width:340px; max-width:calc(100vw - 40px); height:460px; max-height:calc(100vh - 120px); background:var(--app-surface); border:1px solid var(--app-border); border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.25); display:flex; flex-direction:column; overflow:hidden;">
        <div style="padding:14px 16px; background:linear-gradient(135deg,#8b1fb8,#6a0392); display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:8px; color:#fff; font-weight:700; font-size:.92rem;">
                @if($bcChatMascoteFile)
                    <img src="{{ asset('assets/img/mascots/'.$bcChatMascoteFile) }}" alt="" width="26" height="26" style="width:26px; height:26px; border-radius:50%; object-fit:cover; background:#fff;">
                @else
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a4 4 0 00-4 4v1.05A5 5 0 004 12v5a2 2 0 002 2h1l1 3 1-3h6l1 3 1-3h1a2 2 0 002-2v-5a5 5 0 00-4-4.9V6a4 4 0 00-4-4z"/></svg>
                @endif
                {{ $bcChatTitle }}
            </div>
            <button type="button" id="bcAiChatClear" title="{{ __('ia_chat.clear') }}" aria-label="{{ __('ia_chat.clear') }}"
                    style="background:transparent; border:none; color:rgba(255,255,255,.8); cursor:pointer; padding:4px; display:flex;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
            </button>
        </div>

        <div id="bcAiChatMessages" style="flex:1; overflow-y:auto; padding:14px; display:flex; flex-direction:column; gap:10px;"></div>

        <div style="padding:10px; border-top:1px solid var(--app-border); display:flex; gap:8px; flex-shrink:0;">
            <input type="text" id="bcAiChatInput" placeholder="{{ __('ia_chat.placeholder') }}" autocomplete="off"
                   style="flex:1; min-width:0; border:1px solid var(--app-border); border-radius:10px; padding:9px 12px; font-size:.85rem; background:var(--app-bg); color:var(--app-text);">
            <button type="button" id="bcAiChatSend" aria-label="{{ __('ia_chat.send_aria') }}"
                    style="width:38px; height:38px; border-radius:10px; border:none; background:linear-gradient(135deg,#8b1fb8,#6a0392); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var widget = document.getElementById('bcAiChatWidget');
    if (!widget) return;
    var toggle = document.getElementById('bcAiChatToggle');
    var panel = document.getElementById('bcAiChatPanel');
    var iconOpen = document.getElementById('bcAiChatIconClosed');
    var iconClose = document.getElementById('bcAiChatIconClose');
    var messagesBox = document.getElementById('bcAiChatMessages');
    var input = document.getElementById('bcAiChatInput');
    var sendBtn = document.getElementById('bcAiChatSend');
    var clearBtn = document.getElementById('bcAiChatClear');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var loaded = false;
    var sending = false;

    var URLS = {
        history: '{{ route('ai.chat.history') }}',
        send: '{{ route('ai.chat.send') }}',
        clear: '{{ route('ai.chat.clear') }}'
    };

    function scrollToEnd() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function appendMessage(role, texto) {
        var bubble = document.createElement('div');
        var isUser = role === 'user';
        bubble.style.maxWidth = '85%';
        bubble.style.padding = '9px 12px';
        bubble.style.borderRadius = '12px';
        bubble.style.fontSize = '.82rem';
        bubble.style.lineHeight = '1.5';
        bubble.style.whiteSpace = 'pre-wrap';
        bubble.style.alignSelf = isUser ? 'flex-end' : 'flex-start';
        bubble.style.background = isUser ? 'linear-gradient(135deg,#8b1fb8,#6a0392)' : 'rgba(139,31,184,.08)';
        bubble.style.color = isUser ? '#fff' : 'var(--app-text)';
        bubble.textContent = texto;
        messagesBox.appendChild(bubble);
        return bubble;
    }

    function ensureWelcome() {
        if (messagesBox.children.length === 0) {
            appendMessage('model', @json($bcChatWelcome));
        }
    }

    function openPanel() {
        panel.classList.remove('d-none');
        iconOpen.style.display = 'none';
        iconClose.style.display = 'flex';
        toggle.setAttribute('aria-label', @json(__('ia_chat.close_aria')));
        if (!loaded) {
            loaded = true;
            fetch(URLS.history, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (body) {
                    (body.mensagens || []).forEach(function (m) { appendMessage(m.role, m.texto); });
                    ensureWelcome();
                    scrollToEnd();
                })
                .catch(function () { ensureWelcome(); });
        }
        setTimeout(function () { input.focus(); }, 50);
    }

    function closePanel() {
        panel.classList.add('d-none');
        iconOpen.style.display = 'flex';
        iconClose.style.display = 'none';
        toggle.setAttribute('aria-label', @json(__('ia_chat.open_aria')));
    }

    toggle.addEventListener('click', function () {
        if (panel.classList.contains('d-none')) {
            openPanel();
        } else {
            closePanel();
        }
    });

    function doSend() {
        var texto = input.value.trim();
        if (!texto || sending) return;
        sending = true;
        input.value = '';
        appendMessage('user', texto);
        var thinking = appendMessage('model', @json(__('ia_chat.thinking')));
        thinking.style.opacity = '.6';
        scrollToEnd();

        fetch(URLS.send, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf ? csrf.content : ''
            },
            body: JSON.stringify({ mensagem: texto })
        }).then(function (r) {
            return r.json().then(function (body) { return { ok: r.ok, body: body }; });
        }).then(function (res) {
            thinking.remove();
            if (!res.ok) {
                appendMessage('model', (res.body && res.body.error) || @json(__('ia_chat.error')));
            } else {
                appendMessage('model', res.body.resposta);
            }
            scrollToEnd();
        }).catch(function () {
            thinking.remove();
            appendMessage('model', @json(__('ia_chat.error')));
            scrollToEnd();
        }).finally(function () {
            sending = false;
        });
    }

    sendBtn.addEventListener('click', doSend);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            doSend();
        }
    });

    clearBtn.addEventListener('click', function () {
        fetch(URLS.clear, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf ? csrf.content : ''
            }
        }).finally(function () {
            messagesBox.innerHTML = '';
            ensureWelcome();
        });
    });
})();
</script>
