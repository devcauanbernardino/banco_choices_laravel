@extends('layouts.public')

@section('title', __('result.page_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
<style>
    body {
        background: var(--app-surface-1, #f5f6fa);
    }
    .result-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem 1rem 3rem;
    }
    .result-score-card {
        text-align: center;
        padding: 2.5rem 2rem;
        border-radius: 20px;
        background: var(--app-surface-1, #fff);
        border: 2px solid var(--app-border, #e5e7eb);
        margin-bottom: 2rem;
    }
    .result-score-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }
    .result-approved {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 4px solid #10b981;
    }
    .result-failed {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 4px solid #ef4444;
    }
</style>
@endpush

@section('content')
<div class="result-container">
    {{-- Score card --}}
    <div class="result-score-card">
        <div class="result-score-circle {{ $porcentagem >= 70 ? 'result-approved' : 'result-failed' }}">
            {{ $porcentagem }}%
        </div>
        <h2 class="fw-bold mb-2">{{ $acertos }}/{{ $total }}</h2>
        <span class="badge rounded-pill fs-6 px-4 py-2 {{ $porcentagem >= 70 ? 'bg-success' : 'bg-danger' }}">
            {{ $porcentagem >= 70 ? __('result.approved') : __('result.failed') }}
        </span>
        <div class="mt-3 text-muted">
            <span class="fw-bold">{{ $materiaNome }}</span>
            @if ($tempoSegundos)
                &middot; {{ gmdate('H:i:s', $tempoSegundos) }}
            @endif
            &middot; {{ ucfirst($modo) }}
        </div>
    </div>

    {{-- Details table --}}
    <div class="bc-card overflow-hidden mb-4">
        <div class="bc-card-header">
            <h6 class="fw-bold mb-0">{{ __('result.details_title') }}</h6>
        </div>
        <div class="table-responsive">
            <table class="bc-table w-100 mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('result.th_question') }}</th>
                        <th>{{ __('result.th_your_answer') }}</th>
                        <th>{{ __('result.th_correct') }}</th>
                        <th>{{ __('result.th_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalhes as $i => $d)
                        <tr>
                            <td class="fw-bold">{{ $i + 1 }}</td>
                            <td>{{ Str::limit($d['pergunta'] ?? '', 80) }}</td>
                            <td>
                                <span class="fw-bold">{{ $d['resposta_usuario'] ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">{{ $d['resposta_correta'] ?? '-' }}</span>
                            </td>
                            <td>
                                @if (!empty($d['acertou']))
                                    <span class="badge bg-success rounded-pill">{{ __('quiz.correct') }}</span>
                                @else
                                    <span class="badge bg-danger rounded-pill">{{ __('quiz.incorrect') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if (!empty($d['feedback']))
                            <tr>
                                <td></td>
                                <td colspan="4" class="small text-muted fst-italic">{{ $d['feedback'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="d-flex flex-wrap gap-3 justify-content-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-inline-flex align-items-center gap-2">
            <span class="material-icons">home</span>
            {{ __('result.back_dashboard') }}
        </a>
        <a href="{{ route('questionbank') }}" class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center gap-2">
            <span class="material-icons">replay</span>
            {{ __('result.new_quiz') }}
        </a>
    </div>
</div>
@endsection
