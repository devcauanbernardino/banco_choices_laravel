@extends('layouts.app')

@section('title', __('dashboard.title'))
@section('mobile_title', trim(explode('|', __('dashboard.title'))[0]))

@section('topbar_title', trim(explode('|', __('dashboard.title'))[0]))

@section('content')
    {{-- Greeting --}}
    <div class="mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold mb-1">{{ sprintf(__('dashboard.greeting'), explode(' ', $usuario->nome)[0]) }}</h2>
            <p class="text-muted mb-0">{{ __('dashboard.greeting_sub') }}</p>
            <p class="text-muted small mt-2 mb-0">
                <a href="{{ route('addon.materias') }}" class="text-primary fw-semibold text-decoration-none">{{ __('dashboard.buy_more_cta') }}</a>
            </p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-primary-soft text-primary p-2 px-3 rounded-pill">
                <i class="material-icons fs-6 align-middle me-1">calendar_today</i>
                {{ date('d M, Y') }}
            </span>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card h-100">
                <div class="bc-icon-box bg-success-subtle mb-3">
                    <span class="material-icons text-success">task_alt</span>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($stats['questoes_respondidas'], 0, ',', '.') }}</h3>
                <p class="bc-stat-label mt-1 mb-0">{{ __('dashboard.stat.questions_answered') }}</p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card h-100">
                <div class="bc-icon-box bg-primary-soft mb-3">
                    <span class="material-icons text-primary">insights</span>
                </div>
                <h3 class="fw-bold mb-0">{{ $stats['aproveitamento_geral'] }}%</h3>
                <p class="bc-stat-label mt-1 mb-0">{{ __('dashboard.stat.overall') }}</p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card h-100">
                <div class="bc-icon-box bg-warning-subtle mb-3">
                    <span class="material-icons text-warning">local_fire_department</span>
                </div>
                <h3 class="fw-bold mb-0">{{ $stats['sequencia_dias'] }} {{ __('dashboard.stat.streak_days') }}</h3>
                <p class="bc-stat-label mt-1 mb-0">{{ __('dashboard.stat.streak') }}</p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card h-100">
                <div class="bc-icon-box bg-info-subtle mb-3">
                    <span class="material-icons text-info">emoji_events</span>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($stats['pontuacao_total'], 0, ',', '.') }}</h3>
                <p class="bc-stat-label mt-1 mb-0">{{ __('dashboard.stat.total_score') }}</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Recent Simulados Table --}}
        <div class="col-lg-8">
            <div class="bc-card h-100">
                <div class="bc-card-header">
                    <h6 class="fw-bold mb-0">{{ __('dashboard.recent.title') }}</h6>
                    <a href="{{ route('stats') }}" class="text-primary text-decoration-none small fw-bold">{{ __('dashboard.recent.see_all') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="bc-table w-100">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table.date') }}</th>
                                <th>{{ __('dashboard.table.subject') }}</th>
                                <th>{{ __('dashboard.table.result') }}</th>
                                <th>{{ __('dashboard.table.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentes as $sim)
                                <tr>
                                    <td class="text-muted small">{{ $sim['data'] }}</td>
                                    <td class="fw-bold">{{ $sim['categoria'] }}</td>
                                    <td><span class="fw-bold">{{ $sim['pontuacao'] }}</span></td>
                                    <td>
                                        <span class="bc-badge bc-badge--{{ $sim['classe'] }}">
                                            {{ $sim['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="bc-empty-state">
                                        {{ __('dashboard.recent.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CTA Card --}}
        <div class="col-lg-4">
            <div class="bc-cta-banner text-white h-100">
                <div class="d-flex flex-column justify-content-center text-center p-4 h-100">
                    <div class="mb-3">
                        <span class="material-icons" style="font-size: 48px;">psychology</span>
                    </div>
                    <h4 class="fw-bold mb-2">{{ __('dashboard.cta.title') }}</h4>
                    <p class="opacity-75 small mb-4">{{ __('dashboard.cta.text') }}</p>
                    <a href="{{ route('questionbank') }}"
                        class="btn btn-light btn-lg fw-bold rounded-pill py-3 shadow-sm">
                        {{ __('dashboard.cta.btn') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
