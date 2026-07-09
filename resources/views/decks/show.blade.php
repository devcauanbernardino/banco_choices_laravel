@extends('layouts.app')

@section('title', $deck->nome)
@section('mobile_title', $deck->nome)
@section('topbar_title', $deck->nome)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shared-select.css') }}?v={{ @filemtime(public_path('assets/css/shared-select.css')) }}">
<style>
.dks-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
.dks-header h1 { font-size: clamp(1.3rem,2vw,1.6rem); font-weight: 700; color: var(--app-text); margin-bottom: 4px; }
.dks-header p { color: var(--app-muted); font-size: .88rem; margin: 0; }
.dks-header__actions { display: flex; gap: 8px; flex-shrink: 0; }
.dks-icon-btn { width: 38px; height: 38px; border-radius: 10px; border: 1px solid rgba(120,120,140,.2); background: rgba(255,255,255,.5); color: var(--app-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; }
[data-theme="dark"] .dks-icon-btn { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); }
.dks-icon-btn:hover { color: #8b1fb8; }
.dks-icon-btn--danger:hover { color: #dc2626; }
.dks-back { display: inline-flex; align-items: center; gap: 4px; color: var(--app-muted); font-size: .82rem; text-decoration: none; margin-bottom: 14px; }
.dks-back:hover { color: #8b1fb8; }

.dks-panel {
    border-radius: 18px;
    padding: 20px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
    margin-bottom: 20px;
}
[data-theme="dark"] .dks-panel { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }
.dks-panel h2 { font-size: .95rem; font-weight: 700; color: var(--app-text); margin-bottom: 14px; }

.dks-form-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: start; }
@media (max-width: 720px) { .dks-form-row { grid-template-columns: 1fr; } }
.dks-form-row textarea { background: rgba(255,255,255,.5); border: 1px solid rgba(120,120,140,.25); border-radius: 10px; padding: 8px 10px; font-size: .86rem; color: var(--app-text); resize: vertical; min-height: 44px; }
[data-theme="dark"] .dks-form-row textarea { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); }
.dks-add-btn { padding: 9px 18px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); white-space: nowrap; }

.dks-cards { display: flex; flex-direction: column; gap: 10px; }
.dks-carta { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: start; padding: 12px; border-radius: 12px; background: rgba(255,255,255,.35); border: 1px solid rgba(120,120,140,.14); }
[data-theme="dark"] .dks-carta { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
@media (max-width: 720px) { .dks-carta { grid-template-columns: 1fr; } }
.dks-carta__text { font-size: .84rem; color: var(--app-text); white-space: pre-wrap; }
.dks-carta__label { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8b1fb8; margin-bottom: 4px; display: block; }
[data-theme="dark"] .dks-carta__label { color: #c77dfd; }
.dks-carta__actions { display: flex; gap: 6px; align-self: center; }
.dks-empty-cards { text-align: center; padding: 30px; color: var(--app-muted); font-size: .86rem; }

/* Modal de revisão — mesmo padrão visual dos flashcards */
.dk-review-modal .modal-content { border-radius: 22px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.75); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); box-shadow: 0 25px 60px rgba(31,10,60,.18); }
[data-theme="dark"] .dk-review-modal .modal-content { background: rgba(30,20,40,.85); border-color: rgba(255,255,255,.1); box-shadow: 0 25px 60px rgba(0,0,0,.55); }
.dk-review-modal .modal-header { display: none; }
.dk-review-modal .modal-body { padding: 20px; }

/* Modais de editar/compartilhar deck */
.modal .modal-content { border-radius: 22px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.85); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); box-shadow: 0 25px 60px rgba(31,10,60,.18); }
[data-theme="dark"] .modal .modal-content { background: rgba(30,20,40,.9); border-color: rgba(255,255,255,.1); box-shadow: 0 25px 60px rgba(0,0,0,.55); }
.modal .form-control,
.modal .bc-styled-select.bc-styled-select__toggle { background: rgba(255,255,255,.5) !important; border: 1px solid rgba(120,120,140,.25) !important; color: var(--app-text) !important; }
[data-theme="dark"] .modal .form-control,
[data-theme="dark"] .modal .bc-styled-select.bc-styled-select__toggle { background: rgba(255,255,255,.06) !important; border-color: rgba(255,255,255,.14) !important; color: var(--app-text) !important; }
.modal .bc-styled-select.bc-styled-select__toggle:focus,
.modal .form-control:focus { border-color: #8b1fb8 !important; box-shadow: 0 0 0 .2rem rgba(139,31,184,.2); }
.modal .form-check-input:checked { background-color: #8b1fb8; border-color: #8b1fb8; }
.dk-toprow { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
.dk-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: .76rem; font-weight: 700; white-space: nowrap; background: rgba(139,31,184,.14); color: #8b1fb8; }
[data-theme="dark"] .dk-pill { background: rgba(199,125,253,.16); color: #c77dfd; }
.dk-flip { perspective: 1000px; }
.dk-flip-inner { position: relative; width: 100%; height: min(52vh, 300px); transition: transform .6s cubic-bezier(.4,.15,.2,1); transform-style: preserve-3d; will-change: transform; }
.dk-flip.is-flipped .dk-flip-inner { transform: rotateY(180deg); }
.dk-flip-face { position: absolute; inset: 0; overflow-y: auto; backface-visibility: hidden; -webkit-backface-visibility: hidden; display: flex; flex-direction: column; padding: clamp(18px,4vw,26px); border-radius: 18px; background: rgba(255,255,255,.5); border: 1px solid rgba(255,255,255,.55); box-shadow: 0 8px 22px rgba(31,10,60,.08); }
[data-theme="dark"] .dk-flip-face { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 22px rgba(0,0,0,.35); }
.dk-flip-face--front { cursor: pointer; border-color: rgba(255,255,255,.55); text-align: inherit; width: 100%; color: inherit; }
.dk-flip-face--front:disabled { cursor: default; }
.dk-flip-back { transform: rotateY(180deg); }
.dk-flip-tag { align-self: flex-start; font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8b1fb8; margin-bottom: 12px; }
[data-theme="dark"] .dk-flip-tag { color: #c77dfd; }
.dk-flip-text { flex: 1; font-size: 1.05rem; color: var(--app-text); line-height: 1.55; margin: 0; text-align: left; white-space: pre-wrap; }
.dk-flip-bottom { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(120,120,140,.18); font-size: .78rem; }
.dk-flip-timer { display: inline-flex; align-items: center; gap: 5px; color: var(--app-muted); font-weight: 600; }
.dk-flip-hint { color: #8b1fb8; font-weight: 700; }
[data-theme="dark"] .dk-flip-hint { color: #c77dfd; }
.dk-rate-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px; }
.dk-rate-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 6px; border-radius: 12px; border: 1px solid rgba(120,120,140,.2); background: rgba(255,255,255,.4); font-size: .82rem; font-weight: 700; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
[data-theme="dark"] .dk-rate-btn { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.12); }
.dk-rate-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15,23,42,.12); }
.dk-rate-btn__num { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: .68rem; font-weight: 800; flex-shrink: 0; }
.dk-rate-btn--dificil { color: #dc2626; }
.dk-rate-btn--dificil .dk-rate-btn__num { background: rgba(220,38,38,.15); }
.dk-rate-btn--medio { color: #d97706; }
.dk-rate-btn--medio .dk-rate-btn__num { background: rgba(217,119,6,.15); }
.dk-rate-btn--facil { color: #0d9488; }
.dk-rate-btn--facil .dk-rate-btn__num { background: rgba(13,148,136,.15); }
.dk-sum { text-align: center; padding: 8px; }
.dk-sum__total { font-size: 2rem; font-weight: 800; color: #8b1fb8; margin: 10px 0; }
[data-theme="dark"] .dk-sum__total { color: #c77dfd; }
.dk-sum__grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin: 16px 0; }
.dk-sum__cell { background: rgba(255,255,255,.4); border: 1px solid rgba(120,120,140,.18); border-radius: 12px; padding: 12px 6px; }
[data-theme="dark"] .dk-sum__cell { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); }
.dk-sum__cell strong { display: block; font-size: 1.1rem; }
.dk-sum__btn { padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; }
</style>
@endpush

@section('content')
<a href="{{ route('decks.index') }}" class="dks-back">
    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_back</span>
    {{ __('decks.form.back_to_list') }}
</a>

<div class="dks-header">
    <div>
        <h1>{{ $deck->nome }}</h1>
        @if ($deck->descricao)
            <p>{{ $deck->descricao }}</p>
        @endif
    </div>
    <div class="dks-header__actions">
        @if ($deck->compartilhado)
            <span class="dk-pill" style="align-self:center;">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">share</span>
                {{ __('decks.form.shared_badge') }}
            </span>
            <form method="POST" action="{{ route('decks.unshare', $deck) }}">
                @csrf
                <button type="submit" class="dks-icon-btn" aria-label="{{ __('decks.form.unshare') }}">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">link_off</span>
                </button>
            </form>
        @else
            <button type="button" class="dks-icon-btn" data-bs-toggle="modal" data-bs-target="#dksShareModal" aria-label="{{ __('decks.form.share') }}">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">share</span>
            </button>
        @endif
        <button type="button" class="dks-icon-btn" data-bs-toggle="modal" data-bs-target="#dksEditModal" aria-label="{{ __('decks.form.edit_deck') }}">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">edit</span>
        </button>
        <form method="POST" action="{{ route('decks.destroy', $deck) }}" onsubmit="return confirm(@json(__('decks.form.confirm_delete_deck')));">
            @csrf @method('DELETE')
            <button type="submit" class="dks-icon-btn dks-icon-btn--danger" aria-label="{{ __('decks.form.delete_deck') }}">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">delete</span>
            </button>
        </form>
    </div>
</div>

<div class="dks-panel">
    <h2>{{ __('decks.form.add_card') }}</h2>
    <form method="POST" action="{{ route('decks.cartas.store', $deck) }}">
        @csrf
        <div class="dks-form-row">
            <textarea name="frente" placeholder="{{ __('decks.form.front_placeholder') }}" required maxlength="2000"></textarea>
            <textarea name="verso" placeholder="{{ __('decks.form.back_placeholder') }}" required maxlength="2000"></textarea>
            <button type="submit" class="dks-add-btn">{{ __('decks.form.add') }}</button>
        </div>
    </form>
</div>

<div class="dks-panel">
    <h2>{{ __('decks.form.cards_count', ['n' => $deck->cartas->count()]) }}</h2>
    @if ($deck->cartas->isEmpty())
        <p class="dks-empty-cards">{{ __('decks.no_cards_yet') }}</p>
    @else
        <div class="dks-cards">
            @foreach ($deck->cartas as $c)
                <div class="dks-carta">
                    <div>
                        <span class="dks-carta__label">{{ __('decks.form.front_label') }}</span>
                        <div class="dks-carta__text">{{ $c->frente }}</div>
                    </div>
                    <div>
                        <span class="dks-carta__label">{{ __('decks.form.back_label') }}</span>
                        <div class="dks-carta__text">{{ $c->verso }}</div>
                    </div>
                    <div class="dks-carta__actions">
                        <form method="POST" action="{{ route('decks.cartas.destroy', [$deck, $c]) }}" onsubmit="return confirm(@json(__('decks.form.confirm_delete_card')));">
                            @csrf @method('DELETE')
                            <button type="submit" class="dks-icon-btn dks-icon-btn--danger" aria-label="{{ __('decks.form.delete_card') }}">
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('modals')
<div class="modal fade" id="dksEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-3">{{ __('decks.form.edit_deck') }}</h2>
                <form method="POST" action="{{ route('decks.update', $deck) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.name_label') }}</label>
                        <input type="text" name="nome" class="form-control" maxlength="120" value="{{ $deck->nome }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">{{ __('decks.form.desc_label') }}</label>
                        <textarea name="descricao" class="form-control" rows="2" maxlength="255">{{ $deck->descricao }}</textarea>
                    </div>
                    <button type="submit" class="dks-add-btn w-100">{{ __('decks.form.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if (! $deck->compartilhado)
<div class="modal fade" id="dksShareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-2">{{ __('decks.form.share') }}</h2>
                <p class="small text-muted mb-3">{{ __('decks.form.share_hint') }}</p>
                <form method="POST" action="{{ route('decks.share', $deck) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.subject_label') }}</label>
                        <select name="materia_id" class="bc-styled-select bc-styled-select--fluid" required>
                            <option value="" disabled {{ $deck->materia_id ? '' : 'selected' }}>{{ __('decks.form.subject_choose') }}</option>
                            @foreach (auth()->user()->materiasUnicas() as $m)
                                <option value="{{ $m->id }}" {{ (int) $deck->materia_id === (int) $m->id ? 'selected' : '' }}>{{ $m->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="dksConfirmaDireitos" name="confirma_direitos" required>
                        <label class="form-check-label small" for="dksConfirmaDireitos">{{ __('decks.form.confirm_rights') }}</label>
                    </div>
                    <button type="submit" class="dks-add-btn w-100">{{ __('decks.form.share_confirm') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade dk-review-modal" id="dkReviewModal" tabindex="-1" aria-labelledby="dkReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div id="dkErrorBox" class="alert alert-danger d-none"></div>

                <div id="dkModalCardView">
                    <div class="dk-toprow">
                        <span class="dk-pill">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">stacks</span>
                            <span id="dkModalDeck"></span>
                        </span>
                    </div>

                    <div class="dk-flip" id="dkFlip">
                        <div class="dk-flip-inner">
                            <button type="button" class="dk-flip-face dk-flip-face--front" id="dkFrontBtn">
                                <span class="dk-flip-tag">{{ __('decks.review.front_tag') }}</span>
                                <p class="dk-flip-text" id="dkFrenteText"></p>
                                <div class="dk-flip-bottom">
                                    <span class="dk-flip-timer"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem;">schedule</span><span id="dkTimer">0s</span></span>
                                    <span class="dk-flip-hint" id="dkRevealHint"></span>
                                </div>
                            </button>
                            <div class="dk-flip-face dk-flip-back">
                                <span class="dk-flip-tag">{{ __('decks.review.reveal_button') }}</span>
                                <p class="dk-flip-text" id="dkVersoText"></p>
                            </div>
                        </div>
                    </div>

                    <div class="dk-rate-grid d-none" id="dkRateGrid">
                        <button type="button" class="dk-rate-btn dk-rate-btn--dificil" data-avaliacao="dificil"><span class="dk-rate-btn__num">1</span>{{ __('decks.review.rate_dificil') }}</button>
                        <button type="button" class="dk-rate-btn dk-rate-btn--medio" data-avaliacao="medio"><span class="dk-rate-btn__num">2</span>{{ __('decks.review.rate_medio') }}</button>
                        <button type="button" class="dk-rate-btn dk-rate-btn--facil" data-avaliacao="facil"><span class="dk-rate-btn__num">3</span>{{ __('decks.review.rate_facil') }}</button>
                    </div>
                </div>

                <div id="dkModalSummaryView" class="d-none dk-sum">
                    <h2 class="h5 fw-bold mb-0">{{ __('decks.summary.title') }}</h2>
                    <div class="dk-sum__total" id="dkSumTotal"></div>
                    <div class="dk-sum__grid">
                        <div class="dk-sum__cell"><strong style="color:#dc2626;" id="dkSumDificil">0</strong>{{ __('decks.summary.breakdown_dificil') }}</div>
                        <div class="dk-sum__cell"><strong style="color:#d97706;" id="dkSumMedio">0</strong>{{ __('decks.summary.breakdown_medio') }}</div>
                        <div class="dk-sum__cell"><strong style="color:#0d9488;" id="dkSumFacil">0</strong>{{ __('decks.summary.breakdown_facil') }}</div>
                    </div>
                    <button type="button" class="dk-sum__btn" data-bs-dismiss="modal">{{ __('decks.summary.cta_back') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}" defer></script>
<script>
(function () {
    var modalEl = document.getElementById('dkReviewModal');
    if (!modalEl) return;
    var modal = null;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var reviewedAny = false;

    var cardView = document.getElementById('dkModalCardView');
    var summaryView = document.getElementById('dkModalSummaryView');
    var flip = document.getElementById('dkFlip');
    var frontBtn = document.getElementById('dkFrontBtn');
    var revealHint = document.getElementById('dkRevealHint');
    var frenteText = document.getElementById('dkFrenteText');
    var versoText = document.getElementById('dkVersoText');
    var timerEl = document.getElementById('dkTimer');
    var rateGrid = document.getElementById('dkRateGrid');
    var errorBox = document.getElementById('dkErrorBox');
    var deckLbl = document.getElementById('dkModalDeck');
    var revealBtnLabel = @json(__('decks.review.reveal_button'));
    var intervalNewLabel = @json(__('decks.review.interval_new'));
    var intervalDaysTpl = @json(__('decks.review.interval_days', ['n' => '__N__']));

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
        deckLbl.textContent = data.deck_nome + ' · ' + data.numero + '/' + data.total;
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
        document.getElementById('dkSumTotal').textContent = @json(__('decks.summary.total_reviewed', ['n' => '__N__'])).replace('__N__', data.total);
        document.getElementById('dkSumDificil').textContent = data.contagem.dificil;
        document.getElementById('dkSumMedio').textContent = data.contagem.medio;
        document.getElementById('dkSumFacil').textContent = data.contagem.facil;
    }

    function startReview(deckId, novosPorDia) {
        fetch('{{ route('decks.revisar.iniciar') }}', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ deck: deckId, novos_por_dia: novosPorDia })
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
        fetch('{{ route('decks.revisar.process') }}', {
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
            fetch('{{ route('decks.revisar.process') }}', {
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

    modalEl.addEventListener('hidden.bs.modal', function () {
        stopTimer();
        if (reviewedAny) {
            location.reload();
        }
    });

    var params = new URLSearchParams(window.location.search);
    if (params.get('revisar') === '1') {
        startReview({{ $deck->id }}, parseInt(params.get('novos_por_dia'), 10) || 20);
        history.replaceState(null, '', window.location.pathname);
    }
})();
</script>
@endpush
