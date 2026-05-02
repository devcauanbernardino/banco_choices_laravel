@extends('layouts.public')

@section('title', __('demo.page_title'))

@section('body_attr')
 class="demo-show-public-page bg-body"
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/page-banco-config.css') }}">
<style>
    .demo-fac-pick-btn {
        cursor: pointer;
        border: none;
        padding: 0;
        margin: 0;
        width: 100%;
        background: transparent;
        text-align: inherit;
        font: inherit;
    }
    .demo-fac-pick-btn:focus-visible .bc-mock-subject-card__box {
        outline: 2px solid var(--bc-mock-primary-ct);
        outline-offset: 2px;
    }
    .demo-fac-pick-btn.is-selected .bc-mock-subject-card__box {
        border-color: var(--bc-mock-primary-ct);
        box-shadow: 0 4px 16px rgba(106, 3, 146, 0.12);
        background: var(--app-surface, #fff);
    }
    .demo-fac-pick-btn.is-selected .bc-mock-subject-card__ico {
        color: var(--bc-mock-primary-ct);
        font-variation-settings: "FILL" 1;
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <div class="demo-show-public-actions d-flex flex-wrap align-items-center justify-content-between gap-3 pb-4 mb-1 border-bottom border-secondary border-opacity-10">
        <a href="{{ route('home') }}" class="small link-secondary text-decoration-none d-inline-flex align-items-center gap-1 fw-semibold">
            <span class="material-symbols-outlined fs-6" aria-hidden="true">arrow_back</span>
            {{ __('demo.back_home') }}
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-md-auto">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </div>
<div class="bc-mock-banco-page bc-mock-page-shell">
    <div class="bc-mock-banco-page__glow" aria-hidden="true"></div>
    <div class="bc-mock-banco-page__inner">
        <header class="bc-mock-editorial">
            @php
                $demoTitleWords = preg_split('/\s+/u', trim((string) __('demo.heading')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $demoTitleLast = count($demoTitleWords) > 1 ? array_pop($demoTitleWords) : null;
                $demoTitleRest = $demoTitleLast !== null ? implode(' ', $demoTitleWords) : (string) __('demo.heading');
            @endphp
            <h1 class="bc-mock-editorial__title">
                @if ($demoTitleLast !== null)
                    {{ $demoTitleRest }} <span class="bc-mock-editorial__accent">{{ $demoTitleLast }}</span>
                @else
                    {{ $demoTitleRest }}
                @endif
            </h1>
            <p class="bc-mock-editorial__lead">{{ __('demo.lead') }}</p>
        </header>

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">{{ session('error') }}</div>
        @endif

        @if ($faculdades->isEmpty())
            <section class="bc-mock-panel">
                <div class="bc-mock-panel__head mb-4">
                    <h2 class="bc-mock-panel__title mb-0">{{ __('demo.catalog.empty_panel_title') }}</h2>
                </div>
                <div class="d-flex flex-column align-items-center text-center gap-4 py-md-3">
                    <span class="material-symbols-outlined text-secondary-emphasis mb-2" style="font-size: 3.25rem; opacity:.65;" aria-hidden="true">
                        stacks
                    </span>
                    <p class="text-secondary mb-0 col-12 col-md-10">{{ __('demo.no_catalog') }}</p>
                    <a href="{{ route('signup.materias') }}" class="bc-mock-btn-start text-decoration-none">
                        <span class="material-symbols-outlined" aria-hidden="true">add_shopping_cart</span>
                        {{ __('demo.go_signup') }}
                    </a>
                </div>
            </section>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <form method="post" action="{{ route('demo.iniciar') }}" id="demoStartForm">
                        @csrf
                        <input type="hidden" id="demo_hidden_fac" value="" autocomplete="off">
                        <input type="hidden" name="materia_id" id="demo_hidden_materia" value="">
                        <input type="hidden" name="catedra_id" id="demo_hidden_catedra" value="">

                        <section class="bc-mock-panel">
                            <div class="bc-mock-panel__head">
                                <h2 class="bc-mock-panel__title">{{ __('demo.catalog.pick_faculdade') }}</h2>
                                <span class="bc-mock-pill">{{ __('demo.catalog.pick_faculdade_hint') }}</span>
                            </div>
                            <p id="demo_fac_label" class="visually-hidden">{{ __('demo.catalog.pick_faculdade') }}</p>
                            <div class="bc-mock-subject-grid mb-4" role="group" aria-labelledby="demo_fac_label">
                                @foreach ($faculdades as $fac)
                                    <button type="button"
                                            class="demo-fac-pick-btn bc-mock-subject-card"
                                            data-demo-fac="{{ $fac->id }}"
                                            aria-pressed="false">
                                        <span class="bc-mock-subject-card__box w-100 text-start px-4 py-3 align-items-start">
                                            <span class="material-symbols-outlined bc-mock-subject-card__ico" aria-hidden="true">
                                                apartment
                                            </span>
                                            <span class="bc-mock-subject-card__label">{{ $fac->nome }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </section>

                        <section class="bc-mock-panel mb-4">
                            <h2 class="bc-mock-panel__title mb-4">{{ __('demo.section_catalog_choice') }}</h2>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="demo_sel_agr">{{ __('demo.catalog.pick_agrupamiento') }}</label>
                                <select class="form-select" id="demo_sel_agr" disabled required></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="demo_sel_mat">{{ __('demo.catalog.pick_materia') }}</label>
                                <select class="form-select" id="demo_sel_mat" disabled required></select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold" for="demo_sel_cat">{{ __('demo.catalog.label_catedra') }}</label>
                                <select class="form-select" id="demo_sel_cat" disabled></select>
                                <div id="demo_cat_hint" class="form-text small d-none">{{ __('demo.catalog.catedra_obrigatoria') }}</div>
                            </div>
                        </section>

                        <button type="submit" class="bc-mock-btn-start mb-4" id="demo_btn_start">
                            <span class="material-symbols-outlined" aria-hidden="true">play_arrow</span>
                            {{ __('demo.btn_start') }}
                        </button>
                    </form>
                </div>

                <div class="col-lg-4 bc-mock-summary-col">
                    <div class="bc-mock-summary-sticky">
                        <div class="bc-mock-glass-card">
                            <h3 class="bc-mock-glass-card__title">{{ __('demo.sidebar_title') }}</h3>
                            <p class="small text-muted mb-4">{{ __('demo.sidebar_lead') }}</p>
                            <a href="{{ route('signup.materias') }}" class="bc-mock-btn-start text-decoration-none d-inline-flex w-100">
                                <span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
                                {{ __('demo.go_signup') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
            <script>
            (function () {
                var urls = {
                    agr: '{{ route('catalogo.public.agrupamentos') }}',
                    mat: '{{ route('catalogo.public.materias') }}',
                    cat: '{{ route('catalogo.public.catedras') }}',
                };
                var hidFac = document.getElementById('demo_hidden_fac');
                var facBtns = document.querySelectorAll('[data-demo-fac]');
                var selAgr = document.getElementById('demo_sel_agr');
                var selMat = document.getElementById('demo_sel_mat');
                var selCat = document.getElementById('demo_sel_cat');
                var hintCat = document.getElementById('demo_cat_hint');
                var hidM = document.getElementById('demo_hidden_materia');
                var hidC = document.getElementById('demo_hidden_catedra');
                var form = document.getElementById('demoStartForm');
                function setOpts(sel, items, placeholder) {
                    sel.innerHTML = '';
                    var o0 = document.createElement('option');
                    o0.value = '';
                    o0.textContent = placeholder;
                    sel.appendChild(o0);
                    (items || []).forEach(function (it) {
                        var o = document.createElement('option');
                        o.value = String(it.id);
                        o.textContent = it.nome;
                        if (typeof it.catedras_count !== 'undefined') {
                            o.setAttribute('data-catedras', String(it.catedras_count || 0));
                        }
                        sel.appendChild(o);
                    });
                }
                function fetchJson(url) {
                    return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
                        .then(function (r) { return r.ok ? r.json() : Promise.reject(new Error('http')); });
                }
                function markFacButtons(fid) {
                    facBtns.forEach(function (b) {
                        var sel = String(b.getAttribute('data-demo-fac')) === String(fid);
                        b.classList.toggle('is-selected', sel);
                        b.setAttribute('aria-pressed', sel ? 'true' : 'false');
                    });
                }
                function onFacChosen(fid) {
                    hidFac.value = fid ? String(fid) : '';
                    markFacButtons(fid || '');
                    selAgr.disabled = !fid;
                    selMat.disabled = true;
                    selCat.disabled = true;
                    selCat.innerHTML = '';
                    selCat.onchange = null;
                    if (hintCat) hintCat.classList.add('d-none');
                    hidM.value = '';
                    hidC.value = '';
                    setOpts(selAgr, [], '{{ __('demo.catalog.pick_agrupamiento') }}');
                    setOpts(selMat, [], '{{ __('demo.catalog.pick_materia') }}');
                    if (!fid) return;
                    fetchJson(urls.agr + '?faculdade_id=' + encodeURIComponent(fid)).then(function (j) {
                        setOpts(selAgr, j.data || [], '{{ __('demo.catalog.pick_agrupamiento') }}');
                        selAgr.disabled = false;
                    });
                }
                facBtns.forEach(function (b) {
                    b.addEventListener('click', function () {
                        onFacChosen(b.getAttribute('data-demo-fac'));
                    });
                });

                selAgr.addEventListener('change', function () {
                    selMat.disabled = !selAgr.value;
                    selCat.disabled = true;
                    selCat.innerHTML = '';
                    selCat.onchange = null;
                    if (hintCat) hintCat.classList.add('d-none');
                    hidM.value = '';
                    hidC.value = '';
                    setOpts(selMat, [], '{{ __('demo.catalog.pick_materia') }}');
                    if (!selAgr.value) return;
                    fetchJson(urls.mat + '?agrupamento_id=' + encodeURIComponent(selAgr.value)).then(function (j) {
                        setOpts(selMat, j.data || [], '{{ __('demo.catalog.pick_materia') }}');
                        selMat.disabled = false;
                    });
                });

                selMat.addEventListener('change', function () {
                    hidM.value = selMat.value || '';
                    hidC.value = '';
                    selCat.innerHTML = '';
                    selCat.onchange = null;
                    if (hintCat) hintCat.classList.add('d-none');
                    var opt = selMat.selectedOptions[0];
                    var nCat = opt ? parseInt(opt.getAttribute('data-catedras') || '0', 10) : 0;
                    if (!hidM.value) {
                        selCat.disabled = true;
                        return;
                    }
                    if (nCat < 1) {
                        selCat.disabled = true;
                        return;
                    }
                    fetchJson(urls.cat + '?materia_id=' + encodeURIComponent(hidM.value)).then(function (j) {
                        var rows = j.data || [];
                        if (!rows.length) return;
                        if (hintCat) hintCat.classList.remove('d-none');
                        selCat.disabled = false;
                        setOpts(selCat, rows, '{{ __('demo.catalog.pick_catedra_req') }}');
                        selCat.onchange = function () {
                            hidC.value = selCat.value || '';
                        };
                    });
                });

                form.addEventListener('submit', function (ev) {
                    if (!hidFac.value || !hidM.value) {
                        ev.preventDefault();
                        return;
                    }
                    var opt = selMat.selectedOptions[0];
                    var nCat = opt ? parseInt(opt.getAttribute('data-catedras') || '0', 10) : 0;
                    if (nCat > 0 && !hidC.value) {
                        ev.preventDefault();
                        alert({!! json_encode(__('demo.catalog.err_pick_catedra'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!});
                    }
                });
            })();
            </script>
            @endpush
        @endif

        <p class="bc-mock-footer-note mb-0">&copy; {{ date('Y') }} — {{ __('bank.footer_copy') }}</p>
    </div>
</div>
</div>
@endsection
