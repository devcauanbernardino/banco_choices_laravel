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
    </div>
</div>

<style>
.pm-widget {
    position: fixed; left: 20px; bottom: 20px; z-index: 1045;
    display: flex; align-items: center;
    font-family: 'Inter', system-ui, sans-serif;
}
.pm-widget__avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--app-surface, #fff);
    border: 1px solid var(--app-border, rgba(120,120,140,.2));
    box-shadow: 0 8px 22px rgba(31,10,60,.18);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}
[data-theme="dark"] .pm-widget__avatar { box-shadow: 0 8px 22px rgba(0,0,0,.4); }
.pm-widget__mascote { width: 44px; height: 44px; object-fit: contain; }
.pm-widget__icon { font-size: 1.5rem; color: #8b1fb8; }
[data-theme="dark"] .pm-widget__icon { color: #c77dfd; }

.pm-widget__bubble {
    position: relative;
    margin-left: 10px;
    background: var(--app-surface, #fff);
    border: 1px solid var(--app-border, rgba(120,120,140,.2));
    border-radius: 16px;
    padding: 7px 8px 7px 14px;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 10px 26px rgba(31,10,60,.16);
}
[data-theme="dark"] .pm-widget__bubble { box-shadow: 0 10px 26px rgba(0,0,0,.4); }
.pm-widget__bubble::before {
    content: '';
    position: absolute;
    left: -7px; top: 50%; margin-top: -6px;
    width: 0; height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-right: 8px solid var(--app-surface, #fff);
    filter: drop-shadow(-1px 0 0 var(--app-border, rgba(120,120,140,.2)));
}
.pm-widget__bubble-body { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; border: none; background: transparent; padding: 0; cursor: pointer; }
.pm-widget__time { font-size: .95rem; font-weight: 800; color: var(--app-text); font-variant-numeric: tabular-nums; }
.pm-widget__state { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8b1fb8; }
[data-theme="dark"] .pm-widget__state { color: #c77dfd; }
.pm-widget.is-break .pm-widget__state { color: #0d9488; }
[data-theme="dark"] .pm-widget.is-break .pm-widget__state { color: #2dd4bf; }
.pm-widget__toggle { width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(139,31,184,.12); color: #6a0392; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
[data-theme="dark"] .pm-widget__toggle { background: rgba(199,125,253,.16); color: #e0bbfd; }
.pm-widget__toggle .material-symbols-outlined { font-size: 1rem; }
@media (max-width: 560px) {
    .pm-widget { left: 10px; bottom: 10px; }
    .pm-widget__avatar { width: 44px; height: 44px; }
    .pm-widget__mascote { width: 36px; height: 36px; }
}
</style>

<script>
(function () {
    var widget = document.getElementById('pmWidget');
    if (!widget) return;
    var onPomodoroPage = window.location.pathname.indexOf('/pomodoro') === 0;
    var pageTitle = document.title;

    var timeEl = document.getElementById('pmWidgetTime');
    var stateEl = document.getElementById('pmWidgetState');
    var openBtn = document.getElementById('pmWidgetOpen');
    var toggleBtn = document.getElementById('pmWidgetToggle');
    var toggleIcon = document.getElementById('pmWidgetToggleIcon');

    var LABELS = {
        focus: @json(__('pomodoro.state.focus')),
        brk: @json(__('pomodoro.state.break'))
    };
    var POMODORO_URL = @json(route('pomodoro.index'));

    openBtn.addEventListener('click', function () {
        window.location.href = POMODORO_URL;
    });

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var st = window.PomodoroEngine.getState();
        if (st.running) { window.PomodoroEngine.pause(); } else { window.PomodoroEngine.resume(); }
    });

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

        if (onPomodoroPage || !st || st.mode === 'idle') {
            widget.classList.add('d-none');
            return;
        }
        widget.classList.remove('d-none');
        widget.classList.toggle('is-break', st.mode === 'break');
        timeEl.textContent = formatTime(st);
        stateEl.textContent = st.mode === 'focus' ? LABELS.focus : LABELS.brk;
        toggleIcon.textContent = st.running ? 'pause' : 'play_arrow';
    }

    window.addEventListener('pomodoro:update', function (e) { render(e.detail); });
    render(window.PomodoroEngine.getState());
})();
</script>
