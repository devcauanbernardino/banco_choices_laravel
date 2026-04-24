@extends('layouts.public')

@section('title', __('quiz.page_title'))

@push('styles')
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('bancochoices-theme');
                if (stored === 'dark' || stored === 'light') {
                    document.documentElement.setAttribute('data-theme', stored);
                    document.documentElement.setAttribute('data-bs-theme', stored);
                    document.documentElement.style.colorScheme = stored;
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-mock.css') }}">
@endpush

@section('body_attr', ' class="quiz-mock-body"')

@section('content')
@php
    $opcoes = $questao->getOpcoes();
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    $respostaAtual = $respostas[$indiceAtual] ?? null;
    $textoPergunta = $questao->getPergunta();
    $isStudy = $modo === 'estudo';
    $hasFeedback = $isStudy && !empty($feedback);
    $isAnswered = $respostaAtual !== null && $respostaAtual !== '';
    $modoLabel = $isStudy ? __('quiz.mode_study') : __('quiz.mode_exam');
@endphp

<div class="quiz-mock">
    {{-- Header --}}
    <header class="quiz-mock-header">
        <div class="quiz-mock-brand">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" class="quiz-mock-logo" width="40" height="40">
        </div>
        <div class="quiz-mock-header-info">
            <button type="button"
                    class="quiz-mock-theme-btn js-theme-toggle-btn"
                    aria-pressed="false"
                    aria-label="{{ __('sidebar.theme_dark_aria') }}"
                    title="{{ __('sidebar.appearance') }}">
                <span class="material-symbols-outlined quiz-mock-theme-ico-light" aria-hidden="true">light_mode</span>
                <span class="material-symbols-outlined quiz-mock-theme-ico-dark" aria-hidden="true">dark_mode</span>
            </button>
            @if ($tempoRestante !== null)
                <div class="quiz-mock-timer-block">
                    <span class="quiz-mock-timer-label">{{ __('quiz.timer_label') }}</span>
                    <span class="quiz-mock-timer-value" id="timerDisplay">{{ gmdate('H:i:s', $tempoRestante) }}</span>
                </div>
                <div class="quiz-mock-divider" aria-hidden="true"></div>
            @endif
            <div class="quiz-mock-meta">
                <p class="quiz-mock-meta-title">{{ $modoLabel }}</p>
                <p class="quiz-mock-meta-sub">{{ $materiaNome }}</p>
            </div>
        </div>
    </header>

    {{-- Main canvas --}}
    <main class="quiz-mock-canvas">
        {{-- Question Section --}}
        <section class="quiz-mock-question-col">
            <div class="quiz-mock-breadcrumb">
                <nav class="quiz-mock-breadcrumb-nav" aria-label="{{ __('quiz.breadcrumb_aria') }}">
                    <span>{{ $modoLabel }}</span>
                    <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                    <span>{{ __('quiz.question_of', ['current' => $indiceAtual + 1, 'total' => $totalQuestoes]) }}</span>
                </nav>
                <span class="quiz-mock-tag">{{ $materiaNome }}</span>
            </div>

            <article class="quiz-mock-question-card">
                <h2 class="quiz-mock-question-text">{{ $textoPergunta }}</h2>

                @if (count($opcoes) === 0)
                    <div class="alert alert-warning mb-0" role="alert">{{ __('quiz.no_options') }}</div>
                @endif

                <form method="POST" action="{{ route('simulation.process') }}" id="quizForm" class="quiz-mock-form">
                    @csrf
                    <input type="hidden" name="indice" value="{{ $indiceAtual }}">

                    <div class="quiz-mock-options" role="radiogroup" aria-label="{{ __('quiz.options_aria') }}">
                        @foreach ($opcoes as $i => $opcao)
                            @php
                                $isSelected = (string) $respostaAtual === (string) $i;
                                $isCorrect = $hasFeedback && (string) ($feedback['resposta_correta'] ?? '') === (string) $i;
                                $isWrongPick = $hasFeedback && $isSelected && empty($feedback['acertou']);
                            @endphp
                            <label class="quiz-mock-option
                                {{ $isSelected ? 'is-selected' : '' }}
                                {{ $isCorrect ? 'is-correct' : '' }}
                                {{ $isWrongPick ? 'is-wrong' : '' }}">
                                <input type="radio" name="resposta" value="{{ $i }}"
                                       {{ $isSelected ? 'checked' : '' }}
                                       @if ($hasFeedback) disabled @endif>
                                <span class="quiz-mock-option-letter" aria-hidden="true">{{ $letras[$i] ?? ($i + 1) }}</span>
                                <span class="quiz-mock-option-text">{{ $opcao }}</span>
                                @if ($hasFeedback && $isCorrect)
                                    <span class="material-symbols-outlined quiz-mock-option-ico" aria-hidden="true">check_circle</span>
                                @elseif ($isWrongPick)
                                    <span class="material-symbols-outlined quiz-mock-option-ico" aria-hidden="true">cancel</span>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    {{-- Feedback (modo estudo) --}}
                    @if ($hasFeedback)
                        <div class="quiz-mock-feedback {{ !empty($feedback['acertou']) ? 'is-correct' : 'is-incorrect' }}">
                            <div class="quiz-mock-feedback-head">
                                <span class="material-symbols-outlined" aria-hidden="true">
                                    {{ !empty($feedback['acertou']) ? 'task_alt' : 'error' }}
                                </span>
                                <strong>
                                    {{ !empty($feedback['acertou']) ? __('quiz.correct') : __('quiz.incorrect') }}
                                </strong>
                            </div>
                            @if (!empty($feedback['feedback']))
                                <p class="quiz-mock-feedback-body">{{ $feedback['feedback'] }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Footer / Nav --}}
                    <div class="quiz-mock-actions">
                        @if ($indiceAtual > 0)
                            <button type="submit" name="voltar" value="1" class="quiz-mock-btn quiz-mock-btn-ghost">
                                <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                                <span>{{ __('quiz.prev') }}</span>
                            </button>
                        @else
                            <span class="quiz-mock-btn quiz-mock-btn-ghost is-disabled" aria-hidden="true">
                                <span class="material-symbols-outlined">arrow_back</span>
                                <span>{{ __('quiz.prev') }}</span>
                            </span>
                        @endif

                        <div class="quiz-mock-progress-mini">
                            <div class="quiz-mock-progress-bar">
                                <div class="quiz-mock-progress-fill" style="width: {{ (($indiceAtual + 1) / max(1, $totalQuestoes)) * 100 }}%"></div>
                            </div>
                            <span class="quiz-mock-progress-label">{{ __('quiz.exam_progress') }}</span>
                        </div>

                        @if ($indiceAtual < $totalQuestoes - 1)
                            <button type="submit" name="avancar" value="1" class="quiz-mock-btn quiz-mock-btn-primary">
                                <span>{{ __('quiz.next') }}</span>
                                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                            </button>
                        @else
                            <button type="submit" name="avancar" value="1" class="quiz-mock-btn quiz-mock-btn-success"
                                    @if (count($opcoes) === 0) disabled aria-disabled="true" @endif>
                                <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                                <span>{{ __('quiz.finish') }}</span>
                            </button>
                        @endif
                    </div>
                </form>
            </article>
        </section>

        {{-- Right Sidebar (mapa de questões + atalhos) --}}
        <aside class="quiz-mock-sidebar">
            <div class="quiz-mock-card">
                <div class="quiz-mock-card-head">
                    <h3 class="quiz-mock-card-title">{{ __('quiz.map_title') }}</h3>
                    <span class="quiz-mock-card-meta">{{ $indiceAtual + 1 }} / {{ $totalQuestoes }}</span>
                </div>
                <form method="POST" action="{{ route('simulation.process') }}" class="quiz-mock-map" id="quizMapForm">
                    @csrf
                    @for ($i = 0; $i < $totalQuestoes; $i++)
                        @php
                            $st = $mapaStatus[$i] ?? 'pendente';
                            $isCurrent = $i === $indiceAtual;
                        @endphp
                        <button type="submit" name="ir" value="{{ $i }}"
                                class="quiz-mock-map-cell
                                       quiz-mock-map-cell--{{ $st }}
                                       {{ $isCurrent ? 'is-current' : '' }}"
                                aria-label="{{ __('quiz.map_goto', ['n' => $i + 1]) }}"
                                @if ($isCurrent) aria-current="true" @endif>
                            {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}
                        </button>
                    @endfor
                </form>

                <div class="quiz-mock-legend">
                    @if ($isStudy)
                        <span class="quiz-mock-legend-item"><span class="quiz-mock-dot quiz-mock-dot--correta"></span>{{ __('quiz.legend_correct') }}</span>
                        <span class="quiz-mock-legend-item"><span class="quiz-mock-dot quiz-mock-dot--incorreta"></span>{{ __('quiz.legend_wrong') }}</span>
                    @else
                        <span class="quiz-mock-legend-item"><span class="quiz-mock-dot quiz-mock-dot--respondida"></span>{{ __('quiz.legend_answered') }}</span>
                    @endif
                    <span class="quiz-mock-legend-item"><span class="quiz-mock-dot quiz-mock-dot--pendente"></span>{{ __('quiz.legend_pending') }}</span>
                    <span class="quiz-mock-legend-item"><span class="quiz-mock-dot quiz-mock-dot--current"></span>{{ __('quiz.legend_current') }}</span>
                </div>
            </div>

            <div class="quiz-mock-card quiz-mock-card--stats">
                @php
                    $totalRespondidas = 0; $totalCorretas = 0;
                    foreach ($mapaStatus as $st) {
                        if ($st !== 'pendente') $totalRespondidas++;
                        if ($st === 'correta') $totalCorretas++;
                    }
                    $taxa = $totalRespondidas > 0 ? round($totalCorretas / $totalRespondidas * 100) : 0;
                @endphp
                <div class="quiz-mock-stat">
                    <span class="quiz-mock-stat-label">{{ __('quiz.stat_answered') }}</span>
                    <span class="quiz-mock-stat-value">{{ $totalRespondidas }}/{{ $totalQuestoes }}</span>
                </div>
                @if ($isStudy)
                    <div class="quiz-mock-stat quiz-mock-stat--accent">
                        <span class="quiz-mock-stat-label">{{ __('quiz.stat_hits') }}</span>
                        <span class="quiz-mock-stat-value">{{ $taxa }}%</span>
                    </div>
                @endif
            </div>
        </aside>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/theme.js') }}" defer></script>
<script>
    (function() {
        const isStudy = {{ $isStudy ? 'true' : 'false' }};
        const alreadyFeedback = {{ $hasFeedback ? 'true' : 'false' }};
        const form = document.getElementById('quizForm');

        document.querySelectorAll('.quiz-mock-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                const input = opt.querySelector('input[type="radio"]');
                if (!input || input.disabled) return;

                document.querySelectorAll('.quiz-mock-option').forEach(function(o) { o.classList.remove('is-selected'); });
                opt.classList.add('is-selected');
                input.checked = true;

                // Modo estudo: ao escolher, envia para revelar feedback imediatamente
                if (isStudy && !alreadyFeedback && form) {
                    e.preventDefault();
                    setTimeout(function() { form.submit(); }, 120);
                }
            });
        });
    })();

    @if ($tempoRestante !== null)
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
