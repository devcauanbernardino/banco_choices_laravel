@extends('layouts.app')

@section('title', __('flashcards.page_title'))
@section('mobile_title', __('flashcards.mobile_title'))
@section('topbar_title', __('flashcards.mobile_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<style>
.fc-header { margin-bottom: 24px; }
.fc-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.fc-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }

.fc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 22px; }

/* Efeito de pilha: 2 cartões espiando atrás do card principal */
.fc-deck-card { position: relative; margin: 0 12px 12px 0; }
.fc-deck-card::before, .fc-deck-card::after { content: ''; position: absolute; inset: 0; border-radius: 18px; background: var(--app-surface); border: 1px solid var(--app-border); }
.fc-deck-card::before { transform: translate(6px, 6px); opacity: .8; z-index: 0; }
.fc-deck-card::after { transform: translate(12px, 12px); opacity: .5; z-index: -1; }

.fc-card { position: relative; z-index: 1; background: var(--app-surface); border: 1px solid var(--app-border); box-shadow: 0 10px 26px rgba(15,23,42,.08); border-radius: 18px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
[data-theme="dark"] .fc-card { box-shadow: 0 10px 26px rgba(0,0,0,.4); }
.fc-card__title { font-size: 1rem; font-weight: 700; color: var(--app-text); margin: 0; }
.fc-card__badges { display: flex; gap: 8px; flex-wrap: wrap; }
.fc-badge { font-size: .74rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }
.fc-badge--due { background: rgba(13,148,136,.14); color: #0d9488; }
[data-theme="dark"] .fc-badge--due { background: rgba(45,212,191,.16); color: #2dd4bf; }
.fc-badge--new { background: rgba(34,197,94,.16); color: #22c55e; }
.fc-badge--empty { background: rgba(120,120,140,.16); color: var(--app-muted); }
.fc-card__actions { margin-top: auto; display: flex; align-items: center; gap: 10px; }
.fc-card__input { width: 72px; border: 1px solid var(--app-border); border-radius: 8px; padding: 6px 8px; font-size: .82rem; background: var(--app-bg); color: var(--app-text); }
.fc-card__btn { flex: 1; padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.fc-card__btn:disabled { opacity: .4; cursor: default; box-shadow: none; }

/* Modal de revisão */
.fc-review-modal .modal-content { border-radius: 22px; border: 1px solid var(--app-border); background: var(--app-surface); box-shadow: 0 25px 60px rgba(15,23,42,.25); }
[data-theme="dark"] .fc-review-modal .modal-content { box-shadow: 0 25px 60px rgba(0,0,0,.5); }
.fc-review-modal .modal-header { display: none; }
.fc-review-modal .modal-body { padding: 18px; }

.fc-toprow { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
.fc-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: .76rem; font-weight: 700; white-space: nowrap; }
.fc-pill--materia { background: rgba(13,148,136,.14); color: #0d9488; }
[data-theme="dark"] .fc-pill--materia { background: rgba(45,212,191,.16); color: #2dd4bf; }
.fc-pill--streak { background: rgba(249,115,22,.14); color: #ea580c; }
[data-theme="dark"] .fc-pill--streak { background: rgba(251,146,60,.18); color: #fb923c; }

.fc-panel-deck { position: relative; margin: 0 14px 14px 0; }
.fc-panel-deck::before, .fc-panel-deck::after { content: ''; position: absolute; inset: 0; border-radius: 16px; background: var(--app-surface); border: 1px solid var(--app-border); }
.fc-panel-deck::before { transform: translate(7px, 7px); opacity: .85; z-index: 0; }
.fc-panel-deck::after { transform: translate(14px, 14px); opacity: .55; z-index: -1; }
.fc-panel { position: relative; z-index: 1; background: var(--app-bg); border: 1px solid var(--app-border); border-radius: 16px; overflow: hidden; }

.fc-flip { perspective: 1400px; }
.fc-flip-inner { position: relative; width: 100%; height: min(52vh, 300px); transition: transform .55s cubic-bezier(.4,.15,.2,1); transform-style: preserve-3d; }
.fc-flip.is-flipped .fc-flip-inner { transform: rotateY(180deg); }
.fc-flip-face { position: absolute; inset: 0; overflow-y: auto; backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; padding: clamp(18px,4vw,26px); background: var(--app-bg); }
.fc-flip-face--front { cursor: pointer; border: none; text-align: inherit; width: 100%; }
.fc-flip-face--front:disabled { cursor: default; }
.fc-flip-back { transform: rotateY(180deg); }

.fc-flip-tag { align-self: flex-start; font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #0d9488; margin-bottom: 12px; }
[data-theme="dark"] .fc-flip-tag { color: #2dd4bf; }
.fc-flip-text { flex: 1; font-size: 1.05rem; color: var(--app-text); line-height: 1.55; margin: 0; text-align: left; }
.fc-flip-bottom { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--app-border); font-size: .78rem; }
.fc-flip-timer { display: inline-flex; align-items: center; gap: 5px; color: var(--app-muted); font-weight: 600; }
.fc-flip-hint { color: #0d9488; font-weight: 700; }
[data-theme="dark"] .fc-flip-hint { color: #2dd4bf; }

.fc-rate-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 14px; }
.fc-rate-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 6px; border-radius: 12px; border: 1px solid var(--app-border); background: var(--app-bg); font-size: .82rem; font-weight: 700; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
.fc-rate-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15,23,42,.12); }
.fc-rate-btn__num { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: .68rem; font-weight: 800; flex-shrink: 0; }
.fc-rate-btn--dificil { color: #dc2626; }
.fc-rate-btn--dificil .fc-rate-btn__num { background: rgba(220,38,38,.15); }
.fc-rate-btn--medio { color: #d97706; }
.fc-rate-btn--medio .fc-rate-btn__num { background: rgba(217,119,6,.15); }
.fc-rate-btn--facil { color: #0d9488; }
.fc-rate-btn--facil .fc-rate-btn__num { background: rgba(13,148,136,.15); }

.fc-sum { text-align: center; padding: 8px; }
.fc-sum__total { font-size: 2rem; font-weight: 800; color: #0d9488; margin: 10px 0; }
.fc-sum__grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin: 16px 0; }
.fc-sum__cell { background: var(--app-bg); border: 1px solid var(--app-border); border-radius: 12px; padding: 12px 6px; }
.fc-sum__cell strong { display: block; font-size: 1.1rem; }
</style>
@endpush

@section('content')
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
            <div class="fc-deck-card">
                <div class="fc-card">
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
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('modals')
<div class="modal fade fc-review-modal" id="fcReviewModal" tabindex="-1" aria-labelledby="fcReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div id="fcErrorBox" class="alert alert-danger d-none"></div>

                <div id="fcModalCardView">
                    <div class="fc-toprow">
                        <span class="fc-pill fc-pill--materia">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">layers</span>
                            <span id="fcModalMateria"></span>
                        </span>
                        <span class="fc-pill fc-pill--streak">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">local_fire_department</span>
                            <span id="fcModalStreak"></span>
                        </span>
                    </div>

                    <div class="fc-panel-deck">
                        <div class="fc-panel">
                            <div class="fc-flip" id="fcFlip">
                                <div class="fc-flip-inner">
                                    <button type="button" class="fc-flip-face fc-flip-face--front" id="fcFrontBtn">
                                        <span class="fc-flip-tag" id="fcFrenteTag"></span>
                                        <p class="fc-flip-text" id="fcFrenteText"></p>
                                        <div class="fc-flip-bottom">
                                            <span class="fc-flip-timer"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">schedule</span><span id="fcTimer">0s</span></span>
                                            <span class="fc-flip-hint" id="fcRevealHint"></span>
                                        </div>
                                    </button>
                                    <div class="fc-flip-face fc-flip-back">
                                        <span class="fc-flip-tag">{{ __('flashcards.review.reveal_button') }}</span>
                                        <p class="fc-flip-text" id="fcVersoText"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fc-rate-grid d-none" id="fcRateGrid">
                        <button type="button" class="fc-rate-btn fc-rate-btn--dificil" data-avaliacao="dificil"><span class="fc-rate-btn__num">1</span>{{ __('flashcards.review.rate_dificil') }}</button>
                        <button type="button" class="fc-rate-btn fc-rate-btn--medio" data-avaliacao="medio"><span class="fc-rate-btn__num">2</span>{{ __('flashcards.review.rate_medio') }}</button>
                        <button type="button" class="fc-rate-btn fc-rate-btn--facil" data-avaliacao="facil"><span class="fc-rate-btn__num">3</span>{{ __('flashcards.review.rate_facil') }}</button>
                    </div>
                </div>

                <div id="fcModalSummaryView" class="d-none fc-sum">
                    <h2 class="h5 fw-bold mb-0">{{ __('flashcards.summary.title') }}</h2>
                    <div class="fc-sum__total" id="fcSumTotal"></div>
                    <div class="fc-sum__grid">
                        <div class="fc-sum__cell"><strong style="color:#dc2626;" id="fcSumDificil">0</strong>{{ __('flashcards.summary.breakdown_dificil') }}</div>
                        <div class="fc-sum__cell"><strong style="color:#d97706;" id="fcSumMedio">0</strong>{{ __('flashcards.summary.breakdown_medio') }}</div>
                        <div class="fc-sum__cell"><strong style="color:#0d9488;" id="fcSumFacil">0</strong>{{ __('flashcards.summary.breakdown_facil') }}</div>
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
    var frenteTag = document.getElementById('fcFrenteTag');
    var frenteText = document.getElementById('fcFrenteText');
    var versoText = document.getElementById('fcVersoText');
    var timerEl = document.getElementById('fcTimer');
    var rateGrid = document.getElementById('fcRateGrid');
    var errorBox = document.getElementById('fcErrorBox');
    var materiaLbl = document.getElementById('fcModalMateria');
    var streakLbl = document.getElementById('fcModalStreak');
    var defaultTag = @json(__('flashcards.review.default_tag'));
    var revealBtnLabel = @json(__('flashcards.review.reveal_button'));
    var intervalNewLabel = @json(__('flashcards.review.interval_new'));
    var intervalDaysTpl = @json(__('flashcards.review.interval_days', ['n' => '__N__']));
    var streakTpl = @json(__('flashcards.review.streak_days', ['n' => '__N__']));

    var timerHandle = null;
    var timerStart = 0;

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

    function stopTimer() {
        if (timerHandle) { clearInterval(timerHandle); timerHandle = null; }
    }

    function startTimer() {
        stopTimer();
        timerStart = Date.now();
        timerEl.textContent = '0s';
        timerHandle = setInterval(function () {
            timerEl.textContent = Math.floor((Date.now() - timerStart) / 1000) + 's';
        }, 1000);
    }

    function renderCard(data) {
        errorBox.classList.add('d-none');
        cardView.classList.remove('d-none');
        summaryView.classList.add('d-none');
        materiaLbl.textContent = data.materia_nome + ' · Q' + data.numero;
        streakLbl.textContent = streakTpl.replace('__N__', data.streak_dias);
        frenteTag.textContent = data.tema || defaultTag;
        frenteText.textContent = data.frente;
        revealHint.textContent = (data.intervalo_atual ? intervalDaysTpl.replace('__N__', data.intervalo_atual) : intervalNewLabel) + ' · ' + revealBtnLabel;

        if (data.revelado) {
            stopTimer();
            versoText.textContent = data.verso;
            flip.classList.add('is-flipped');
            rateGrid.classList.remove('d-none');
            frontBtn.disabled = true;
        } else {
            startTimer();
            flip.classList.remove('is-flipped');
            rateGrid.classList.add('d-none');
            frontBtn.disabled = false;
        }
    }

    function renderSummary(data) {
        stopTimer();
        cardView.classList.add('d-none');
        summaryView.classList.remove('d-none');
        document.getElementById('fcSumTotal').textContent = @json(__('flashcards.summary.total_reviewed', ['n' => '__N__'])).replace('__N__', data.total);
        document.getElementById('fcSumDificil').textContent = data.contagem.dificil;
        document.getElementById('fcSumMedio').textContent = data.contagem.medio;
        document.getElementById('fcSumFacil').textContent = data.contagem.facil;
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
        stopTimer();
        if (reviewedAny) {
            location.reload();
        }
    });
})();
</script>
@endpush
