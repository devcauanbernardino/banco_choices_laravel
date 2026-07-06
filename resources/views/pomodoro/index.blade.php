@extends('layouts.app')

@section('title', __('pomodoro.page_title'))
@section('mobile_title', __('pomodoro.mobile_title'))
@section('topbar_title', __('pomodoro.mobile_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shared-select.css') }}?v={{ @filemtime(public_path('assets/css/shared-select.css')) }}">
<style>
.pm-header { margin-bottom: 26px; }
.pm-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.pm-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }

.pm-layout { display: grid; grid-template-columns: minmax(320px, 420px) 1fr; gap: 22px; align-items: start; }
@media (max-width: 900px) { .pm-layout { grid-template-columns: 1fr; } }

.pm-card {
    border-radius: 20px;
    padding: 26px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
}
[data-theme="dark"] .pm-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }

.pm-subject-label { font-size: .82rem; font-weight: 700; color: var(--app-text); display: block; margin-bottom: 8px; }
.pm-error { color: #dc2626; font-size: .8rem; margin-top: 8px; display: none; }
.pm-error.is-visible { display: block; }

.pm-durations { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px; }
.pm-stepper-group span.pm-subject-label { margin-bottom: 6px; }
.pm-stepper { display: flex; align-items: center; border: 1px solid rgba(120,120,140,.25); border-radius: 10px; background: rgba(255,255,255,.5); overflow: hidden; }
[data-theme="dark"] .pm-stepper { border-color: rgba(255,255,255,.14); background: rgba(255,255,255,.06); }
.pm-stepper__btn { flex-shrink: 0; width: 34px; height: 38px; border: none; background: transparent; color: #8b1fb8; font-size: 1.1rem; font-weight: 700; cursor: pointer; }
.pm-stepper__btn:hover { background: rgba(139,31,184,.12); }
[data-theme="dark"] .pm-stepper__btn { color: #c77dfd; }
.pm-stepper__val { flex: 1; text-align: center; font-size: .92rem; font-weight: 700; color: var(--app-text); }
.pm-stepper.is-disabled { opacity: .5; pointer-events: none; }

.pm-ring-wrap { display: flex; flex-direction: column; align-items: center; margin-top: 26px; }
.pm-ring { position: relative; width: 220px; height: 220px; }
.pm-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.pm-ring circle { fill: none; stroke-width: 10; }
.pm-ring .pm-ring__bg { stroke: rgba(120,120,140,.18); }
.pm-ring .pm-ring__fg { stroke: #8b1fb8; stroke-linecap: round; transition: stroke-dashoffset .3s linear, stroke .3s ease; }
.pm-ring.is-break .pm-ring__fg { stroke: #0d9488; }
.pm-ring__center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.pm-ring__time { font-size: 2.4rem; font-weight: 800; color: var(--app-text); font-variant-numeric: tabular-nums; }
.pm-ring__state { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8b1fb8; margin-top: 2px; }
.pm-ring.is-break .pm-ring__state { color: #0d9488; }
[data-theme="dark"] .pm-ring__state { color: #c77dfd; }
[data-theme="dark"] .pm-ring.is-break .pm-ring__state { color: #2dd4bf; }
.pm-cycle-count { font-size: .78rem; color: var(--app-muted); margin-top: 10px; }

.pm-summary { text-align: center; margin-top: 22px; }
.pm-summary__title { font-size: 1.05rem; font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.pm-summary__body { font-size: .88rem; color: var(--app-muted); margin-bottom: 18px; }

.pm-controls { display: flex; gap: 10px; margin-top: 22px; justify-content: center; flex-wrap: wrap; }
.pm-btn { padding: 11px 22px; border-radius: 12px; border: none; font-weight: 700; font-size: .88rem; cursor: pointer; }
.pm-btn--primary { background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.pm-btn--ghost { background: rgba(139,31,184,.1); color: #6a0392; border: 1px solid rgba(139,31,184,.25); }
[data-theme="dark"] .pm-btn--ghost { background: rgba(199,125,253,.14); color: #e0bbfd; border-color: rgba(199,125,253,.3); }
.pm-btn:disabled { opacity: .4; cursor: default; }

.pm-stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; margin-bottom: 18px; }
@media (max-width: 500px) { .pm-stats-grid { grid-template-columns: 1fr; } }
.pm-stat-card { text-align: center; padding: 18px; }
.pm-stat-card__label { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--app-muted); margin-bottom: 8px; }
.pm-stat-card__value { font-size: 1.8rem; font-weight: 800; color: #8b1fb8; }
[data-theme="dark"] .pm-stat-card__value { color: #c77dfd; }
.pm-stat-card__sub { font-size: .8rem; color: var(--app-muted); margin-top: 2px; }

.pm-section-title { font-size: .92rem; font-weight: 700; color: var(--app-text); margin-bottom: 14px; }
.pm-subject-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(120,120,140,.14); font-size: .86rem; }
.pm-subject-row:last-child { border-bottom: none; }
.pm-subject-row__name { color: var(--app-text); font-weight: 600; }
.pm-subject-row__time { color: var(--app-muted); }

.pm-session-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(120,120,140,.14); font-size: .84rem; }
.pm-session-row:last-child { border-bottom: none; }
.pm-session-row__meta { color: var(--app-muted); font-size: .76rem; }
.pm-empty { text-align: center; color: var(--app-muted); font-size: .85rem; padding: 20px 0; }
</style>
@endpush

@section('content')
<div class="pm-header">
    <h1>{{ __('pomodoro.header.title') }}</h1>
    <p>{{ __('pomodoro.header.sub') }}</p>
</div>

@if ($materias->isEmpty())
    <p class="text-muted">{{ __('pomodoro.no_subjects') }}</p>
@else
    <div class="pm-layout">
        <div class="pm-card">
            <label class="pm-subject-label" for="pmMateria">{{ __('pomodoro.form.subject_label') }}</label>
            <select id="pmMateria" class="bc-styled-select bc-styled-select--fluid">
                @foreach ($materias as $m)
                    <option value="{{ $m->id }}">{{ $m->nome }}</option>
                @endforeach
            </select>
            <p class="pm-error" id="pmError">{{ __('pomodoro.err.pick_subject') }}</p>

            <div class="pm-durations">
                <div class="pm-stepper-group">
                    <span class="pm-subject-label">{{ __('pomodoro.form.focus_label') }}</span>
                    <div class="pm-stepper" id="pmFocusStepper">
                        <button type="button" class="pm-stepper__btn" data-pm-step="-1">−</button>
                        <span class="pm-stepper__val" id="pmFocusVal">25</span>
                        <button type="button" class="pm-stepper__btn" data-pm-step="1">+</button>
                    </div>
                </div>
                <div class="pm-stepper-group">
                    <span class="pm-subject-label">{{ __('pomodoro.form.break_label') }}</span>
                    <div class="pm-stepper" id="pmBreakStepper">
                        <button type="button" class="pm-stepper__btn" data-pm-step="-1">−</button>
                        <span class="pm-stepper__val" id="pmBreakVal">5</span>
                        <button type="button" class="pm-stepper__btn" data-pm-step="1">+</button>
                    </div>
                </div>
            </div>

            <div class="pm-ring-wrap">
                <div class="pm-ring" id="pmRing">
                    <svg viewBox="0 0 220 220">
                        <circle class="pm-ring__bg" cx="110" cy="110" r="100"></circle>
                        <circle class="pm-ring__fg" id="pmRingFg" cx="110" cy="110" r="100"></circle>
                    </svg>
                    <div class="pm-ring__center">
                        <span class="pm-ring__time" id="pmTime">25:00</span>
                        <span class="pm-ring__state" id="pmState">{{ __('pomodoro.state.idle') }}</span>
                    </div>
                </div>
                <p class="pm-cycle-count" id="pmCycleCount"></p>
            </div>

            <div class="pm-controls" id="pmControls">
                <button type="button" class="pm-btn pm-btn--primary" id="pmStartBtn">{{ __('pomodoro.form.start') }}</button>
                <button type="button" class="pm-btn pm-btn--ghost d-none" id="pmPauseBtn">{{ __('pomodoro.form.pause') }}</button>
                <button type="button" class="pm-btn pm-btn--ghost d-none" id="pmSkipBtn">{{ __('pomodoro.form.skip') }}</button>
                <button type="button" class="pm-btn pm-btn--ghost d-none" id="pmFinishBtn">{{ __('pomodoro.form.finish') }}</button>
            </div>

            <div class="pm-summary d-none" id="pmSummary">
                <p class="pm-summary__title">{{ __('pomodoro.summary.title') }}</p>
                <p class="pm-summary__body" id="pmSummaryBody"></p>
                <button type="button" class="pm-btn pm-btn--primary" id="pmSummaryOkBtn">{{ __('pomodoro.summary.cta_ok') }}</button>
            </div>
        </div>

        <div>
            <div class="pm-stats-grid">
                <div class="pm-card pm-stat-card">
                    <div class="pm-stat-card__label">{{ __('pomodoro.stats.today_title') }}</div>
                    <div class="pm-stat-card__value" id="pmTodayMinutes">{{ __('pomodoro.stats.today_minutes', ['n' => $hoje['minutos']]) }}</div>
                    <div class="pm-stat-card__sub" id="pmTodayCycles">{{ __('pomodoro.stats.today_cycles', ['n' => $hoje['ciclos']]) }}</div>
                </div>
                <div class="pm-card pm-stat-card">
                    <div class="pm-stat-card__label">{{ __('pomodoro.stats.streak_title') }}</div>
                    <div class="pm-stat-card__value" id="pmStreak">{{ __('pomodoro.stats.streak_days', ['n' => $streak]) }}</div>
                </div>
            </div>

            <div class="pm-card mb-3">
                <h2 class="pm-section-title">{{ __('pomodoro.stats.by_subject_title') }}</h2>
                @if (empty($porMateria))
                    <p class="pm-empty">{{ __('pomodoro.stats.no_data') }}</p>
                @else
                    @foreach ($porMateria as $pm)
                        <div class="pm-subject-row">
                            <span class="pm-subject-row__name">{{ $pm['materia_nome'] }}</span>
                            <span class="pm-subject-row__time">{{ __('pomodoro.stats.today_minutes', ['n' => $pm['total_minutos']]) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="pm-card">
                <h2 class="pm-section-title">{{ __('pomodoro.stats.recent_title') }}</h2>
                @if (empty($sessoesRecentes))
                    <p class="pm-empty">{{ __('pomodoro.stats.no_data') }}</p>
                @else
                    @foreach ($sessoesRecentes as $s)
                        <div class="pm-session-row">
                            <span>{{ __('pomodoro.stats.session_summary', ['materia' => $s['materia_nome'], 'ciclos' => $s['total_ciclos'], 'min' => $s['total_minutos']]) }}</span>
                            <span class="pm-session-row__meta">{{ \Illuminate\Support\Carbon::parse($s['data'])->diffForHumans() }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}" defer></script>
<script>
(function () {
    var materiaSelect = document.getElementById('pmMateria');
    if (!materiaSelect) return;

    var errorEl = document.getElementById('pmError');
    var focusVal = document.getElementById('pmFocusVal');
    var breakVal = document.getElementById('pmBreakVal');
    var focusStepper = document.getElementById('pmFocusStepper');
    var breakStepper = document.getElementById('pmBreakStepper');
    var ring = document.getElementById('pmRing');
    var ringFg = document.getElementById('pmRingFg');
    var timeEl = document.getElementById('pmTime');
    var stateEl = document.getElementById('pmState');
    var cycleCountEl = document.getElementById('pmCycleCount');
    var startBtn = document.getElementById('pmStartBtn');
    var pauseBtn = document.getElementById('pmPauseBtn');
    var skipBtn = document.getElementById('pmSkipBtn');
    var finishBtn = document.getElementById('pmFinishBtn');
    var controlsEl = document.getElementById('pmControls');
    var summaryEl = document.getElementById('pmSummary');
    var summaryBodyEl = document.getElementById('pmSummaryBody');
    var summaryOkBtn = document.getElementById('pmSummaryOkBtn');
    var csrf = document.querySelector('meta[name="csrf-token"]');

    var LABELS = {
        focus: @json(__('pomodoro.state.focus')),
        brk: @json(__('pomodoro.state.break')),
        idle: @json(__('pomodoro.state.idle')),
        pause: @json(__('pomodoro.form.pause')),
        resume: @json(__('pomodoro.form.resume')),
        cycle: @json(__('pomodoro.cycle_label', ['n' => '__N__'])),
        todayMinutes: @json(__('pomodoro.stats.today_minutes', ['n' => '__N__'])),
        todayCycles: @json(__('pomodoro.stats.today_cycles', ['n' => '__N__'])),
        streakDays: @json(__('pomodoro.stats.streak_days', ['n' => '__N__'])),
        summaryBody: @json(__('pomodoro.summary.body', ['ciclos' => '__C__', 'min' => '__M__'])),
        summaryEmpty: @json(__('pomodoro.summary.body_empty')),
    };

    var RADIUS = 100;
    var CIRC = 2 * Math.PI * RADIUS;
    ringFg.style.strokeDasharray = CIRC;
    ringFg.style.strokeDashoffset = 0;

    var focusMin = 25, breakMin = 5;
    var mode = 'idle'; // idle | focus | break
    var running = false;
    var remaining = focusMin * 60;
    var total = focusMin * 60;
    var timerHandle = null;
    var cycles = 0;
    var sessaoUid = null;
    var audioCtx = null;

    function updateStepperVal(el, delta, min, max) {
        var current = parseInt(el.textContent, 10);
        var next = Math.max(min, Math.min(max, current + delta));
        el.textContent = next;
        return next;
    }

    focusStepper.querySelectorAll('[data-pm-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            focusMin = updateStepperVal(focusVal, parseInt(btn.dataset.pmStep, 10), 1, 90);
            if (mode === 'idle') { remaining = focusMin * 60; total = remaining; renderTime(); }
        });
    });
    breakStepper.querySelectorAll('[data-pm-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            breakMin = updateStepperVal(breakVal, parseInt(btn.dataset.pmStep, 10), 1, 30);
        });
    });

    function beep() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var o = audioCtx.createOscillator();
            var g = audioCtx.createGain();
            o.connect(g); g.connect(audioCtx.destination);
            o.frequency.value = 660;
            g.gain.setValueAtTime(0.0001, audioCtx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.4);
            o.start();
            o.stop(audioCtx.currentTime + 0.4);
        } catch (e) { /* ignore */ }
    }

    function renderTime() {
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        timeEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        var frac = total > 0 ? remaining / total : 0;
        ringFg.style.strokeDashoffset = CIRC * frac;
        stateEl.textContent = mode === 'focus' ? LABELS.focus : (mode === 'break' ? LABELS.brk : LABELS.idle);
        ring.classList.toggle('is-break', mode === 'break');
        cycleCountEl.textContent = cycles > 0 ? LABELS.cycle.replace('__N__', cycles) : '';
    }

    function headers() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        };
    }

    function logCycle() {
        fetch('{{ route('pomodoro.ciclo.store') }}', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({
                materia_id: materiaSelect.value,
                sessao_uid: sessaoUid,
                duracao_minutos: focusMin
            })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.hoje) {
                document.getElementById('pmTodayMinutes').textContent = LABELS.todayMinutes.replace('__N__', data.hoje.minutos);
                document.getElementById('pmTodayCycles').textContent = LABELS.todayCycles.replace('__N__', data.hoje.ciclos);
            }
            if (typeof data.streak === 'number') {
                document.getElementById('pmStreak').textContent = LABELS.streakDays.replace('__N__', data.streak);
            }
        }).catch(function () { /* silencioso: nao interrompe o timer local */ });
    }

    function tick() {
        remaining -= 1;
        if (remaining < 0) {
            beep();
            if (mode === 'focus') {
                cycles += 1;
                logCycle();
                mode = 'break';
                remaining = breakMin * 60;
                total = remaining;
            } else {
                mode = 'focus';
                remaining = focusMin * 60;
                total = remaining;
            }
        }
        renderTime();
    }

    function startTimer() {
        if (timerHandle) return;
        timerHandle = setInterval(tick, 1000);
    }
    function stopTimer() {
        if (timerHandle) { clearInterval(timerHandle); timerHandle = null; }
    }

    startBtn.addEventListener('click', function () {
        if (!materiaSelect.value) {
            errorEl.classList.add('is-visible');
            return;
        }
        errorEl.classList.remove('is-visible');
        if (mode === 'idle') {
            mode = 'focus';
            remaining = focusMin * 60;
            total = remaining;
            cycles = 0;
            sessaoUid = 'pm-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
            focusStepper.classList.add('is-disabled');
            breakStepper.classList.add('is-disabled');
        }
        running = true;
        startTimer();
        renderTime();
        summaryEl.classList.add('d-none');
        controlsEl.classList.remove('d-none');
        startBtn.classList.add('d-none');
        pauseBtn.classList.remove('d-none');
        skipBtn.classList.remove('d-none');
        finishBtn.classList.remove('d-none');
        pauseBtn.textContent = LABELS.pause;
    });

    pauseBtn.addEventListener('click', function () {
        if (running) {
            running = false;
            stopTimer();
            pauseBtn.textContent = LABELS.resume;
        } else {
            running = true;
            startTimer();
            pauseBtn.textContent = LABELS.pause;
        }
    });

    skipBtn.addEventListener('click', function () {
        remaining = 0;
        tick();
    });

    function backToIdle() {
        mode = 'idle';
        cycles = 0;
        sessaoUid = null;
        remaining = focusMin * 60;
        total = remaining;
        focusStepper.classList.remove('is-disabled');
        breakStepper.classList.remove('is-disabled');
        renderTime();
        summaryEl.classList.add('d-none');
        controlsEl.classList.remove('d-none');
        startBtn.classList.remove('d-none');
        pauseBtn.classList.add('d-none');
        skipBtn.classList.add('d-none');
        finishBtn.classList.add('d-none');
    }

    finishBtn.addEventListener('click', function () {
        stopTimer();
        running = false;
        var totalMin = cycles * focusMin;
        summaryBodyEl.textContent = cycles > 0
            ? LABELS.summaryBody.replace('__C__', cycles).replace('__M__', totalMin)
            : LABELS.summaryEmpty;
        controlsEl.classList.add('d-none');
        summaryEl.classList.remove('d-none');
    });

    summaryOkBtn.addEventListener('click', backToIdle);

    renderTime();
})();
</script>
@endpush
