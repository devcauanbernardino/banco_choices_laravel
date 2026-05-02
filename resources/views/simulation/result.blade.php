@extends('layouts.result-standalone')

@section('title', __('result.page_title'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/simulation-result.css') }}?v={{ @filemtime(public_path('assets/css/simulation-result.css')) }}">
@endpush

@section('content')
    <div class="sim-result-page">
        <header class="sim-result-page__intro">
            <h1 class="sim-result-page__title">{{ __('result.standalone_heading') }}</h1>
            <p class="sim-result-page__sub">{{ __('result.standalone_sub') }}</p>
            <div class="sim-result-page__chips d-flex flex-wrap align-items-center justify-content-center gap-2 mt-2">
                <span class="sim-result-page__chip fw-semibold">{{ $materiaNome }}</span>
                @if ($tempoSegundos)
                    <span class="text-muted" aria-hidden="true">&middot;</span>
                    <span class="text-muted small">{{ gmdate('H:i:s', $tempoSegundos) }}</span>
                @endif
                <span class="text-muted" aria-hidden="true">&middot;</span>
                <span class="text-muted small">{{ ucfirst($modo) }}</span>
            </div>
        </header>

        <div class="sim-result-score-card">
            <div class="sim-result-score-circle {{ $porcentagem >= 70 ? 'sim-result-approved' : 'sim-result-failed' }}">
                {{ $porcentagem }}%
            </div>
            <h2 class="h3 fw-bold mb-3 sim-result-fraction">{{ $acertos }}/{{ $total }}</h2>
            <span class="badge rounded-pill fs-6 px-4 py-2 {{ $porcentagem >= 70 ? 'bg-success' : 'bg-danger' }}">
                {{ $porcentagem >= 70 ? __('result.approved') : __('result.failed') }}
            </span>
        </div>

        <div class="bc-card overflow-hidden mb-4 sim-result-table-card">
            <div class="bc-card-header">
                <h2 class="h6 fw-bold mb-0">{{ __('result.details_title') }}</h2>
            </div>
            <div class="table-responsive sim-result-table-wrap">
                <table class="bc-table w-100 mb-0 sim-result-table">
                    <thead>
                        <tr>
                            <th class="sim-result-col-n">#</th>
                            <th>{{ __('result.th_question') }}</th>
                            <th class="text-nowrap">{{ __('result.th_your_answer') }}</th>
                            <th class="text-nowrap">{{ __('result.th_correct') }}</th>
                            <th class="text-nowrap sim-result-col-status">{{ __('result.th_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalhes as $i => $d)
                            <tr>
                                <td class="fw-bold align-top sim-result-col-n">{{ $i + 1 }}</td>
                                <td class="sim-result-q-cell">
                                    {!! nl2br(e($d['pergunta'] ?? '')) !!}
                                </td>
                                <td class="align-top">
                                    <span class="fw-bold">{{ $d['resposta_usuario'] ?? '—' }}</span>
                                </td>
                                <td class="align-top">
                                    <span class="fw-bold text-success">{{ $d['resposta_correta'] ?? '—' }}</span>
                                </td>
                                <td class="align-top sim-result-col-status">
                                    @if (!empty($d['acertou']))
                                        <span class="badge bg-success rounded-pill">{{ __('quiz.correct') }}</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill">{{ __('quiz.incorrect') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if (!empty($d['feedback']))
                                <tr class="sim-result-feedback">
                                    <td></td>
                                    <td colspan="4" class="small text-muted fst-italic sim-result-feedback-cell">
                                        {!! nl2br(e($d['feedback'])) !!}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-center sim-result-actions">
            <a href="{{ route('questionbank') }}" class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center gap-2">
                <span class="material-icons">replay</span>
                {{ __('result.new_quiz') }}
            </a>
        </div>
    </div>
@endsection
