@extends('layouts.app')

@section('title', __('dashboard.title'))
@section('mobile_title', trim(explode('|', __('dashboard.title'))[0]))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<style>
@keyframes fadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
@keyframes barGrow { from { transform: scaleY(0); } to { transform: scaleY(1); } }
@keyframes countUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

.dash-home2 { font-family: 'Inter', system-ui, sans-serif; }
.dash-home2 h1, .dash-home2 h2, .dash-home2 h3 { font-family: 'Poppins', system-ui, sans-serif; }
.dash-home2 .material-symbols-outlined { font-family: 'Material Symbols Outlined'; }

.dash-home2-card {
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.06);
    overflow: hidden;
}
[data-theme="dark"] .dash-home2-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.3); }

.chart-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 6px; cursor: pointer; min-width: 0; }
.chart-bar-wrap:hover .chart-bar { filter: brightness(1.18); }
.chart-bar { border-radius: 5px 5px 2px 2px; background: linear-gradient(180deg, rgba(192,132,252,.75), rgba(106,3,146,.25)); width: 100%; transform-origin: bottom; animation: barGrow .6s ease both; }
.chart-bar.today { background: linear-gradient(180deg, #c084fc, #8b1fb8); box-shadow: 0 4px 14px rgba(139,31,184,.3); }
.chart-label { font-size: .65rem; color: var(--app-muted); font-weight: 500; white-space: nowrap; }

.bc-activity {
    display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    text-decoration: none; color: inherit; transition: border-color .18s ease, box-shadow .18s ease;
}
.bc-activity:hover { border-color: rgba(106,3,146,.4); box-shadow: 0 4px 14px rgba(0,0,0,.12); }
[data-theme="dark"] .bc-activity { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); }

.bc-quick {
    display: flex; flex-direction: column; gap: 8px; padding: 20px; border-radius: 14px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    text-decoration: none; color: inherit; transition: transform .28s cubic-bezier(.2,.9,.2,1), border-color .2s ease, box-shadow .2s ease;
}
.bc-quick:hover { transform: translateY(-3px); border-color: rgba(106,3,146,.4); box-shadow: 0 12px 30px rgba(0,0,0,.18); }
[data-theme="dark"] .bc-quick { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); }
</style>
@endpush

@section('content')
@php
    $nomeCompleto = trim((string) ($usuario->nome ?? ''));
    $partes = $nomeCompleto !== '' ? preg_split('/\s+/u', $nomeCompleto, -1, PREG_SPLIT_NO_EMPTY) : [];
    $primeiroNome = $partes[0] ?? '';
    $evoLabels = $evolucao['labels'] ?? [];
    $evoData = $evolucao['data'] ?? [];
    $evoCount = count($evoData);
    $maxEvo = $evoCount > 0 ? max($evoData) : 1.0;
    if ($maxEvo <= 0) {
        $maxEvo = 1.0;
    }
    $lastEvoIndex = $evoCount > 0 ? $evoCount - 1 : -1;
    $aproveitamentoPct = min(100, max(0, (float) ($stats['aproveitamento_geral'] ?? 0)));
    $melhorMateria = $stats['melhor_materia'] ?? 'N/A';
    $tipMateria = $melhorMateria !== '' && $melhorMateria !== 'N/A';
    $fmtCount = static fn (int $n): string => number_format($n, 0, ',', '.');
    $questoes = (int) ($stats['questoes_respondidas'] ?? 0);
    $studyHoursEst = min(999, max(0, (int) round($questoes * 1.5 / 60)));
    $streakDays = (int) ($stats['sequencia_dias'] ?? 0);
    $hasRecent = !empty($recentes[0]);
@endphp

<div class="dash-home2" style="display: flex; flex-direction: column; gap: 24px;">

    {{-- Page header --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; animation: fadeUp .6s ease both;">
        <div>
            <h1 style="font-size:clamp(1.4rem,2.2vw,1.7rem); font-weight:700; color:var(--app-text); letter-spacing:-.025em; margin-bottom:5px;">
                @if ($primeiroNome !== '')
                    {{ __('dashboard.home.welcome_title', ['name' => $primeiroNome]) }}
                @else
                    {{ __('dashboard.home.welcome_fallback') }}
                @endif
            </h1>
            <p style="color:var(--app-muted); font-size:.9rem;">{!! __('dashboard.home.monthly_lead', ['pct' => '<strong style="color:#a855f7;">'.(int) round($aproveitamentoPct).'</strong>']) !!}</p>
        </div>
        <div class="dash-home2-card" style="display:flex; align-items:center; gap:10px; border-radius:14px; padding:10px 16px;" aria-live="polite">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.5rem; line-height:1; color:#f97316;">local_fire_department</span>
            <div>
                <div style="font-size:.7rem; color:var(--app-muted); font-weight:600; text-transform:uppercase; letter-spacing:.08em;">{{ __('dashboard.home.streak_caption') }}</div>
                <div style="font-family:'Poppins',sans-serif; font-weight:700; font-size:1rem; color:var(--app-text);">{{ __('dashboard.home.streak_value', ['days' => $streakDays]) }}</div>
            </div>
        </div>
    </div>

    {{-- Top row: Hero card + Chart --}}
    <div style="display:grid; grid-template-columns:1.35fr 1fr; gap:20px; animation: fadeUp .6s .06s ease both;">

        @if ($hasRecent)
            <a href="{{ route('history') }}" style="display:flex; flex-direction:column; justify-content:space-between; background:linear-gradient(135deg, #18002e 0%, #2d0050 60%, #0d0a1a 100%); border-radius:18px; padding:clamp(20px,3vw,28px); text-decoration:none; position:relative; overflow:hidden; min-height:168px; transition:filter .2s ease;">
                <div style="position:absolute; inset:0; background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px); background-size:24px 24px; pointer-events:none;"></div>
                <div style="position:absolute; top:-20%; right:-5%; width:55%; height:70%; background:radial-gradient(ellipse at 60% 30%, rgba(139,31,184,.45), transparent 65%); pointer-events:none;"></div>
                <div style="position:relative; z-index:1; margin-bottom:14px;">
                    <span style="display:inline-block; padding:4px 12px; border-radius:999px; border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.07); color:rgba(255,255,255,.65); font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; backdrop-filter:blur(8px);">{{ __('dashboard.home.hero_tag') }}</span>
                </div>
                <div style="position:relative; z-index:1;">
                    <h2 style="font-size:clamp(1.1rem,1.8vw,1.3rem); font-weight:700; color:#fff; margin-bottom:8px; letter-spacing:-.02em;">{{ __('dashboard.home.hero_sim_title', ['materia' => $recentes[0]['categoria']]) }}</h2>
                    <p style="color:rgba(255,255,255,.5); font-size:.84rem; margin-bottom:16px;">{{ __('dashboard.home.hero_sim_sub', ['pont' => $recentes[0]['pontuacao'], 'data' => $recentes[0]['data']]) }}</p>
                    <span style="display:inline-flex; align-items:center; gap:7px; color:#fff; font-size:.84rem; font-weight:600;">
                        {{ __('dashboard.home.btn_continue') }}
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <svg style="position:absolute; right:20px; bottom:14px; color:rgba(255,255,255,.05);" width="80" height="80" viewBox="0 0 24 24" fill="currentColor"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </a>
        @else
            <a href="{{ route('questionbank') }}" style="display:flex; flex-direction:column; justify-content:space-between; background:linear-gradient(135deg, #18002e 0%, #2d0050 60%, #0d0a1a 100%); border-radius:18px; padding:clamp(20px,3vw,28px); text-decoration:none; position:relative; overflow:hidden; min-height:168px; transition:filter .2s ease;">
                <div style="position:absolute; inset:0; background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px); background-size:24px 24px; pointer-events:none;"></div>
                <div style="position:absolute; top:-20%; right:-5%; width:55%; height:70%; background:radial-gradient(ellipse at 60% 30%, rgba(139,31,184,.45), transparent 65%); pointer-events:none;"></div>
                <div style="position:relative; z-index:1; margin-bottom:14px;">
                    <span style="display:inline-block; padding:4px 12px; border-radius:999px; border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.07); color:rgba(255,255,255,.65); font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; backdrop-filter:blur(8px);">{{ __('dashboard.home.hero_tag') }}</span>
                </div>
                <div style="position:relative; z-index:1;">
                    <h2 style="font-size:clamp(1.1rem,1.8vw,1.3rem); font-weight:700; color:#fff; margin-bottom:8px; letter-spacing:-.02em;">{{ __('dashboard.home.hero_empty_title') }}</h2>
                    <p style="color:rgba(255,255,255,.5); font-size:.84rem; margin-bottom:16px;">{{ __('dashboard.home.hero_empty_sub') }}</p>
                    <span style="display:inline-flex; align-items:center; gap:7px; color:#fff; font-size:.84rem; font-weight:600;">
                        {{ __('dashboard.home.btn_open_bank') }}
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <svg style="position:absolute; right:20px; bottom:14px; color:rgba(255,255,255,.05);" width="80" height="80" viewBox="0 0 24 24" fill="currentColor"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </a>
        @endif

        <div class="dash-home2-card" style="border-radius:18px; padding:20px 22px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                <div>
                    <h3 style="font-size:.95rem; font-weight:700; color:var(--app-text); margin-bottom:3px;">{{ __('dashboard.panel.chart_weekly_title') }}</h3>
                    <p style="font-size:.75rem; color:var(--app-muted);">{{ __('dashboard.panel.chart_compare') }}</p>
                </div>
                <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:999px; background:rgba(106,3,146,.12); border:1px solid rgba(106,3,146,.25); font-size:.7rem; font-weight:700; color:#a855f7;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#a855f7; display:inline-block;"></span>
                    {{ __('dashboard.panel.you_badge', ['value' => $stats['aproveitamento_geral']]) }}
                </span>
            </div>
            @if ($evoCount > 0)
                <div style="display:flex; align-items:stretch; gap:6px; flex:1; padding-top:12px; height:90px;" role="img" aria-label="{{ __('dashboard.panel.chart_weekly_title') }}">
                    @foreach ($evoData as $i => $valor)
                        @php
                            $h = (float) $valor / $maxEvo * 100;
                            $h = max($valor > 0 ? 12.0 : 6.0, min(100.0, $h));
                            $isToday = $i === $lastEvoIndex;
                        @endphp
                        <div class="chart-bar-wrap" title="{{ __('dashboard.panel.chart_value', ['pct' => (int) round($valor), 'date' => $evoLabels[$i] ?? '']) }}">
                            <div class="chart-bar {{ $isToday ? 'today' : '' }}" style="height:{{ $h }}%; animation-delay: {{ $i * 0.05 }}s;"></div>
                            <span class="chart-label" style="{{ $isToday ? 'color:#a855f7; font-weight:700;' : '' }}">{{ $evoLabels[$i] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size:.82rem; color:var(--app-muted); margin-top:12px;">{{ __('dashboard.panel.chart_empty') }}</p>
            @endif
        </div>
    </div>

    {{-- Mini stats + Activities row --}}
    <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:20px; animation: fadeUp .6s .12s ease both;">

        <div style="display:flex; flex-direction:column; gap:14px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="dash-home2-card" style="border-radius:14px; padding:16px;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:rgba(106,3,146,.14); margin-bottom:10px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.4rem; color:var(--app-text); line-height:1; animation: countUp .5s .2s ease both;">{{ $fmtCount($questoes) }}</div>
                    <div style="font-size:.75rem; color:var(--app-muted); margin-top:4px;">{{ __('dashboard.home.mini_q') }}</div>
                </div>
                <div class="dash-home2-card" style="border-radius:14px; padding:16px;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:rgba(106,3,146,.14); margin-bottom:10px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    <div style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.4rem; color:var(--app-text); line-height:1; animation: countUp .5s .25s ease both;">{{ __('dashboard.home.mini_t_value', ['h' => $studyHoursEst]) }}</div>
                    <div style="font-size:.75rem; color:var(--app-muted); margin-top:4px;">{{ __('dashboard.home.mini_t') }}</div>
                </div>
            </div>

            <div class="dash-home2-card" style="border-radius:14px; padding:18px; flex:1;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.2rem; color:#a855f7;">military_tech</span>
                    <h3 style="font-size:.88rem; font-weight:700; color:var(--app-text);">{{ __('dashboard.home.ranking_title') }}</h3>
                </div>
                <p style="font-size:.8rem; color:var(--app-muted); margin-bottom:14px; line-height:1.5;">{{ __('dashboard.home.ranking_copy') }}</p>
                <div style="height:8px; background:var(--app-border); border-radius:99px; overflow:hidden; margin-bottom:8px;">
                    <div style="height:100%; width:{{ $aproveitamentoPct }}%; background:linear-gradient(90deg, #8b1fb8, #c084fc); border-radius:99px; transition:width .8s cubic-bezier(.2,.9,.2,1);"></div>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="font-size:.72rem; color:var(--app-muted);">{{ __('dashboard.home.ranking_meter_low') }}</span>
                    <span style="font-size:.78rem; font-weight:700; color:#a855f7;">{{ __('dashboard.stat.overall') }}: {{ $stats['aproveitamento_geral'] }}%</span>
                    <span style="font-size:.72rem; color:var(--app-muted);">{{ __('dashboard.home.ranking_meter_high') }}</span>
                </div>
            </div>

            <div style="background:rgba(106,3,146,.08); border:1px solid rgba(106,3,146,.22); border-radius:14px; padding:14px 16px;">
                <strong style="display:flex; align-items:center; gap:8px; font-size:.85rem; color:var(--app-text);">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.05rem; color:#a855f7;">tips_and_updates</span>
                    {{ __('dashboard.panel.tip_title') }}
                </strong>
                <p style="font-size:.82rem; color:var(--app-muted); line-height:1.6; margin:6px 0 0;">
                    @if ($tipMateria)
                        {{ __('dashboard.panel.tip_body_materia', ['materia' => $melhorMateria]) }}
                    @else
                        {{ __('dashboard.panel.tip_body') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="dash-home2-card" style="border-radius:18px; padding:22px; display:flex; flex-direction:column;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h3 style="font-size:.95rem; font-weight:700; color:var(--app-text); display:flex; align-items:center; gap:7px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><path d="M3 3h6l2 9h7l2-7H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('dashboard.recent.title') }}
                </h3>
                <a href="{{ route('history') }}" style="font-size:.78rem; font-weight:700; color:#a855f7; text-decoration:none;">{{ __('dashboard.recent.see_all') }}</a>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; flex:1;">
                @forelse ($recentes as $sim)
                    @php
                        $xpGain = 0;
                        if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', (string) ($sim['pontuacao'] ?? ''), $m)) {
                            $ac = (int) $m[1];
                            $tot = max(1, (int) $m[2]);
                            $xpGain = (int) max(15, min(500, round($ac * 12 + ($ac / $tot) * 80)));
                        }
                    @endphp
                    <a href="{{ route('history') }}" class="bc-activity">
                        <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                            <div style="width:38px; height:38px; border-radius:11px; background:rgba(106,3,146,.14); display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" stroke="currentColor" stroke-width="1.8"/></svg>
                            </div>
                            <div style="min-width:0;">
                                <div style="font-size:.87rem; font-weight:600; color:var(--app-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $sim['categoria'] }}</div>
                                <div style="font-size:.75rem; color:var(--app-muted);">{{ $sim['data'] }}</div>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-size:.72rem; font-weight:700; background:rgba(106,3,146,.16); color:#a855f7; padding:2px 8px; border-radius:999px; margin-bottom:3px;">{{ __('dashboard.home.xp_badge', ['n' => $xpGain]) }}</div>
                            <div style="font-size:.78rem; font-weight:700; color:var(--app-text);">{{ $sim['pontuacao'] }}</div>
                        </div>
                    </a>
                @empty
                    <p style="color:var(--app-muted); font-size:.85rem;">{{ __('dashboard.recent.empty') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div style="animation: fadeUp .6s .18s ease both;">
        <h3 style="font-size:.82rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:12px;">{{ __('dashboard.home.quick_actions_title') }}</h3>
        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px;">
            <a class="bc-quick" href="{{ route('questionbank') }}">
                <div style="width:40px; height:40px; border-radius:12px; background:rgba(106,3,146,.14); display:inline-flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </div>
                <div style="font-size:.9rem; font-weight:700; color:var(--app-text);">{{ __('dashboard.home.quick_1_title') }}</div>
                <p style="font-size:.78rem; color:var(--app-muted); line-height:1.5; margin:0;">{{ __('dashboard.home.quick_1_desc') }}</p>
            </a>
            <a class="bc-quick" href="{{ route('profile.show') }}">
                <div style="width:40px; height:40px; border-radius:12px; background:rgba(106,3,146,.14); display:inline-flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="color:#a855f7;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                </div>
                <div style="font-size:.9rem; font-weight:700; color:var(--app-text);">{{ __('dashboard.home.quick_3_title') }}</div>
                <p style="font-size:.78rem; color:var(--app-muted); line-height:1.5; margin:0;">{{ __('dashboard.home.quick_3_desc') }}</p>
            </a>
        </div>
    </div>

</div>
@endsection
