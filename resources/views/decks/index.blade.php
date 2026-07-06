@extends('layouts.app')

@section('title', __('decks.page_title'))
@section('mobile_title', __('decks.mobile_title'))
@section('topbar_title', __('decks.mobile_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shared-select.css') }}?v={{ @filemtime(public_path('assets/css/shared-select.css')) }}">
<style>
.dk-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 26px; }
.dk-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.dk-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }
.dk-new-btn { padding: 10px 18px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .86rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; }
.dk-new-btn--ghost { background: rgba(139,31,184,.1); color: #6a0392; box-shadow: none; border: 1px solid rgba(139,31,184,.25); }
.dk-new-btn--ghost:hover { background: rgba(139,31,184,.16); }
[data-theme="dark"] .dk-new-btn--ghost { background: rgba(199,125,253,.14); color: #e0bbfd; border-color: rgba(199,125,253,.3); }
[data-theme="dark"] .dk-new-btn--ghost:hover { background: rgba(199,125,253,.22); }

.dk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px; }

.dk-card {
    position: relative;
    border-radius: 18px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
    transition: transform .18s ease, box-shadow .18s ease;
}
.dk-card:hover { transform: translateY(-3px); box-shadow: 0 14px 34px rgba(31,10,60,.13); }
[data-theme="dark"] .dk-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }
[data-theme="dark"] .dk-card:hover { box-shadow: 0 14px 34px rgba(0,0,0,.5); }

.dk-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.dk-card__icon { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg,#8b1fb8,#6a0392); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 6px 16px rgba(106,3,146,.28); }
.dk-card__icon .material-symbols-outlined { color: #fff; font-size: 1.3rem; }
.dk-card__open { width: 32px; height: 32px; border-radius: 50%; border: 1px solid rgba(120,120,140,.2); background: transparent; color: var(--app-muted); display: flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0; }
.dk-card__open:hover { background: rgba(139,31,184,.1); color: #8b1fb8; }
.dk-card__title { font-size: 1rem; font-weight: 700; color: var(--app-text); margin: 0; }
.dk-card__desc { font-size: .82rem; color: var(--app-muted); margin: 0; line-height: 1.4; }

.dk-card__badges { display: flex; gap: 8px; flex-wrap: wrap; }
.dk-badge { font-size: .74rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }
.dk-badge--due { background: rgba(13,148,136,.14); color: #0d9488; }
[data-theme="dark"] .dk-badge--due { background: rgba(45,212,191,.16); color: #2dd4bf; }
.dk-badge--new { background: rgba(34,197,94,.16); color: #22c55e; }
.dk-badge--empty { background: rgba(120,120,140,.16); color: var(--app-muted); }

.dk-card__actions { margin-top: auto; display: flex; align-items: center; gap: 10px; }
.dk-stepper { display: flex; align-items: center; border: 1px solid rgba(255,255,255,.5); border-radius: 10px; background: rgba(255,255,255,.5); overflow: hidden; flex-shrink: 0; }
[data-theme="dark"] .dk-stepper { border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.04); }
.dk-stepper__btn { width: 30px; height: 34px; border: none; background: transparent; color: #8b1fb8; font-size: 1.05rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; }
.dk-stepper__btn:hover { background: rgba(139,31,184,.12); }
[data-theme="dark"] .dk-stepper__btn { color: #c77dfd; }
.dk-stepper__val { width: 34px; text-align: center; font-size: .86rem; font-weight: 700; color: var(--app-text); user-select: none; }
.dk-card__btn { flex: 1; padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.dk-card__btn:disabled { opacity: .4; cursor: default; box-shadow: none; }

.dk-empty { text-align: center; padding: 60px 20px; color: var(--app-muted); }
.dk-empty .material-symbols-outlined { font-size: 3rem; color: #8b1fb8; opacity: .5; margin-bottom: 12px; display: block; }

/* Modal criar deck */
.dk-modal .modal-content { border-radius: 22px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.85); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); box-shadow: 0 25px 60px rgba(31,10,60,.18); }
[data-theme="dark"] .dk-modal .modal-content { background: rgba(30,20,40,.9); border-color: rgba(255,255,255,.1); box-shadow: 0 25px 60px rgba(0,0,0,.55); }
.dk-modal .form-control,
.dk-modal .form-select { background: rgba(255,255,255,.5); border: 1px solid rgba(120,120,140,.25); color: var(--app-text); }
[data-theme="dark"] .dk-modal .form-control,
[data-theme="dark"] .dk-modal .form-select { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); color: var(--app-text); }
.dk-modal .form-select:focus,
.dk-modal .form-control:focus { border-color: #8b1fb8; box-shadow: 0 0 0 .2rem rgba(139,31,184,.2); }
.dk-modal .form-select option { background: #fff; color: #15131a; }
[data-theme="dark"] .dk-modal .form-select option { background: #241a30; color: #f1eaf7; }

a.dk-new-btn { text-decoration: none; }
a.dk-new-btn:hover { text-decoration: none; }
</style>
@endpush

@section('content')
<div class="dk-header">
    <div>
        <h1>{{ __('decks.header.title') }}</h1>
        <p>{{ __('decks.header.sub') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('decks.descobrir') }}" class="dk-new-btn dk-new-btn--ghost">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">explore</span>
            {{ __('decks.form.discover') }}
        </a>
        <button type="button" class="dk-new-btn dk-new-btn--ghost" data-bs-toggle="modal" data-bs-target="#dkImportModal">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">upload_file</span>
            {{ __('decks.form.import_anki') }}
        </button>
        <button type="button" class="dk-new-btn" data-bs-toggle="modal" data-bs-target="#dkCreateModal">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">add</span>
            {{ __('decks.form.new_deck') }}
        </button>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($decks->isEmpty())
    <div class="dk-empty">
        <span class="material-symbols-outlined" aria-hidden="true">workspaces</span>
        <p class="mb-0">{{ __('decks.no_decks') }}</p>
    </div>
@else
    <div class="dk-grid">
        @foreach ($decks as $d)
            @php $resumo = $resumoPorDeck[$d->id] ?? ['due_count' => 0, 'new_count' => 0, 'new_available_count' => 0]; @endphp
            <div class="dk-card">
                <div class="dk-card__top">
                    <div class="dk-card__icon"><span class="material-symbols-outlined" aria-hidden="true">workspaces</span></div>
                    <a href="{{ route('decks.show', $d) }}" class="dk-card__open" aria-label="{{ __('decks.form.manage') }}">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_outward</span>
                    </a>
                </div>
                <h3 class="dk-card__title">{{ $d->nome }}</h3>
                @if ($d->descricao)
                    <p class="dk-card__desc">{{ $d->descricao }}</p>
                @endif
                <div class="dk-card__badges">
                    @if ($d->cartas_count === 0)
                        <span class="dk-badge dk-badge--empty">{{ __('decks.card.no_cards') }}</span>
                    @else
                        @if ($resumo['due_count'] > 0)
                            <span class="dk-badge dk-badge--due">{{ __('decks.card.due_count', ['n' => $resumo['due_count']]) }}</span>
                        @endif
                        @if ($resumo['new_available_count'] > 0)
                            <span class="dk-badge dk-badge--new">{{ __('decks.card.new_count', ['n' => $resumo['new_available_count']]) }}</span>
                        @endif
                        @if ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0)
                            <span class="dk-badge dk-badge--empty">{{ __('decks.card.all_caught_up') }}</span>
                        @endif
                    @endif
                </div>
                <form class="dk-card__actions" data-dk-start>
                    <input type="hidden" name="deck" value="{{ $d->id }}">
                    <div class="dk-stepper">
                        <button type="button" class="dk-stepper__btn" data-dk-step="-1" aria-label="-">−</button>
                        <span class="dk-stepper__val" data-dk-val>20</span>
                        <button type="button" class="dk-stepper__btn" data-dk-step="1" aria-label="+">+</button>
                    </div>
                    <input type="hidden" name="novos_por_dia" value="20" data-dk-hidden>
                    <button type="submit" class="dk-card__btn" @if ($d->cartas_count === 0 || ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0)) disabled @endif>
                        {{ __('decks.form.start') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('modals')
<div class="modal fade dk-modal" id="dkCreateModal" tabindex="-1" aria-labelledby="dkCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-3">{{ __('decks.form.new_deck') }}</h2>
                <form method="POST" action="{{ route('decks.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.name_label') }}</label>
                        <input type="text" name="nome" class="form-control" maxlength="120" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.desc_label') }}</label>
                        <textarea name="descricao" class="form-control" rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">{{ __('decks.form.subject_label') }}</label>
                        <select name="materia_id" class="bc-styled-select bc-styled-select--fluid form-select">
                            <option value="">{{ __('decks.form.subject_none') }}</option>
                            @foreach ($materiasUsuario as $m)
                                <option value="{{ $m->id }}">{{ $m->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="dk-card__btn w-100">{{ __('decks.form.create') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dk-modal" id="dkImportModal" tabindex="-1" aria-labelledby="dkImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-2">{{ __('decks.form.import_anki') }}</h2>
                <p class="small text-muted mb-3">{{ __('decks.form.import_anki_hint') }}</p>
                <form method="POST" action="{{ route('decks.import.anki') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.name_label') }}</label>
                        <input type="text" name="nome" class="form-control" maxlength="120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('decks.form.subject_label') }}</label>
                        <select name="materia_id" class="bc-styled-select bc-styled-select--fluid form-select">
                            <option value="">{{ __('decks.form.subject_none') }}</option>
                            @foreach ($materiasUsuario as $m)
                                <option value="{{ $m->id }}">{{ $m->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">{{ __('decks.form.apkg_label') }}</label>
                        <input type="file" name="arquivo" class="form-control" accept=".apkg,.zip" required>
                    </div>
                    <button type="submit" class="dk-card__btn w-100">{{ __('decks.form.import') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}" defer></script>
<script>
document.querySelectorAll('.dk-stepper').forEach(function (stepper) {
    var form = stepper.closest('form');
    var valEl = stepper.querySelector('[data-dk-val]');
    var hidden = form.querySelector('[data-dk-hidden]');
    var min = 0, max = 200;
    stepper.querySelectorAll('[data-dk-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var current = parseInt(hidden.value, 10) || 0;
            var next = current + parseInt(btn.dataset.dkStep, 10);
            next = Math.max(min, Math.min(max, next));
            hidden.value = next;
            valEl.textContent = next;
        });
    });
});

document.querySelectorAll('[data-dk-start]').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var deckId = form.querySelector('[name="deck"]').value;
        var novos = form.querySelector('[name="novos_por_dia"]').value;
        window.location.href = '{{ url("/decks") }}/' + deckId + '?revisar=1&novos_por_dia=' + encodeURIComponent(novos);
    });
});
</script>
@endpush
