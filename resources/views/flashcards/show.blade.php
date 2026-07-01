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

.fc-wrap { max-width: 560px; margin: 0 auto; padding: clamp(20px,4vw,48px) 16px; }
.fc-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 22px; }
.fc-top__materia { font-size: .82rem; font-weight: 700; color: var(--app-muted); }
.fc-top__progress { font-size: .82rem; font-weight: 700; color: #a855f7; }

.fc-flip { perspective: 1400px; }
.fc-flip-inner { position: relative; width: 100%; min-height: 280px; transition: transform .55s cubic-bezier(.4,.15,.2,1); transform-style: preserve-3d; }
.fc-flip.is-flipped .fc-flip-inner { transform: rotateY(180deg); }
.fc-flip-face { position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden; border-radius: 20px; padding: clamp(24px,5vw,40px); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 16px; background: var(--app-surface); border: 1px solid var(--app-border); box-shadow: 0 10px 34px rgba(0,0,0,.08); }
.fc-flip-back { transform: rotateY(180deg); }
.fc-flip-face--front { cursor: pointer; border: none; width: 100%; }
.fc-flip-tag { font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #a855f7; }
.fc-flip-text { font-size: 1.08rem; color: var(--app-text); line-height: 1.6; margin: 0; }
.fc-flip-hint { font-size: .78rem; color: var(--app-muted); }

.fc-rate-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 20px; }
.fc-rate-btn { padding: 12px 8px; border-radius: 12px; border: 1.5px solid var(--app-border); background: var(--app-surface); font-size: .8rem; font-weight: 700; cursor: pointer; color: var(--app-text); }
.fc-rate-btn--again { color: #f87171; border-color: rgba(239,68,68,.35); }
.fc-rate-btn--hard { color: #f97316; border-color: rgba(249,115,22,.35); }
.fc-rate-btn--good { color: #22c55e; border-color: rgba(34,197,94,.35); }
.fc-rate-btn--easy { color: #38bdf8; border-color: rgba(56,189,248,.35); }

.fc-warn { margin-top: 14px; font-size: .78rem; color: #f97316; text-align: center; }
</style>
@endpush

@section('content')
<div class="fc-wrap">
    <div class="fc-top">
        <span class="fc-top__materia">{{ $materiaNome }}</span>
        <span class="fc-top__progress">{{ __('flashcards.review.progress', ['current' => $atual + 1, 'total' => $total]) }}</span>
    </div>

    <div class="fc-flip" id="fcFlip">
        <div class="fc-flip-inner">
            <form action="{{ route('flashcards.process') }}" method="post" class="fc-flip-face fc-flip-face--front">
                @csrf
                <span class="fc-flip-tag">{{ __('flashcards.review.due_badge') }}</span>
                <p class="fc-flip-text">{{ $frente }}</p>
                @if (! $revelado)
                    <button type="submit" name="revelar" value="1" class="fc-flip-hint" style="background:none;border:none;cursor:pointer;">
                        {{ __('flashcards.review.reveal_button') }}
                    </button>
                @endif
            </form>
            <div class="fc-flip-face fc-flip-back">
                <span class="fc-flip-tag">{{ __('flashcards.summary.breakdown_good') }}</span>
                <p class="fc-flip-text">{{ $verso }}</p>
            </div>
        </div>
    </div>

    @if ($erroGeracao)
        <p class="fc-warn">{{ $erroGeracao }}</p>
    @endif

    @if ($revelado)
        <form action="{{ route('flashcards.process') }}" method="post">
            @csrf
            <div class="fc-rate-grid">
                <button type="submit" name="avaliar" value="again" class="fc-rate-btn fc-rate-btn--again">{{ __('flashcards.review.rate_again') }}</button>
                <button type="submit" name="avaliar" value="hard" class="fc-rate-btn fc-rate-btn--hard">{{ __('flashcards.review.rate_hard') }}</button>
                <button type="submit" name="avaliar" value="good" class="fc-rate-btn fc-rate-btn--good">{{ __('flashcards.review.rate_good') }}</button>
                <button type="submit" name="avaliar" value="easy" class="fc-rate-btn fc-rate-btn--easy">{{ __('flashcards.review.rate_easy') }}</button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var flip = document.getElementById('fcFlip');
    if (!flip) return;
    var revelado = {{ $revelado ? 'true' : 'false' }};
    if (revelado) {
        requestAnimationFrame(function () {
            setTimeout(function () { flip.classList.add('is-flipped'); }, 60);
        });
    }
})();
</script>
@endpush
