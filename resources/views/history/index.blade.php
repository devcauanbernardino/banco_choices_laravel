@extends('layouts.app')

@section('title', __('simulados.page_title'))
@section('mobile_title', __('simulados.mobile_title'))

@section('topbar_title', __('simulados.mobile_title'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/page-historico-mock.css') }}">
@endpush

@section('content')
    <div class="bc-mock-historico py-4 px-3 px-md-4">
        <header class="bc-mock-historico__hero">
            <div>
                <h1 class="bc-mock-historico__title">{{ __('simulados.heading') }}</h1>
                <p class="bc-mock-historico__lead">
                    {!! __('simulados.subtitle_before') . '<strong>' . (int) $totalSimulados . '</strong>' . __('simulados.subtitle_after') !!}
                </p>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-end gap-3">
                <div class="bc-mock-historico__kpis">
                    <div class="bc-mock-historico__kpi">
                        <span class="bc-mock-historico__kpi-label">{{ __('simulados.kpi_avg_label') }}</span>
                        <span class="bc-mock-historico__kpi-value">{{ $mediaPct }}%</span>
                    </div>
                    <div class="bc-mock-historico__kpi">
                        <span class="bc-mock-historico__kpi-label">{{ __('simulados.kpi_total_label') }}</span>
                        <span class="bc-mock-historico__kpi-value">{{ (int) $totalSimulados }}</span>
                    </div>
                </div>
                <a href="{{ route('questionbank') }}" class="btn btn-primary bc-mock-historico__cta align-self-stretch align-self-sm-center">
                    {{ __('simulados.new') }}
                </a>
            </div>
        </header>

        <form action="{{ route('history') }}" method="GET" class="bc-mock-historico__filters">
            <div class="bc-mock-historico__search-wrap">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="search" name="q" value="{{ $filtroQ ?? '' }}" class="bc-mock-historico__search"
                       placeholder="{{ __('simulados.search_placeholder') }}" autocomplete="off">
            </div>
            <div class="bc-mock-historico__selects">
                <select name="materia" class="bc-mock-historico__select" aria-label="{{ __('simulados.filter_subject') }}">
                    <option value="">{{ __('simulados.all_subjects') }}</option>
                    @foreach ($materias as $m)
                        <option value="{{ $m }}" {{ ($filtroMateria ?? '') === $m ? 'selected' : '' }}>
                            {{ ucfirst($m) }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="bc-mock-historico__select" aria-label="{{ __('simulados.filter_status') }}">
                    <option value="">{{ __('simulados.all_status') }}</option>
                    <option value="aprovado" {{ ($filtroStatus ?? '') === 'aprovado' ? 'selected' : '' }}>{{ __('simulados.status_ok') }}</option>
                    <option value="reprovado" {{ ($filtroStatus ?? '') === 'reprovado' ? 'selected' : '' }}>{{ __('simulados.status_fail') }}</option>
                </select>
                <button type="submit" class="bc-mock-historico__btn-filter">
                    <span class="material-symbols-outlined" aria-hidden="true">filter_alt</span>
                    {{ __('simulados.filter_btn') }}
                </button>
            </div>
        </form>
        @if (!empty($filtroMateria) || !empty($filtroStatus) || !empty($filtroQ))
            <p class="mb-4">
                <a href="{{ route('history') }}" class="text-muted small text-decoration-none">{{ __('simulados.clear_filters') }}</a>
            </p>
        @endif

        <div class="bc-mock-historico__table-wrap">
            <div class="table-responsive">
                <table class="bc-mock-historico__table">
                    <thead>
                        <tr>
                            <th>{{ __('simulados.th_datetime') }}</th>
                            <th>{{ __('simulados.th_subject') }}</th>
                            <th>{{ __('simulados.th_performance') }}</th>
                            <th>{{ __('simulados.th_status') }}</th>
                            <th>{{ __('simulados.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historico as $item)
                            @php
                                $parts = preg_split('/\s+/', (string) $item['data'], 2, PREG_SPLIT_NO_EMPTY);
                                $d = $parts[0] ?? $item['data'];
                                $h = $parts[1] ?? '';
                            @endphp
                            <tr>
                                <td class="bc-mock-historico__cell-date">
                                    <strong>{{ $d }}</strong>
                                    @if ($h !== '')
                                        <small>{{ $h }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="bc-mock-historico__subject-pill">
                                        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                                        {{ $item['materia'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $item['porcentagem'] }}</span>
                                    <span class="text-muted small">({{ $item['pontuacao'] }})</span>
                                </td>
                                <td>
                                    <span class="bc-badge bc-badge--{{ $item['classe'] }}">{{ $item['status'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('simulation.result', ['historico' => $item['id']]) }}" class="bc-btn-action" title="{{ __('simulados.review_aria') }}">
                                        <span class="material-icons fs-5">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="bc-mock-historico__empty">
                                    <span class="material-symbols-outlined d-block" aria-hidden="true">history_toggle_off</span>
                                    <h5 class="fw-bold mb-2">{{ __('simulados.empty_title') }}</h5>
                                    <p class="text-muted mb-0">{{ __('simulados.empty_hint') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
