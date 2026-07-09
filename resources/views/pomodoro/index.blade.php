@extends('layouts.app')

@section('title', __('pomodoro.page_title'))
@section('mobile_title', __('pomodoro.mobile_title'))
@section('topbar_title', __('pomodoro.mobile_title'))

@php
    $pmMascoteKey = auth()->user()->mascote ?? null;
    $pmMascoteGifs = [
        'robo' => 'robo.gif',
        'fantasma' => 'fantasminha.gif',
        'gato' => 'gato.gif',
    ];
    $pmMascoteGif = $pmMascoteKey ? ($pmMascoteGifs[$pmMascoteKey] ?? null) : null;

    // 'rain' sempre aparece (tem fallback sintetizado); as demais so aparecem
    // quando o arquivo real foi enviado (ver App\Support\AmbientSoundLocator).
    $pmAmbientFiles = \App\Support\AmbientSoundLocator::available();
    $pmExtraAmbientSlugs = array_keys(array_diff_key($pmAmbientFiles, ['rain' => true]));
    $pmAmbientLabel = function (string $slug) {
        $key = 'pomodoro.ambient.'.$slug;

        return \Illuminate\Support\Facades\Lang::has($key) ? __($key) : \Illuminate\Support\Str::title(str_replace(['_', '-'], ' ', $slug));
    };
@endphp

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
.pm-chart-wrap { position: relative; height: 200px; }

.pm-card { position: relative; }
.pm-fullscreen-btn { position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: var(--app-muted); cursor: pointer; display: flex; padding: 4px; border-radius: 8px; }
.pm-fullscreen-btn:hover { background: rgba(139,31,184,.1); color: #8b1fb8; }
.pm-fullscreen-btn .material-symbols-outlined { font-size: 1.3rem; }

.pm-ambient { margin-top: 26px; padding-top: 20px; border-top: 1px solid rgba(120,120,140,.14); }
.pm-ambient__options { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.pm-ambient__btn { padding: 8px 14px; border-radius: 999px; border: 1px solid rgba(120,120,140,.25); background: rgba(255,255,255,.5); color: var(--app-text); font-size: .78rem; font-weight: 600; cursor: pointer; }
[data-theme="dark"] .pm-ambient__btn { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); }
.pm-ambient__btn.is-active { background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; border-color: transparent; }
.pm-ambient__volume { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
.pm-ambient__volume .material-symbols-outlined { color: var(--app-muted); font-size: 1.1rem; flex-shrink: 0; }
.pm-ambient__volume input[type="range"] {
    flex: 1;
    -webkit-appearance: none;
    appearance: none;
    height: 6px;
    border-radius: 999px;
    background: rgba(120,120,140,.25);
    outline: none;
    cursor: pointer;
}
[data-theme="dark"] .pm-ambient__volume input[type="range"] { background: rgba(255,255,255,.16); }
.pm-ambient__volume input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px; height: 16px; border-radius: 50%;
    background: linear-gradient(135deg,#8b1fb8,#6a0392);
    box-shadow: 0 2px 6px rgba(106,3,146,.4);
    cursor: pointer;
    border: none;
}
.pm-ambient__volume input[type="range"]::-moz-range-track {
    height: 6px; border-radius: 999px; background: rgba(120,120,140,.25);
}
[data-theme="dark"] .pm-ambient__volume input[type="range"]::-moz-range-track { background: rgba(255,255,255,.16); }
.pm-ambient__volume input[type="range"]::-moz-range-thumb {
    width: 16px; height: 16px; border-radius: 50%;
    background: linear-gradient(135deg,#8b1fb8,#6a0392);
    border: none;
    cursor: pointer;
}

.pm-fs-overlay { position: fixed; inset: 0; z-index: 2000; background: #0b0416; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; }
.pm-fs-overlay.d-none { display: none; }
.pm-fs-overlay img.pm-fs-mascote { width: 120px; height: 120px; object-fit: contain; margin-bottom: 18px; }
.pm-fs-time { font-size: clamp(3rem,14vw,7rem); font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1; }
.pm-fs-state { font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #c77dfd; margin-top: 10px; }
.pm-fs-subject { font-size: .9rem; color: rgba(255,255,255,.6); margin-top: 6px; }
.pm-fs-controls { display: flex; gap: 12px; margin-top: 34px; }
.pm-fs-controls .pm-btn--ghost { background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.25); }
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
            <button type="button" class="pm-fullscreen-btn" id="pmFullscreenBtn" aria-label="{{ __('pomodoro.form.fullscreen') }}" title="{{ __('pomodoro.form.fullscreen') }}">
                <span class="material-symbols-outlined" aria-hidden="true">fullscreen</span>
            </button>
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

            <div class="pm-ambient">
                <span class="pm-subject-label">{{ __('pomodoro.ambient.title') }}</span>
                <div class="pm-ambient__options" id="pmAmbientOptions">
                    <button type="button" class="pm-ambient__btn is-active" data-pm-ambient="none">{{ __('pomodoro.ambient.none') }}</button>
                    <button type="button" class="pm-ambient__btn" data-pm-ambient="rain">{{ __('pomodoro.ambient.rain') }}</button>
                    @foreach ($pmExtraAmbientSlugs as $slug)
                        <button type="button" class="pm-ambient__btn" data-pm-ambient="{{ $slug }}">{{ $pmAmbientLabel($slug) }}</button>
                    @endforeach
                </div>
                <div class="pm-ambient__volume">
                    <span class="material-symbols-outlined" aria-hidden="true">volume_down</span>
                    <input type="range" id="pmAmbientVolume" min="0" max="1" step="0.05" value="0.4" aria-label="{{ __('pomodoro.ambient.volume') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">volume_up</span>
                </div>
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
                <h2 class="pm-section-title">{{ __('pomodoro.stats.chart_title') }}</h2>
                <div class="pm-chart-wrap">
                    <canvas id="pmChart"></canvas>
                </div>
            </div>

            <div class="pm-card mb-3">
                <h2 class="pm-section-title">{{ __('pomodoro.stats.by_subject_title') }}</h2>
                <div id="pmPorMateria">
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
            </div>

            <div class="pm-card">
                <h2 class="pm-section-title">{{ __('pomodoro.stats.recent_title') }}</h2>
                <div id="pmSessoesRecentes">
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
    </div>

    <div class="pm-fs-overlay d-none" id="pmFsOverlay">
        @if ($pmMascoteGif)
            <img src="{{ asset('assets/img/mascots/mascots-gifs/'.$pmMascoteGif) }}" alt="" class="pm-fs-mascote">
        @endif
        <div class="pm-fs-time" id="pmFsTime">25:00</div>
        <div class="pm-fs-state" id="pmFsState">{{ __('pomodoro.state.idle') }}</div>
        <div class="pm-fs-subject" id="pmFsSubject"></div>
        <div class="pm-fs-controls">
            <button type="button" class="pm-btn pm-btn--ghost" id="pmFsPauseBtn">{{ __('pomodoro.form.pause') }}</button>
            <button type="button" class="pm-btn pm-btn--ghost" id="pmFsSkipBtn">{{ __('pomodoro.form.skip') }}</button>
            <button type="button" class="pm-btn pm-btn--ghost" id="pmFsExitBtn">{{ __('pomodoro.fullscreen.exit') }}</button>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var materiaSelect = document.getElementById('pmMateria');
    if (!materiaSelect) return;

    var pmChartInstance = null;
    var chartEl = document.getElementById('pmChart');
    if (chartEl && window.Chart) {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var tickColor = isDark ? '#9ca3af' : '#6b7280';
        var gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        pmChartInstance = new Chart(chartEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($ultimosDias['labels'] ?? []),
                datasets: [{
                    label: @json(__('pomodoro.stats.chart_title')),
                    data: @json($ultimosDias['minutos'] ?? []),
                    backgroundColor: '#8b1fb8',
                    borderRadius: 4,
                    maxBarThickness: 22
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: tickColor, precision: 0 }, grid: isDark ? { color: gridColor } : { display: false } },
                    x: { ticks: { color: tickColor }, grid: { display: false } }
                }
            }
        });
    }

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

    var fsOverlay = document.getElementById('pmFsOverlay');
    var fsTimeEl = document.getElementById('pmFsTime');
    var fsStateEl = document.getElementById('pmFsState');
    var fsSubjectEl = document.getElementById('pmFsSubject');
    var fsPauseBtn = document.getElementById('pmFsPauseBtn');
    var fsSkipBtn = document.getElementById('pmFsSkipBtn');
    var fsExitBtn = document.getElementById('pmFsExitBtn');
    var fullscreenBtn = document.getElementById('pmFullscreenBtn');

    var ambientButtons = document.querySelectorAll('#pmAmbientOptions [data-pm-ambient]');
    var ambientVolumeInput = document.getElementById('pmAmbientVolume');

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
        noData: @json(__('pomodoro.stats.no_data')),
        justNow: @json(__('pomodoro.stats.just_now')),
        sessionSummary: {!! json_encode(__('pomodoro.stats.session_summary', ['materia' => '__MATERIA__', 'ciclos' => '__CICLOS__', 'min' => '__MIN__'])) !!},
    };

    var RADIUS = 100;
    var CIRC = 2 * Math.PI * RADIUS;
    ringFg.style.strokeDasharray = CIRC;
    ringFg.style.strokeDashoffset = 0;

    var focusMin = 25, breakMin = 5;

    function updateStepperVal(el, delta, min, max) {
        var current = parseInt(el.textContent, 10);
        var next = Math.max(min, Math.min(max, current + delta));
        el.textContent = next;
        return next;
    }

    function renderIdlePreview() {
        var label = (focusMin < 10 ? '0' : '') + focusMin + ':00';
        timeEl.textContent = label;
        ringFg.style.strokeDashoffset = 0;
        stateEl.textContent = LABELS.idle;
        ring.classList.remove('is-break');
        cycleCountEl.textContent = '';
        if (fsTimeEl) { fsTimeEl.textContent = label; fsStateEl.textContent = LABELS.idle; fsSubjectEl.textContent = ''; }
    }

    focusStepper.querySelectorAll('[data-pm-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            focusMin = updateStepperVal(focusVal, parseInt(btn.dataset.pmStep, 10), 1, 90);
            if (window.PomodoroEngine.getState().mode === 'idle') renderIdlePreview();
        });
    });
    breakStepper.querySelectorAll('[data-pm-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            breakMin = updateStepperVal(breakVal, parseInt(btn.dataset.pmStep, 10), 1, 30);
        });
    });

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function renderPorMateria(lista) {
        var el = document.getElementById('pmPorMateria');
        if (!el) return;
        if (!lista || !lista.length) {
            el.innerHTML = '<p class="pm-empty">' + escapeHtml(LABELS.noData) + '</p>';
            return;
        }
        el.innerHTML = lista.map(function (pm) {
            return '<div class="pm-subject-row"><span class="pm-subject-row__name">' + escapeHtml(pm.materia_nome) +
                '</span><span class="pm-subject-row__time">' + LABELS.todayMinutes.replace('__N__', pm.total_minutos) + '</span></div>';
        }).join('');
    }

    function renderSessoesRecentes(lista) {
        var el = document.getElementById('pmSessoesRecentes');
        if (!el) return;
        if (!lista || !lista.length) {
            el.innerHTML = '<p class="pm-empty">' + escapeHtml(LABELS.noData) + '</p>';
            return;
        }
        el.innerHTML = lista.map(function (s) {
            var resumo = LABELS.sessionSummary
                .replace('__MATERIA__', escapeHtml(s.materia_nome))
                .replace('__CICLOS__', s.total_ciclos)
                .replace('__MIN__', s.total_minutos);
            return '<div class="pm-session-row"><span>' + resumo + '</span><span class="pm-session-row__meta">' + escapeHtml(LABELS.justNow) + '</span></div>';
        }).join('');
    }

    function updateChart(ultimosDias) {
        if (!pmChartInstance || !ultimosDias) return;
        pmChartInstance.data.labels = ultimosDias.labels;
        pmChartInstance.data.datasets[0].data = ultimosDias.minutos;
        pmChartInstance.update();
    }

    window.addEventListener('pomodoro:cycle-logged', function (e) {
        var data = e.detail;
        if (!data) return;
        if (data.hoje) {
            document.getElementById('pmTodayMinutes').textContent = LABELS.todayMinutes.replace('__N__', data.hoje.minutos);
            document.getElementById('pmTodayCycles').textContent = LABELS.todayCycles.replace('__N__', data.hoje.ciclos);
        }
        if (typeof data.streak === 'number') {
            document.getElementById('pmStreak').textContent = LABELS.streakDays.replace('__N__', data.streak);
        }
        renderPorMateria(data.porMateria);
        renderSessoesRecentes(data.sessoesRecentes);
        updateChart(data.ultimosDias);
    });

    function renderFromEngine(st) {
        var active = st.mode !== 'idle';

        if (!active) {
            renderIdlePreview();
        } else {
            var m = Math.floor(st.remainingSec / 60);
            var s = st.remainingSec % 60;
            var timeStr = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            var frac = st.totalSec > 0 ? st.remainingSec / st.totalSec : 0;
            timeEl.textContent = timeStr;
            ringFg.style.strokeDashoffset = CIRC * frac;
            stateEl.textContent = st.mode === 'focus' ? LABELS.focus : LABELS.brk;
            ring.classList.toggle('is-break', st.mode === 'break');
            cycleCountEl.textContent = st.cycles > 0 ? LABELS.cycle.replace('__N__', st.cycles) : '';
            if (fsTimeEl) { fsTimeEl.textContent = timeStr; fsStateEl.textContent = stateEl.textContent; fsSubjectEl.textContent = st.materiaNome || ''; }
            focusMin = st.focusMin; breakMin = st.breakMin;
            focusVal.textContent = focusMin; breakVal.textContent = breakMin;
        }

        summaryEl.classList.add('d-none');
        controlsEl.classList.remove('d-none');
        startBtn.classList.toggle('d-none', active);
        pauseBtn.classList.toggle('d-none', !active);
        skipBtn.classList.toggle('d-none', !active);
        finishBtn.classList.toggle('d-none', !active);
        focusStepper.classList.toggle('is-disabled', active);
        breakStepper.classList.toggle('is-disabled', active);
        pauseBtn.textContent = st.running ? LABELS.pause : LABELS.resume;
        if (fsPauseBtn) fsPauseBtn.textContent = pauseBtn.textContent;

        if (active && st.materiaId && String(materiaSelect.value) !== String(st.materiaId)) {
            materiaSelect.value = st.materiaId;
            materiaSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    window.addEventListener('pomodoro:update', function (e) { renderFromEngine(e.detail); });

    startBtn.addEventListener('click', function () {
        if (!materiaSelect.value) {
            errorEl.classList.add('is-visible');
            return;
        }
        errorEl.classList.remove('is-visible');
        var materiaNome = materiaSelect.options[materiaSelect.selectedIndex]
            ? materiaSelect.options[materiaSelect.selectedIndex].textContent
            : '';
        window.PomodoroEngine.start({
            materiaId: materiaSelect.value,
            materiaNome: materiaNome,
            focusMin: focusMin,
            breakMin: breakMin
        });
    });

    pauseBtn.addEventListener('click', function () {
        var st = window.PomodoroEngine.getState();
        if (st.running) { window.PomodoroEngine.pause(); } else { window.PomodoroEngine.resume(); }
    });

    skipBtn.addEventListener('click', function () { window.PomodoroEngine.skip(); });

    finishBtn.addEventListener('click', function () {
        var result = window.PomodoroEngine.finish();
        summaryBodyEl.textContent = result.cycles > 0
            ? LABELS.summaryBody.replace('__C__', result.cycles).replace('__M__', result.totalMin)
            : LABELS.summaryEmpty;
        controlsEl.classList.add('d-none');
        summaryEl.classList.remove('d-none');
    });

    summaryOkBtn.addEventListener('click', function () {
        summaryEl.classList.add('d-none');
        controlsEl.classList.remove('d-none');
        startBtn.classList.remove('d-none');
        pauseBtn.classList.add('d-none');
        skipBtn.classList.add('d-none');
        finishBtn.classList.add('d-none');
        focusStepper.classList.remove('is-disabled');
        breakStepper.classList.remove('is-disabled');
        renderIdlePreview();
    });

    // --- som ambiente ---
    function syncAmbientUi(st) {
        var kind = st.ambient || 'none';
        ambientButtons.forEach(function (b) { b.classList.toggle('is-active', b.dataset.pmAmbient === kind); });
        ambientVolumeInput.value = typeof st.ambientVolume === 'number' ? st.ambientVolume : 0.4;
    }

    ambientButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            ambientButtons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            window.PomodoroEngine.setAmbient(btn.dataset.pmAmbient);
        });
    });
    ambientVolumeInput.addEventListener('input', function () {
        window.PomodoroEngine.setAmbientVolume(parseFloat(ambientVolumeInput.value));
    });

    // --- tela cheia ---
    fullscreenBtn.addEventListener('click', function () {
        fsOverlay.classList.remove('d-none');
        var req = fsOverlay.requestFullscreen || fsOverlay.webkitRequestFullscreen;
        if (req) req.call(fsOverlay);
    });

    function exitFullscreen() {
        if (document.fullscreenElement || document.webkitFullscreenElement) {
            var exit = document.exitFullscreen || document.webkitExitFullscreen;
            if (exit) exit.call(document);
        }
        fsOverlay.classList.add('d-none');
    }

    fsExitBtn.addEventListener('click', exitFullscreen);
    document.addEventListener('fullscreenchange', function () { if (!document.fullscreenElement) fsOverlay.classList.add('d-none'); });
    document.addEventListener('webkitfullscreenchange', function () { if (!document.webkitFullscreenElement) fsOverlay.classList.add('d-none'); });

    fsPauseBtn.addEventListener('click', function () {
        var st = window.PomodoroEngine.getState();
        if (st.running) { window.PomodoroEngine.pause(); } else { window.PomodoroEngine.resume(); }
    });
    fsSkipBtn.addEventListener('click', function () { window.PomodoroEngine.skip(); });

    // --- estado inicial: retoma sessao ja em andamento (localStorage), se houver ---
    var initialState = window.PomodoroEngine.getState();
    syncAmbientUi(initialState);
    renderFromEngine(initialState);
})();
</script>
@endpush
