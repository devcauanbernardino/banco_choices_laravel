@extends('layouts.public')

@section('title', __('flashcards.page_title'))

@section('body_attr')
 class="quiz-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
.quiz-page { font-family: 'Inter', system-ui, sans-serif; background: var(--app-bg); }
.quiz-page h1, .quiz-page h2, .quiz-page h3 { font-family: 'Poppins', system-ui, sans-serif; }

.fc-wrap { max-width: 680px; margin: 0 auto; padding: clamp(20px,4vw,48px) 16px; }
.fc-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.fc-top__materia { font-size: .82rem; font-weight: 700; color: var(--app-muted); }
.fc-top__progress { font-size: .82rem; font-weight: 700; color: #a855f7; }

.fc-card { background: var(--app-surface); border: 1px solid var(--app-border); border-radius: 18px; padding: clamp(20px,4vw,32px); }
.fc-question { font-size: 1.02rem; color: var(--app-text); line-height: 1.6; margin-bottom: 20px; }

.fc-opt { display: flex; align-items: center; gap: 12px; padding: 13px 16px; border-radius: 13px; border: 1.5px solid var(--app-border); background: var(--app-bg); margin-bottom: 10px; }
.fc-opt.is-correct { border-color: rgba(22,163,74,.55); background: rgba(22,163,74,.1); }
.fc-opt-letter { flex-shrink: 0; width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .72rem; background: rgba(106,3,146,.08); color: #a855f7; border: 1px solid rgba(106,3,146,.2); }
.fc-opt.is-correct .fc-opt-letter { background: rgba(22,163,74,.18); color: #22c55e; border-color: rgba(22,163,74,.35); }
.fc-opt-text { font-size: .92rem; color: var(--app-text); line-height: 1.5; }

.fc-feedback { margin-top: 16px; padding: 16px 18px; border-radius: 14px; background: rgba(139,31,184,.06); border: 1px solid rgba(139,31,184,.2); font-size: .86rem; color: var(--app-text); line-height: 1.65; }

.fc-reveal-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 12px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-family: 'Poppins', sans-serif; font-size: .9rem; font-weight: 700; cursor: pointer; margin-top: 8px; }

.fc-rate-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 20px; }
.fc-rate-btn { padding: 12px 8px; border-radius: 12px; border: 1.5px solid var(--app-border); background: var(--app-surface); font-size: .8rem; font-weight: 700; cursor: pointer; color: var(--app-text); }
.fc-rate-btn--again { color: #f87171; border-color: rgba(239,68,68,.35); }
.fc-rate-btn--hard { color: #f97316; border-color: rgba(249,115,22,.35); }
.fc-rate-btn--good { color: #22c55e; border-color: rgba(34,197,94,.35); }
.fc-rate-btn--easy { color: #38bdf8; border-color: rgba(56,189,248,.35); }
</style>
@endpush

@section('content')
@php
    $opcoes = $questao->getOpcoes();
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    $isMulti = $questao->isMultiResposta();
    $corretasMulti = $isMulti ? $questao->getCorrectAnswerIndices() : [];
    $correctIdx = $isMulti ? null : $questao->getCorrectAnswer();
@endphp

<div class="fc-wrap">
    <div class="fc-top">
        <span class="fc-top__materia">{{ $materiaNome }}</span>
        <span class="fc-top__progress">{{ __('flashcards.review.progress', ['current' => $atual + 1, 'total' => $total]) }}</span>
    </div>

    <div class="fc-card">
        <p class="fc-question">{{ $questao->getPergunta() }}</p>

        @foreach ($opcoes as $i => $opcao)
            @php
                $isCorrect = $revelado && ($isMulti ? in_array((string) $i, $corretasMulti, true) : (string) $correctIdx === (string) $i);
            @endphp
            <div class="fc-opt {{ $isCorrect ? 'is-correct' : '' }}">
                <span class="fc-opt-letter" aria-hidden="true">{{ $letras[$i] ?? ($i + 1) }}</span>
                <span class="fc-opt-text">{{ $opcao }}</span>
            </div>
        @endforeach

        @if ($revelado)
            <div class="fc-feedback">{{ $questao->getFeedback() }}</div>

            <form action="{{ route('flashcards.process') }}" method="post">
                @csrf
                <div class="fc-rate-grid">
                    <button type="submit" name="avaliar" value="again" class="fc-rate-btn fc-rate-btn--again">{{ __('flashcards.review.rate_again') }}</button>
                    <button type="submit" name="avaliar" value="hard" class="fc-rate-btn fc-rate-btn--hard">{{ __('flashcards.review.rate_hard') }}</button>
                    <button type="submit" name="avaliar" value="good" class="fc-rate-btn fc-rate-btn--good">{{ __('flashcards.review.rate_good') }}</button>
                    <button type="submit" name="avaliar" value="easy" class="fc-rate-btn fc-rate-btn--easy">{{ __('flashcards.review.rate_easy') }}</button>
                </div>
            </form>
        @else
            <form action="{{ route('flashcards.process') }}" method="post">
                @csrf
                <button type="submit" name="revelar" value="1" class="fc-reveal-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                    {{ __('flashcards.review.reveal_button') }}
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
