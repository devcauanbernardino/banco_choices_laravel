@php
    $pmMascoteKey = auth()->user()->mascote ?? null;
    $pmMascoteGifs = [
        'robo' => 'robo.gif',
        'fantasma' => 'fantasminha.gif',
        'gato' => 'gato.gif',
    ];
    $pmMascoteGif = $pmMascoteKey ? ($pmMascoteGifs[$pmMascoteKey] ?? null) : null;
@endphp
<script>
window.POMODORO_LOG_URL = @json(route('pomodoro.ciclo.store'));
window.POMODORO_USER_ID = @json((string) auth()->id());
window.POMODORO_AMBIENT_FILES = @json(\App\Support\AmbientSoundLocator::availableUrls());
</script>
<script src="{{ asset('assets/js/pomodoro-engine.js') }}?v={{ @filemtime(public_path('assets/js/pomodoro-engine.js')) }}"></script>

<div id="pmWidget" class="pm-widget d-none" role="status" aria-live="polite">
    <div class="pm-widget__avatar">
        @if ($pmMascoteGif)
            <img src="{{ asset('assets/img/mascots/mascots-gifs/'.$pmMascoteGif) }}" alt="" class="pm-widget__mascote">
        @else
            <span class="material-symbols-outlined pm-widget__icon" aria-hidden="true">timer</span>
        @endif
    </div>
    <div class="pm-widget__bubble">
        <button type="button" class="pm-widget__bubble-body" id="pmWidgetOpen" aria-label="{{ __('pomodoro.widget.back_to_pomodoro') }}">
            <span class="pm-widget__time" id="pmWidgetTime">25:00</span>
            <span class="pm-widget__state" id="pmWidgetState"></span>
        </button>
        <button type="button" class="pm-widget__toggle" id="pmWidgetToggle" aria-label="{{ __('pomodoro.form.pause') }}">
            <span class="material-symbols-outlined" id="pmWidgetToggleIcon" aria-hidden="true">pause</span>
        </button>
        <button type="button" class="pm-widget__toggle pm-widget__pip d-none" id="pmWidgetPip" aria-label="{{ __('pomodoro.widget.pip_open') }}" title="{{ __('pomodoro.widget.pip_open') }}">
            <span class="material-symbols-outlined" aria-hidden="true">picture_in_picture_alt</span>
        </button>
    </div>
</div>

<style>
.pm-widget {
    position: fixed; right: 20px; bottom: 20px; z-index: 1050;
    display: flex; flex-direction: row-reverse; align-items: center; gap: 12px;
    font-family: 'Inter', system-ui, sans-serif;
}
.pm-widget__avatar {
    width: 76px; height: 76px; border-radius: 50%;
    background: var(--app-surface, #fff);
    border: 1px solid var(--app-border, rgba(120,120,140,.2));
    box-shadow: 0 8px 24px rgba(31,10,60,.2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}
[data-theme="dark"] .pm-widget__avatar { box-shadow: 0 8px 24px rgba(0,0,0,.45); }
.pm-widget__mascote { width: 66px; height: 66px; object-fit: contain; }
.pm-widget__icon { font-size: 2rem; color: #8b1fb8; }
[data-theme="dark"] .pm-widget__icon { color: #c77dfd; }

.pm-widget__bubble {
    position: relative;
    background: var(--app-surface, #fff);
    border: 1px solid var(--app-border, rgba(120,120,140,.2));
    border-radius: 18px;
    padding: 10px 12px 10px 16px;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 12px 30px rgba(31,10,60,.18);
}
[data-theme="dark"] .pm-widget__bubble { box-shadow: 0 12px 30px rgba(0,0,0,.45); }
.pm-widget__bubble::before {
    content: '';
    position: absolute;
    right: -8px; top: 50%; margin-top: -7px;
    width: 0; height: 0;
    border-top: 7px solid transparent;
    border-bottom: 7px solid transparent;
    border-left: 9px solid var(--app-surface, #fff);
    filter: drop-shadow(1px 0 0 var(--app-border, rgba(120,120,140,.2)));
}
.pm-widget__bubble-body { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.2; border: none; background: transparent; padding: 0; cursor: pointer; }
.pm-widget__time { font-size: 1.15rem; font-weight: 800; color: var(--app-text); font-variant-numeric: tabular-nums; }
.pm-widget__state { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b1fb8; }
[data-theme="dark"] .pm-widget__state { color: #c77dfd; }
.pm-widget.is-break .pm-widget__state { color: #0d9488; }
[data-theme="dark"] .pm-widget.is-break .pm-widget__state { color: #2dd4bf; }
.pm-widget__toggle { width: 34px; height: 34px; border-radius: 50%; border: none; background: rgba(139,31,184,.12); color: #6a0392; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
[data-theme="dark"] .pm-widget__toggle { background: rgba(199,125,253,.16); color: #e0bbfd; }
.pm-widget__toggle .material-symbols-outlined { font-size: 1.25rem; }
@media (max-width: 560px) {
    .pm-widget { right: 12px; bottom: 12px; }
    .pm-widget__avatar { width: 60px; height: 60px; }
    .pm-widget__mascote { width: 50px; height: 50px; }
}
</style>

<script>
(function () {
    var widget = document.getElementById('pmWidget');
    if (!widget) return;
    var onPomodoroPage = window.location.pathname.indexOf('/pomodoro') === 0;
    var pageTitle = document.title;
    var chatWidget = document.getElementById('bcAiChatWidget');

    var timeEl = document.getElementById('pmWidgetTime');
    var stateEl = document.getElementById('pmWidgetState');
    var openBtn = document.getElementById('pmWidgetOpen');
    var toggleBtn = document.getElementById('pmWidgetToggle');
    var toggleIcon = document.getElementById('pmWidgetToggleIcon');
    var pipBtn = document.getElementById('pmWidgetPip');

    var LABELS = {
        focus: @json(__('pomodoro.state.focus')),
        brk: @json(__('pomodoro.state.break')),
        pause: @json(__('pomodoro.form.pause')),
        resume: @json(__('pomodoro.form.resume')),
        pipHint: @json(__('pomodoro.widget.pip_hint'))
    };
    var POMODORO_URL = @json(route('pomodoro.index'));
    var mascoteSrc = @json($pmMascoteGif ? asset('assets/img/mascots/mascots-gifs/'.$pmMascoteGif) : null);

    openBtn.addEventListener('click', function () {
        window.location.href = POMODORO_URL;
    });

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var st = window.PomodoroEngine.getState();
        if (st.running) { window.PomodoroEngine.pause(); } else { window.PomodoroEngine.resume(); }
    });

    // ---- Picture-in-Picture (janela flutuante por cima de outros apps) ----
    var pipSupported = typeof window.documentPictureInPicture !== 'undefined';
    var pipWindow = null;
    var pipEls = null;

    function buildPipContent(pipDoc) {
        var meta = pipDoc.createElement('meta');
        meta.name = 'viewport';
        meta.content = 'width=device-width, initial-scale=1';
        pipDoc.head.appendChild(meta);

        var style = pipDoc.createElement('style');
        style.textContent = 'html,body{margin:0;height:100%;width:100%;overflow:hidden;}' +
            'body{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:clamp(4px,3vh,10px);font-family:Inter,system-ui,sans-serif;background:#15101c;color:#fff;text-align:center;padding:clamp(6px,4vw,12px);box-sizing:border-box;}' +
            'img{width:clamp(28px,20vw,64px);height:clamp(28px,20vw,64px);object-fit:contain;flex-shrink:0;}' +
            '.t{font-size:clamp(1.1rem,16vw,2.4rem);font-weight:800;font-variant-numeric:tabular-nums;line-height:1;}' +
            '.s{font-size:clamp(.6rem,4vw,.8rem);font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#c77dfd;}' +
            'button{border:none;border-radius:10px;padding:clamp(5px,2vh,8px) clamp(10px,5vw,18px);font-weight:700;font-size:clamp(.68rem,3.5vw,.85rem);background:linear-gradient(135deg,#8b1fb8,#6a0392);color:#fff;cursor:pointer;flex-shrink:0;}' +
            '.h{font-size:.62rem;color:rgba(255,255,255,.5);max-width:90%;}' +
            '@media (max-height:160px){img{display:none;}.h{display:none;}}' +
            '@media (max-height:110px){.s{display:none;}}' +
            '@media (max-width:160px){.h{display:none;}}';
        pipDoc.head.appendChild(style);

        if (mascoteSrc) {
            var img = pipDoc.createElement('img');
            img.src = mascoteSrc;
            img.alt = '';
            pipDoc.body.appendChild(img);
        }
        var t = pipDoc.createElement('div');
        t.className = 't';
        var s = pipDoc.createElement('div');
        s.className = 's';
        var btn = pipDoc.createElement('button');
        btn.type = 'button';
        var hint = pipDoc.createElement('div');
        hint.className = 'h';
        hint.textContent = LABELS.pipHint;

        btn.addEventListener('click', function () {
            var st = window.PomodoroEngine.getState();
            if (st.running) { window.PomodoroEngine.pause(); } else { window.PomodoroEngine.resume(); }
        });

        pipDoc.body.appendChild(t);
        pipDoc.body.appendChild(s);
        pipDoc.body.appendChild(btn);
        pipDoc.body.appendChild(hint);

        pipEls = { time: t, state: s, btn: btn };
    }

    function updatePipContent(st) {
        if (!pipWindow || !pipEls || !st) return;
        pipEls.time.textContent = formatTime(st);
        pipEls.state.textContent = st.mode === 'focus' ? LABELS.focus : LABELS.brk;
        pipEls.btn.textContent = st.running ? LABELS.pause : LABELS.resume;
    }

    function openPip() {
        if (!pipSupported || pipWindow) return;
        window.documentPictureInPicture.requestWindow({ width: 260, height: 220 }).then(function (win) {
            pipWindow = win;
            buildPipContent(pipWindow.document);
            updatePipContent(window.PomodoroEngine.getState());
            pipWindow.addEventListener('pagehide', function () {
                pipWindow = null;
                pipEls = null;
            });
        }).catch(function () { /* usuario cancelou ou navegador bloqueou */ });
    }

    if (pipBtn) {
        if (pipSupported) {
            pipBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                openPip();
            });
        } else {
            pipBtn.remove();
        }
    }

    function formatTime(st) {
        var m = Math.floor(st.remainingSec / 60);
        var s = st.remainingSec % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }

    function updateTitle(st) {
        if (!st || st.mode === 'idle') {
            document.title = pageTitle;
            return;
        }
        var label = st.mode === 'focus' ? LABELS.focus : LABELS.brk;
        document.title = formatTime(st) + ' · ' + label + ' — ' + pageTitle;
    }

    function render(st) {
        updateTitle(st);
        updatePipContent(st);

        if (onPomodoroPage || !st || st.mode === 'idle') {
            widget.classList.add('d-none');
            if (chatWidget) chatWidget.style.display = '';
            return;
        }
        widget.classList.remove('d-none');
        widget.classList.toggle('is-break', st.mode === 'break');
        timeEl.textContent = formatTime(st);
        stateEl.textContent = st.mode === 'focus' ? LABELS.focus : LABELS.brk;
        toggleIcon.textContent = st.running ? 'pause' : 'play_arrow';
        if (pipBtn && pipSupported) pipBtn.classList.remove('d-none');
        // o balao ocupa o mesmo canto do widget de chat - substitui em vez de sobrepor
        if (chatWidget) chatWidget.style.display = 'none';
    }

    window.addEventListener('pomodoro:update', function (e) { render(e.detail); });
    render(window.PomodoroEngine.getState());
})();
</script>
