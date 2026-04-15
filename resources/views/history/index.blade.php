@extends('layouts.app')

@section('title', __('simulados.page_title'))
@section('mobile_title', __('simulados.mobile_title'))

@section('topbar_title', __('simulados.mobile_title'))

@section('content')
    {{-- Header --}}
    <div class="bc-page-header">
        <div>
            <h3 class="fw-bold mb-1">{{ __('simulados.heading') }}</h3>
            <p class="text-muted mb-0">{!! __('simulados.subtitle_before') . '<strong>' . (int) $totalSimulados . '</strong>' . __('simulados.subtitle_after') !!}</p>
        </div>
        <a href="{{ route('questionbank') }}" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm px-4 rounded-pill">
            {{ __('simulados.new') }}
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="bc-filter-card">
        <form action="{{ route('history') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small">{{ __('simulados.filter_subject') }}</label>
                <select name="materia" class="form-select">
                    <option value="">{{ __('simulados.all_subjects') }}</option>
                    @foreach ($materias as $m)
                        <option value="{{ $m }}" {{ $filtroMateria === $m ? 'selected' : '' }}>
                            {{ ucfirst($m) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small">{{ __('simulados.filter_status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('simulados.all_status') }}</option>
                    <option value="aprovado" {{ $filtroStatus === 'aprovado' ? 'selected' : '' }}>{{ __('simulados.status_ok') }}</option>
                    <option value="reprovado" {{ $filtroStatus === 'reprovado' ? 'selected' : '' }}>{{ __('simulados.status_fail') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100 rounded-3">
                    <span class="material-icons align-middle fs-5 me-1">filter_alt</span> {{ __('simulados.filter_btn') }}
                </button>
            </div>
            @if (!empty($filtroMateria) || !empty($filtroStatus))
                <div class="col-md-2">
                    <a href="{{ route('history') }}" class="btn btn-link text-muted w-100 text-decoration-none small">{{ __('simulados.clear_filters') }}</a>
                </div>
            @endif
        </form>
    </div>

    {{-- History table --}}
    <div class="bc-card overflow-hidden">
        <div class="table-responsive">
            <table class="bc-table w-100 mb-0">
                <thead>
                    <tr>
                        <th>{{ __('simulados.th_datetime') }}</th>
                        <th>{{ __('simulados.th_subject') }}</th>
                        <th>{{ __('simulados.th_performance') }}</th>
                        <th>{{ __('simulados.th_status') }}</th>
                        <th class="text-center">{{ __('simulados.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historico as $item)
                        <tr>
                            <td class="fw-bold">{{ $item['data'] }}</td>
                            <td>
                                <span class="badge bg-light text-dark p-2 px-3 rounded-pill border">
                                    {{ $item['materia'] }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold">{{ $item['porcentagem'] }}</span>
                                    <small class="text-muted">({{ $item['pontuacao'] }})</small>
                                </div>
                            </td>
                            <td>
                                <span class="bc-badge bc-badge--{{ $item['classe'] }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('simulation.result', ['id' => $item['id']]) }}" class="bc-btn-action" title="{{ __('simulados.review_aria') }}">
                                    <span class="material-icons fs-5">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="bc-empty-state">
                                <span class="material-icons">history_toggle_off</span>
                                <h5>{{ __('simulados.empty_title') }}</h5>
                                <p class="text-muted mb-0">{{ __('simulados.empty_hint') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
