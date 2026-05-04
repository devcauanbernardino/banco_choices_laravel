@extends('layouts.public')

@section('title', __('demo.configurar.page_title'))
@section('body_attr', ' class="lp-body demo-body demo-body--topo"')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/landing-v2.css') }}?v={{ filemtime(public_path('assets/css/landing-v2.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}?v={{ filemtime(public_path('assets/css/demo.css')) }}">
@endpush

@section('public_topbar')
    @include('pages.partials.topbar')
@endsection

@section('public_offcanvas')
    @include('pages.partials.offcanvas-public')
@endsection

@section('public_footer')
    @include('pages.partials.footer')
@endsection

@php
    /** @var \Illuminate\Support\Collection $combinacoes */
    /** @var \Illuminate\Support\Collection $temasDisponiveis */
@endphp

@section('content')
    <section class="demo-section">
        <div class="lp-container">
            @if(session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif

            <article class="demo-config-card">
                <header class="demo-config-card__head">
                    <h1 class="demo-config-card__title">{{ __('demo.configurar.title') }}</h1>
                    <span class="demo-config-card__badge">{{ __('demo.configurar.badge_demo') }}</span>
                </header>

                <form method="post" action="{{ route('demo.iniciar') }}" id="demoConfigForm">
                    @csrf
                    <input type="hidden" name="materia_id" id="cfg_materia_id" value="">
                    <input type="hidden" name="catedra_id" id="cfg_catedra_id" value="">
                    <input type="hidden" name="parcial" id="cfg_parcial" value="">

                    {{-- Campo 1: Facultad --}}
                    <div class="demo-field">
                        <label class="demo-field__label" for="cfg_faculdade">{{ __('demo.configurar.field_facultad') }}</label>
                        <select class="demo-input demo-select" id="cfg_faculdade" name="faculdade">
                            @foreach($faculdades as $f)
                                <option value="{{ $f->slug }}" @selected($facultadAtiva && $facultadAtiva->id === $f->id)>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campo 2: Materias --}}
                    <div class="demo-field">
                        <label class="demo-field__label">{{ __('demo.configurar.field_materias') }}</label>
                        <div class="demo-list" id="cfg_materias_list" role="radiogroup">
                            @forelse($combinacoes as $i => $row)
                                @php
                                    $payload = json_encode([
                                        'materia_id' => (int) $row->materia_id,
                                        'catedra_id' => $row->catedra_id ? (int) $row->catedra_id : null,
                                        'parcial' => $row->parcial,
                                        'plano' => $row->plano,
                                    ], JSON_UNESCAPED_UNICODE);
                                    $metaParts = [];
                                    if (!empty($row->catedra_nome)) $metaParts[] = $row->catedra_nome;
                                    if (!empty($row->plano)) $metaParts[] = $row->plano;
                                    if (!empty($row->parcial)) $metaParts[] = __('demo.configurar.parcial_label', ['n' => $row->parcial]);
                                @endphp
                                <label class="demo-list__item" data-cfg-row="{{ $i }}">
                                    <input class="demo-list__radio" type="radio" name="cfg_combo" value="{{ $i }}"
                                           data-payload='{{ $payload }}'>
                                    <span class="demo-list__main">
                                        <span class="demo-list__title">{{ $row->materia_nome }}</span>
                                        @if(!empty($metaParts))
                                            <span class="demo-list__meta">{{ implode(' · ', $metaParts) }}</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <p class="demo-list__empty">{{ __('demo.configurar.empty_materias') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Campo 3: Temas --}}
                    <div class="demo-field">
                        <label class="demo-field__label">{{ __('demo.configurar.field_temas') }}</label>
                        <div class="demo-list demo-list--temas" id="cfg_temas_list">
                            <label class="demo-list__item demo-list__item--all">
                                <input class="demo-list__check" type="checkbox" id="cfg_tema_all" checked>
                                <span class="demo-list__main">
                                    <span class="demo-list__title demo-list__title--bold">
                                        {{ __('demo.configurar.tema_todos') }}
                                    </span>
                                </span>
                            </label>
                            @forelse($temasDisponiveis as $tema)
                                <label class="demo-list__item">
                                    <input class="demo-list__check" type="checkbox" name="temas[]"
                                           value="{{ $tema }}" disabled>
                                    <span class="demo-list__main">
                                        <span class="demo-list__title">{{ $tema }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="demo-list__empty">{{ __('demo.configurar.empty_temas') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Campo 4: Tempo --}}
                    <div class="demo-field demo-field--inline">
                        <label class="demo-field__label" for="cfg_tiempo">{{ __('demo.configurar.field_tiempo') }}</label>
                        <div class="demo-input-wrap">
                            <input class="demo-input demo-input--narrow" type="number" id="cfg_tiempo" value="5" readonly>
                            <span class="demo-input-suffix">{{ __('demo.configurar.minutes_unit') }}</span>
                        </div>
                    </div>

                    {{-- Campo 5: Cantidad --}}
                    <div class="demo-field demo-field--inline">
                        <label class="demo-field__label" for="cfg_cantidad">{{ __('demo.configurar.field_cantidad') }}</label>
                        <div class="demo-input-wrap" data-bs-toggle="tooltip" data-bs-placement="top"
                             title="{{ __('demo.configurar.cantidad_tooltip') }}">
                            <select class="demo-input demo-select demo-input--narrow" id="cfg_cantidad" disabled>
                                <option value="5" selected>5</option>
                            </select>
                        </div>
                    </div>

                    <div class="demo-config-card__actions">
                        <button type="submit" class="btn lp-btn-primary" id="cfg_submit" disabled>
                            {{ __('demo.configurar.btn_armar') }}
                        </button>
                        <button type="reset" class="btn lp-btn-ghost" id="cfg_reset">
                            {{ __('demo.configurar.btn_limpiar') }}
                        </button>
                    </div>
                </form>
            </article>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    var selFac = document.getElementById('cfg_faculdade');
    var radios = document.querySelectorAll('input[name="cfg_combo"]');
    var hiddenMat = document.getElementById('cfg_materia_id');
    var hiddenCat = document.getElementById('cfg_catedra_id');
    var hiddenPar = document.getElementById('cfg_parcial');
    var btnSubmit = document.getElementById('cfg_submit');
    var temaAll = document.getElementById('cfg_tema_all');
    var temaChecks = document.querySelectorAll('#cfg_temas_list input[name="temas[]"]');

    selFac.addEventListener('change', function () {
        var slug = selFac.value;
        var url = new URL(window.location.href);
        url.searchParams.set('faculdade', slug);
        window.location.href = url.toString();
    });

    function refreshSubmitState() {
        var selected = document.querySelector('input[name="cfg_combo"]:checked');
        btnSubmit.disabled = !selected;
    }

    radios.forEach(function (r) {
        r.addEventListener('change', function () {
            try {
                var p = JSON.parse(r.getAttribute('data-payload') || '{}');
                hiddenMat.value = p.materia_id || '';
                hiddenCat.value = p.catedra_id || '';
                hiddenPar.value = p.parcial || '';
            } catch (e) {
                hiddenMat.value = '';
                hiddenCat.value = '';
                hiddenPar.value = '';
            }
            document.querySelectorAll('#cfg_materias_list .demo-list__item').forEach(function (it) {
                it.classList.remove('is-selected');
            });
            r.closest('.demo-list__item').classList.add('is-selected');
            refreshSubmitState();
        });
    });

    function syncTemaAll() {
        var allChecked = temaAll.checked;
        temaChecks.forEach(function (c) {
            c.disabled = allChecked;
            if (allChecked) c.checked = false;
        });
    }
    if (temaAll) {
        temaAll.addEventListener('change', syncTemaAll);
        syncTemaAll();
    }

    var resetBtn = document.getElementById('cfg_reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            setTimeout(function () {
                hiddenMat.value = '';
                hiddenCat.value = '';
                hiddenPar.value = '';
                document.querySelectorAll('#cfg_materias_list .demo-list__item').forEach(function (it) {
                    it.classList.remove('is-selected');
                });
                if (temaAll) { temaAll.checked = true; syncTemaAll(); }
                refreshSubmitState();
            }, 0);
        });
    }

    // tooltips
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
})();
</script>
@endpush
