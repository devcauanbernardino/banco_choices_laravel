@extends('layouts.public')

@section('title', __('quiz.page_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
<style>
    body {
        background: var(--app-surface-1, #f5f6fa);
    }
    .quiz-container {
        max-width: 800px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        padding: 1.5rem 1rem 3rem;
        box-sizing: border-box;
    }
    .quiz-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .quiz-progress {
        height: 8px;
        border-radius: 999px;
        background: var(--app-surface-2, #e5e7eb);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .quiz-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6a0392, #a855f7);
        transition: width 0.4s ease;
    }
    .quiz-option {
        display: block;
        width: 100%;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        border: 2px solid var(--app-border, #e5e7eb);
        border-radius: 12px;
        background: var(--app-surface-1, #fff);
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        text-align: left;
    }
    .quiz-option:hover {
        border-color: #6a0392;
        background: rgba(106, 3, 146, 0.04);
    }
    .quiz-option.selected {
        border-color: #6a0392;
        background: rgba(106, 3, 146, 0.08);
    }
    .quiz-option input[type="radio"] {
        display: none;
    }
    .quiz-option-letter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--app-surface-2, #f0f0f0);
        font-weight: 700;
        margin-right: 0.75rem;
        font-size: 0.9rem;
    }
    .quiz-option.selected .quiz-option-letter {
        background: #6a0392;
        color: #fff;
    }
    .quiz-feedback {
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-top: 1rem;
    }
    .quiz-nav {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    .quiz-timer {
        font-weight: 700;
        font-size: 1.1rem;
        color: #6a0392;
    }
</style>
@endpush

@section('content')
<div class="quiz-container">
    {{-- Header --}}
    <div class="quiz-header">
        <div>
            <h5 class="fw-bold mb-1">{{ $materiaNome }}</h5>
            <span class="text-muted small">{{ __('quiz.question_of', ['current' => $indiceAtual + 1, 'total' => $totalQuestoes]) }}</span>
        </div>
        @if ($tempoRestante !== null)
            <div class="quiz-timer" id="quizTimer">
                <span class="material-icons align-middle me-1">timer</span>
                <span id="timerDisplay">{{ gmdate('H:i:s', $tempoRestante) }}</span>
            </div>
        @endif
    </div>

    {{-- Progress bar --}}
    <div class="quiz-progress">
        <div class="quiz-progress-bar" style="width: {{ (($indiceAtual + 1) / $totalQuestoes) * 100 }}%"></div>
    </div>

    {{-- Question (texto e alternativas via App\Support\Question — JSON pode usar pergunta/enunciado e opcoes/alternativas) --}}
    <div class="bc-card p-4 mb-3">
        @php
            $opcoes = $questao->getOpcoes();
            $letras = ['A', 'B', 'C', 'D', 'E'];
            $respostaAtual = $respostas[$indiceAtual] ?? null;
            $textoPergunta = $questao->getPergunta();
        @endphp

        <h5 class="fw-bold mb-4">{{ $textoPergunta }}</h5>

        @if (count($opcoes) === 0)
            <div class="alert alert-warning mb-0" role="alert">{{ __('quiz.no_options') }}</div>
        @endif

        <form method="POST" action="{{ route('simulation.process') }}" id="quizForm">
            @csrf
            <input type="hidden" name="indice" value="{{ $indiceAtual }}">

            @foreach ($opcoes as $i => $opcao)
                <label class="quiz-option {{ $respostaAtual === $i ? 'selected' : '' }}">
                    <input type="radio" name="resposta" value="{{ $i }}"
                           {{ $respostaAtual === $i ? 'checked' : '' }}>
                    <span class="quiz-option-letter">{{ $letras[$i] ?? ($i + 1) }}</span>
                    {{ $opcao }}
                </label>
            @endforeach

            {{-- Feedback (study mode) --}}
            @if (!empty($feedback))
                <div class="quiz-feedback alert {{ !empty($feedback['acertou']) ? 'alert-success' : 'alert-danger' }}">
                    <strong>{{ !empty($feedback['acertou']) ? __('quiz.correct') : __('quiz.incorrect') }}</strong>
                    @if (!empty($feedback['feedback']))
                        <p class="mb-0 mt-2">{{ $feedback['feedback'] }}</p>
                    @endif
                </div>
            @endif

            {{-- Navigation --}}
            <div class="quiz-nav">
                @if ($indiceAtual > 0)
                    <button type="submit" name="voltar" value="1" class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-inline-flex align-items-center gap-2">
                        <span class="material-icons">arrow_back</span>
                        {{ __('quiz.prev') }}
                    </button>
                @else
                    <div></div>
                @endif

                @if ($indiceAtual < $totalQuestoes - 1)
                    <button type="submit" name="avancar" value="1" class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center gap-2">
                        {{ __('quiz.next') }}
                        <span class="material-icons">arrow_forward</span>
                    </button>
                @else
                    <button type="submit" name="avancar" value="1" class="btn btn-success btn-lg px-4 rounded-3 fw-bold d-inline-flex align-items-center gap-2"
                            @if (count($opcoes) === 0) disabled aria-disabled="true" @endif>
                        <span class="material-icons">check_circle</span>
                        {{ __('quiz.finish') }}
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Option selection visual feedback
    document.querySelectorAll('.quiz-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.quiz-option').forEach(function(o) { o.classList.remove('selected'); });
            opt.classList.add('selected');
        });
    });

    @if ($tempoRestante !== null)
    // Timer countdown
    (function() {
        let remaining = {{ (int) $tempoRestante }};
        const display = document.getElementById('timerDisplay');
        const interval = setInterval(function() {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                var f = document.getElementById('quizForm');
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'timeout';
                h.value = '1';
                f.appendChild(h);
                f.submit();
                return;
            }
            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            display.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }, 1000);
    })();
    @endif
</script>
@endpush
