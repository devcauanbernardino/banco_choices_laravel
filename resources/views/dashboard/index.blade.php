@extends('layouts.app')

@section('title', __('dashboard.title'))
@section('mobile_title', trim(explode('|', __('dashboard.title'))[0]))

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-painel.css') }}">
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

    <div class="dash-painel dash-painel--home">
        {{-- Saudação + meta (print) + racha + tema --}}
        <header class="dash-home-head">
            <div class="dash-home-head-main">
                <h2 class="dash-home-title">
                    @if ($primeiroNome !== '')
                        {{ __('dashboard.home.welcome_title', ['name' => $primeiroNome]) }}
                    @else
                        {{ __('dashboard.home.welcome_fallback') }}
                    @endif
                </h2>
                <p class="dash-home-lead">{{ __('dashboard.home.monthly_lead', ['pct' => (int) round($aproveitamentoPct)]) }}</p>
                <p class="dash-home-sub mb-0">{{ __('dashboard.greeting_sub') }}</p>
                <p class="dash-home-buy mt-2 mb-0">
                    <a href="{{ route('addon.materias') }}" class="link-primary fw-semibold text-decoration-none">{{ __('dashboard.buy_more_cta') }}</a>
                </p>
            </div>
            <div class="dash-home-head-aside">
                <div class="dash-home-streak" aria-live="polite">
                    <span class="material-symbols-outlined dash-home-streak-ico" aria-hidden="true">local_fire_department</span>
                    <div>
                        <div class="dash-home-streak-label">{{ __('dashboard.home.streak_caption') }}</div>
                        <div class="dash-home-streak-value">{{ __('dashboard.home.streak_value', ['days' => $streakDays]) }}</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="row g-4 mb-3 align-items-stretch">
            <div class="col-lg-7">
                @if ($hasRecent)
                    <a href="{{ route('history') }}" class="dash-home-mega text-decoration-none">
                        <span class="dash-home-mega-tag">{{ __('dashboard.home.hero_tag') }}</span>
                        <h3 class="dash-home-mega-title">{{ __('dashboard.home.hero_sim_title', ['materia' => $recentes[0]['categoria']]) }}</h3>
                        <p class="dash-home-mega-desc">{{ __('dashboard.home.hero_sim_sub', ['pont' => $recentes[0]['pontuacao'], 'data' => $recentes[0]['data']]) }}</p>
                        <span class="dash-home-mega-btn">
                            {{ __('dashboard.home.btn_continue') }}
                            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                        </span>
                        <span class="material-symbols-outlined dash-home-mega-bg" aria-hidden="true">clinical_notes</span>
                    </a>
                @else
                    <a href="{{ route('questionbank') }}" class="dash-home-mega text-decoration-none">
                        <span class="dash-home-mega-tag">{{ __('dashboard.home.hero_tag') }}</span>
                        <h3 class="dash-home-mega-title">{{ __('dashboard.home.hero_empty_title') }}</h3>
                        <p class="dash-home-mega-desc">{{ __('dashboard.home.hero_empty_sub') }}</p>
                        <span class="dash-home-mega-btn">
                            {{ __('dashboard.home.btn_open_bank') }}
                            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                        </span>
                        <span class="material-symbols-outlined dash-home-mega-bg" aria-hidden="true">quiz</span>
                    </a>
                @endif
            </div>
            <div class="col-lg-5">
                <div class="dash-painel-chart-card dash-home-chart h-100 d-flex flex-column">
                    <div class="dash-painel-chart-head">
                        <div>
                            <h3>{{ __('dashboard.panel.chart_weekly_title') }}</h3>
                            <p class="small mb-0">{{ __('dashboard.panel.chart_compare') }}</p>
                        </div>
                        <span class="dash-painel-chart-badge">
                            <span class="dash-painel-chart-badge-dot" aria-hidden="true"></span>
                            {{ __('dashboard.panel.you_badge', ['value' => $stats['aproveitamento_geral']]) }}
                        </span>
                    </div>
                    @if ($evoCount > 0)
                        <div class="dash-painel-chart-bars flex-grow-1 mt-auto" role="img" aria-label="{{ __('dashboard.panel.chart_weekly_title') }}">
                            @foreach ($evoData as $i => $valor)
                                @php
                                    $h = (float) $valor / $maxEvo * 100;
                                    $h = max($valor > 0 ? 12.0 : 6.0, min(100.0, $h));
                                @endphp
                                <div class="dash-painel-chart-bar-wrap {{ $i === $lastEvoIndex ? 'is-today' : '' }}">
                                    <div class="dash-painel-chart-bar" style="height: {{ $h }}%"></div>
                                    <span class="dash-painel-chart-label">{{ $evoLabels[$i] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="dash-painel-chart-empty mb-0 mt-2">{{ __('dashboard.panel.chart_empty') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7 d-flex">
                <div class="dash-home-activities w-100 d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h3 class="dash-painel-section-title mb-0">
                            <span class="material-symbols-outlined" aria-hidden="true">history</span>
                            {{ __('dashboard.recent.title') }}
                        </h3>
                        <a href="{{ route('history') }}" class="small fw-bold text-decoration-none">{{ __('dashboard.recent.see_all') }}</a>
                    </div>
                    @forelse ($recentes as $sim)
                        @php
                            $xpGain = 0;
                            if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', (string) ($sim['pontuacao'] ?? ''), $m)) {
                                $ac = (int) $m[1];
                                $tot = max(1, (int) $m[2]);
                                $xpGain = (int) max(15, min(500, round($ac * 12 + ($ac / $tot) * 80)));
                            }
                        @endphp
                        <a href="{{ route('history') }}" class="dash-home-activity">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <div class="dash-home-activity-ico" aria-hidden="true">
                                    <span class="material-symbols-outlined">school</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="dash-home-activity-title text-truncate">{{ $sim['categoria'] }}</div>
                                    <div class="dash-home-activity-meta">{{ __('dashboard.table.date') }}: {{ $sim['data'] }}</div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="dash-home-activity-xp">{{ __('dashboard.home.xp_badge', ['n' => $xpGain]) }}</div>
                                <div class="dash-home-activity-score">{{ $sim['pontuacao'] }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted mb-0">{{ __('dashboard.recent.empty') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-5 d-flex flex-column">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="dash-home-mini">
                            <span class="material-symbols-outlined dash-home-mini-ico" aria-hidden="true">assignment_turned_in</span>
                            <div class="dash-home-mini-value">{{ $fmtCount($questoes) }}</div>
                            <div class="dash-home-mini-label">{{ __('dashboard.home.mini_q') }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dash-home-mini">
                            <span class="material-symbols-outlined dash-home-mini-ico" aria-hidden="true">schedule</span>
                            <div class="dash-home-mini-value">{{ __('dashboard.home.mini_t_value', ['h' => $studyHoursEst]) }}</div>
                            <div class="dash-home-mini-label">{{ __('dashboard.home.mini_t') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="dash-home-rank">
                            <div class="dash-home-rank-head">
                                <span class="material-symbols-outlined" aria-hidden="true">military_tech</span>
                                {{ __('dashboard.home.ranking_title') }}
                            </div>
                            <p class="dash-home-rank-copy mb-3">{{ __('dashboard.home.ranking_copy') }}</p>
                            <div class="dash-home-rank-meter">
                                <div class="dash-home-rank-meter-fill" style="width: {{ $aproveitamentoPct }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small mt-2 mb-0 dash-home-rank-scale">
                                <span>{{ __('dashboard.home.ranking_meter_low') }}</span>
                                <span class="fw-bold">{{ __('dashboard.stat.overall') }}: {{ $stats['aproveitamento_geral'] }}%</span>
                                <span>{{ __('dashboard.home.ranking_meter_high') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dash-painel-tip mt-3">
                    <strong>
                        <span class="material-symbols-outlined dash-home-tip-ico" aria-hidden="true">tips_and_updates</span>
                        {{ __('dashboard.panel.tip_title') }}
                    </strong>
                    <p class="mb-0">
                        @if ($tipMateria)
                            {{ __('dashboard.panel.tip_body_materia', ['materia' => $melhorMateria]) }}
                        @else
                            {{ __('dashboard.panel.tip_body') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4 mt-3">
            <div class="col-md-4">
                <a href="{{ route('questionbank') }}" class="dash-home-quick">
                    <span class="material-symbols-outlined dash-home-quick-ico" aria-hidden="true">psychology</span>
                    <div class="dash-home-quick-title">{{ __('dashboard.home.quick_1_title') }}</div>
                    <p class="dash-home-quick-desc mb-0">{{ __('dashboard.home.quick_1_desc') }}</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('home') }}" class="dash-home-quick">
                    <span class="material-symbols-outlined dash-home-quick-ico" aria-hidden="true">public</span>
                    <div class="dash-home-quick-title">{{ __('dashboard.home.quick_2_title') }}</div>
                    <p class="dash-home-quick-desc mb-0">{{ __('dashboard.home.quick_2_desc') }}</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('profile.show') }}" class="dash-home-quick">
                    <span class="material-symbols-outlined dash-home-quick-ico" aria-hidden="true">person</span>
                    <div class="dash-home-quick-title">{{ __('dashboard.home.quick_3_title') }}</div>
                    <p class="dash-home-quick-desc mb-0">{{ __('dashboard.home.quick_3_desc') }}</p>
                </a>
            </div>
        </div>
    </div>
@endsection
