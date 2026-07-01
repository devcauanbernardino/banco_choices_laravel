@extends('layouts.app')

@section('title', __('flashcards.page_title'))
@section('mobile_title', __('flashcards.mobile_title'))
@section('topbar_title', __('flashcards.mobile_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<style>
.fc-page { position: relative; isolation: isolate; }
.fc-page::before, .fc-page::after { content: ''; position: fixed; width: 380px; height: 380px; border-radius: 50%; filter: blur(90px); z-index: -1; pointer-events: none; opacity: .5; }
.fc-page::before { background: #8b1fb8; top: 8%; left: 8%; }
.fc-page::after { background: #38bdf8; bottom: 4%; right: 6%; }
[data-theme="dark"] .fc-page::before, [data-theme="dark"] .fc-page::after { opacity: .35; }

.fc-header { margin-bottom: 24px; }
.fc-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.fc-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }

.fc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }

.fc-glass { background: rgba(255,255,255,.5); backdrop-filter: blur(18px) saturate(180%); -webkit-backdrop-filter: blur(18px) saturate(180%); border: 1px solid rgba(255,255,255,.4); box-shadow: 0 8px 32px rgba(31,10,60,.1); }
[data-theme="dark"] .fc-glass { background: rgba(255,255,255,.055); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 32px rgba(0,0,0,.35); }

.fc-card { border-radius: 18px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.fc-card__title { font-size: 1rem; font-weight: 700; color: var(--app-text); margin: 0; }
.fc-card__badges { display: flex; gap: 8px; flex-wrap: wrap; }
.fc-badge { font-size: .74rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }
.fc-badge--due { background: rgba(139,31,184,.16); color: #a855f7; }
.fc-badge--new { background: rgba(34,197,94,.16); color: #22c55e; }
.fc-badge--empty { background: rgba(120,120,140,.16); color: var(--app-muted); }
.fc-card__actions { margin-top: auto; display: flex; align-items: center; gap: 10px; }
.fc-card__input { width: 72px; border: 1px solid rgba(255,255,255,.4); border-radius: 8px; padding: 6px 8px; font-size: .82rem; background: rgba(255,255,255,.3); color: var(--app-text); }
[data-theme="dark"] .fc-card__input { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12); }
.fc-card__btn { flex: 1; padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.fc-card__btn:disabled { opacity: .4; cursor: default; box-shadow: none; }

/* Modal de revisão */
.fc-review-modal .modal-content { border-radius: 24px; border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.55); backdrop-filter: blur(28px) saturate(190%); -webkit-backdrop-filter: blur(28px) saturate(190%); color: var(--app-text); font-family: 'Inter', system-ui, sans-serif; box-shadow: 0 30px 80px rgba(106,3,146,.28); position: relative; overflow: hidden; }
[data-theme="dark"] .fc-review-modal .modal-content { background: rgba(20,20,26,.6); border-color: rgba(255,255,255,.1); box-shadow: 0 30px 80px rgba(0,0,0,.55); }
.fc-review-modal .modal-content::before, .fc-review-modal .modal-content::after { content: ''; position: absolute; width: 240px; height: 240px; border-radius: 50%; filter: blur(70px); z-index: 0; pointer-events: none; opacity: .5; }
.fc-review-modal .modal-content::before { background: #8b1fb8; top: -70px; left: -70px; }
.fc-review-modal .modal-content::after { background: #38bdf8; bottom: -70px; right: -70px; }
.fc-review-modal .modal-header, .fc-review-modal .modal-body { position: relative; z-index: 1; }
.fc-review-modal__materia { font-size: .8rem; font-weight: 700; color: var(--app-muted); }
.fc-review-modal__progress { font-size: .78rem; font-weight: 700; color: #a855f7; }

.fc-flip { perspective: 1400px; margin-bottom: 4px; }
.fc-flip-inner { position: relative; width: 100%; min-height: 220px; transition: transform .55s cubic-bezier(.4,.15,.2,1); transform-style: preserve-3d; }
.fc-flip.is-flipped .fc-flip-inner { transform: rotateY(180deg); }
.fc-flip-face { position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden; border-radius: 18px; padding: clamp(20px,4vw,32px); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 14px; background: rgba(255,255,255,.4); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.45); box-shadow: 0 10px 30px rgba(31,10,60,.1), inset 0 1px 0 rgba(255,255,255,.5); }
[data-theme="dark"] .fc-flip-face { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 10px 30px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.05); }
.fc-flip-face--front { cursor: pointer; }
.fc-flip-face--front:disabled { cursor: default; }
.fc-flip-back { transform: rotateY(180deg); }
.fc-flip-tag { font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #a855f7; }
.fc-flip-text { font-size: 1.02rem; color: var(--app-text); line-height: 1.6; margin: 0; }
.fc-flip-hint { font-size: .76rem; color: var(--app-muted); }

.fc-rate-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 16px; }
.fc-rate-btn { padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255,255,255,.4); background: rgba(255,255,255,.35); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); font-size: .76rem; font-weight: 700; cursor: pointer; color: var(--app-text); transition: transform .15s ease, background .15s ease; }
.fc-rate-btn:hover { transform: translateY(-2px); }
[data-theme="dark"] .fc-rate-btn { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.12); }
.fc-rate-btn--again { color: #f87171; border-color: rgba(239,68,68,.4); }
.fc-rate-btn--hard { color: #f97316; border-color: rgba(249,115,22,.4); }
.fc-rate-btn--good { color: #22c55e; border-color: rgba(34,197,94,.4); }
.fc-rate-btn--easy { color: #38bdf8; border-color: rgba(56,189,248,.4); }

.fc-warn { margin-top: 12px; font-size: .76rem; color: #f97316; text-align: center; }

.fc-sum { text-align: center; padding: 8px 0; }
.fc-sum__total { font-size: 2rem; font-weight: 800; color: #a855f7; margin: 10px 0; }
.fc-sum__grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin: 16px 0; }
.fc-sum__cell { background: rgba(255,255,255,.35); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.4); border-radius: 12px; padding: 12px 6px; }
[data-theme="dark"] .fc-sum__cell { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); }
.fc-sum__cell strong { display: block; font-size: 1.1rem; }
</style>
@endpush

@section('content')
<div class="fc-page">
<div class="fc-header">
    <h1>{{ __('flashcards.header.title') }}</h1>
    <p>{{ __('flashcards.header.sub') }}</p>
</div>

@if ($materias->isEmpty())
    <p class="text-muted">{{ __('flashcards.no_subjects') }}</p>
@else
    <div class="fc-grid">
        @foreach ($materias as $m)
            @php $resumo = $resumoPorMateria[$m->id] ?? ['due_count' => 0, 'new_count' => 0, 'new_available_count' => 0]; @endphp
            <div class="fc-card fc-glass">
                <h3 class="fc-card__title">{{ $m->nome }}</h3>
                <div class="fc-card__badges">
                    @if ($resumo['due_count'] > 0)
                        <span class="fc-badge fc-badge--due">{{ __('flashcards.card.due_count', ['n' => $resumo['due_count']]) }}</span>
                    @endif
                    @if ($resumo['new_available_count'] > 0)
                        <span class="fc-badge fc-badge--new">{{ __('flashcards.card.new_count', ['n' => $resumo['new_available_count']]) }}</span>
                    @endif
                    @if ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0)
                        <span class="fc-badge fc-badge--empty">{{ __('flashcards.card.all_caught_up') }}</span>
                    @endif
                </div>
                <form class="fc-card__actions" data-fc-start>
                    <input type="hidden" name="materia" value="{{ $m->id }}">
                    <input type="number" name="novos_por_dia" class="fc-card__input" min="0" max="200" value="20"
                           aria-label="{{ __('flashcards.form.new_per_day_label') }}">
                    <button type="submit" class="fc-card__btn"
                            @if ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0) disabled @endif>
                        {{ __('flashcards.form.start') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif
</div>
@endsection

@push('modals')
<div class="modal fade fc-review-modal" id="fcReviewModal" tabindex="-1" aria-labelledby="fcReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="fc-review-modal__materia" id="fcModalMateria"></div>
                    <div class="fc-review-modal__progress" id="fcModalProgress"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="fcErrorBox" class="alert alert-danger d-none"></div>

                <div id="fcModalCardView">
                    <div class="fc-flip" id="fcFlip">
                        <div class="fc-flip-inner">
                            <button type="button" class="fc-flip-face fc-flip-face--front" id="fcFrontBtn">
                                <span class="fc-flip-tag">{{ __('flashcards.review.due_badge') }}</span>
                                <p class="fc-flip-text" id="fcFrenteText"></p>
                                <span class="fc-flip-hint" id="fcRevealHint">{{ __('flashcards.review.reveal_button') }}</span>
                            </button>
                            <div class="fc-flip-face fc-flip-back">
                                <span class="fc-flip-tag">{{ __('flashcards.summary.breakdown_good') }}</span>
                                <p class="fc-flip-text" id="fcVersoText"></p>
                            </div>
                        </div>
                    </div>

                    <p class="fc-warn d-none" id="fcErroGeracao"></p>

                    <div class="fc-rate-grid d-none" id="fcRateGrid">
                        <button type="button" class="fc-rate-btn fc-rate-btn--again" data-avaliacao="again">{{ __('flashcards.review.rate_again') }}</button>
                        <button type="button" class="fc-rate-btn fc-rate-btn--hard" data-avaliacao="hard">{{ __('flashcards.review.rate_hard') }}</button>
                        <button type="button" class="fc-rate-btn fc-rate-btn--good" data-avaliacao="good">{{ __('flashcards.review.rate_good') }}</button>
                        <button type="button" class="fc-rate-btn fc-rate-btn--easy" data-avaliacao="easy">{{ __('flashcards.review.rate_easy') }}</button>
                    </div>
                </div>

                <div id="fcModalSummaryView" class="d-none fc-sum">
                    <h2 class="h5 fw-bold mb-0">{{ __('flashcards.summary.title') }}</h2>
                    <div class="fc-sum__total" id="fcSumTotal"></div>
                    <div class="fc-sum__grid">
                        <div class="fc-sum__cell"><strong style="color:#f87171;" id="fcSumAgain">0</strong>{{ __('flashcards.summary.breakdown_again') }}</div>
                        <div class="fc-sum__cell"><strong style="color:#f97316;" id="fcSumHard">0</strong>{{ __('flashcards.summary.breakdown_hard') }}</div>
                        <div class="fc-sum__cell"><strong style="color:#22c55e;" id="fcSumGood">0</strong>{{ __('flashcards.summary.breakdown_good') }}</div>
                        <div class="fc-sum__cell"><strong style="color:#38bdf8;" id="fcSumEasy">0</strong>{{ __('flashcards.summary.breakdown_easy') }}</div>
                    </div>
                    <button type="button" class="fc-card__btn" data-bs-dismiss="modal">{{ __('flashcards.summary.cta_back') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('fcReviewModal');
    if (!modalEl) return;
    var modal = null;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var reviewedAny = false;

    var cardView = document.getElementById('fcModalCardView');
    var summaryView = document.getElementById('fcModalSummaryView');
    var flip = document.getElementById('fcFlip');
    var frontBtn = document.getElementById('fcFrontBtn');
    var revealHint = document.getElementById('fcRevealHint');
    var frenteText = document.getElementById('fcFrenteText');
    var versoText = document.getElementById('fcVersoText');
    var rateGrid = document.getElementById('fcRateGrid');
    var erroGeracaoBox = document.getElementById('fcErroGeracao');
    var errorBox = document.getElementById('fcErrorBox');
    var materiaLbl = document.getElementById('fcModalMateria');
    var progressLbl = document.getElementById('fcModalProgress');
    var progressTpl = @json(__('flashcards.review.progress', ['current' => '__C__', 'total' => '__T__']));

    function headers() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        };
    }

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove('d-none');
    }

    function renderCard(data) {
        errorBox.classList.add('d-none');
        cardView.classList.remove('d-none');
        summaryView.classList.add('d-none');
        materiaLbl.textContent = data.materia_nome;
        progressLbl.textContent = progressTpl.replace('__C__', data.atual + 1).replace('__T__', data.total);
        frenteText.textContent = data.frente;

        if (data.revelado) {
            versoText.textContent = data.verso;
            flip.classList.add('is-flipped');
            rateGrid.classList.remove('d-none');
            frontBtn.disabled = true;
            revealHint.style.visibility = 'hidden';
        } else {
            flip.classList.remove('is-flipped');
            rateGrid.classList.add('d-none');
            frontBtn.disabled = false;
            revealHint.style.visibility = 'visible';
        }

        if (data.erro_geracao) {
            erroGeracaoBox.textContent = data.erro_geracao;
            erroGeracaoBox.classList.remove('d-none');
        } else {
            erroGeracaoBox.classList.add('d-none');
        }
    }

    function renderSummary(data) {
        cardView.classList.add('d-none');
        summaryView.classList.remove('d-none');
        document.getElementById('fcSumTotal').textContent = @json(__('flashcards.summary.total_reviewed', ['n' => '__N__'])).replace('__N__', data.total);
        document.getElementById('fcSumAgain').textContent = data.contagem.again;
        document.getElementById('fcSumHard').textContent = data.contagem.hard;
        document.getElementById('fcSumGood').textContent = data.contagem.good;
        document.getElementById('fcSumEasy').textContent = data.contagem.easy;
    }

    function startReview(materiaId, novosPorDia) {
        fetch('{{ route('flashcards.create') }}', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ materia: materiaId, novos_por_dia: novosPorDia })
        }).then(function (r) {
            return r.json().then(function (body) { return { ok: r.ok, body: body }; });
        }).then(function (res) {
            if (!modal) modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            if (!res.ok) {
                cardView.classList.add('d-none');
                summaryView.classList.add('d-none');
                showError((res.body && res.body.error) || '');
                return;
            }
            reviewedAny = false;
            renderCard(res.body);
        });
    }

    frontBtn.addEventListener('click', function () {
        if (frontBtn.disabled) return;
        fetch('{{ route('flashcards.process') }}', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ revelar: 1 })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.error) { showError(data.error); return; }
            renderCard(data);
        });
    });

    rateGrid.querySelectorAll('button[data-avaliacao]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            reviewedAny = true;
            fetch('{{ route('flashcards.process') }}', {
                method: 'POST',
                headers: headers(),
                body: JSON.stringify({ avaliar: btn.dataset.avaliacao })
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.error) { showError(data.error); return; }
                if (data.finished) {
                    renderSummary(data);
                } else {
                    renderCard(data);
                }
            });
        });
    });

    document.querySelectorAll('[data-fc-start]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var materiaId = form.querySelector('[name="materia"]').value;
            var novos = form.querySelector('[name="novos_por_dia"]').value;
            startReview(materiaId, novos);
        });
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (reviewedAny) {
            location.reload();
        }
    });
})();
</script>
@endpush
