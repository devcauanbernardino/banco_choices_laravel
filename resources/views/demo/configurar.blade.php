@extends('layouts.public')

@section('title', __('demo.configurar.page_title'))
@section('body_attr', ' class="lp-body demo-body demo-body--funnel"')

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

@section('content')
    <section class="demo-funnel">
        <div class="lp-container">
            <div class="demo-funnel__toolbar">
                <a href="{{ route('demo.show') }}" class="demo-funnel__back">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    {{ __('demo.configurar.back') }}
                </a>
                @if($facultadAtiva)
                    <div class="demo-funnel__context">
                        <span class="demo-funnel__context-dot" aria-hidden="true"></span>
                        <span class="demo-funnel__context-label">{{ $facultadAtiva->nome }}</span>
                        <label class="visually-hidden" for="cfg_faculdade">{{ __('demo.configurar.switch_faculty') }}</label>
                        <select id="cfg_faculdade" class="demo-funnel__fac-select" name="fac_nav">
                            @foreach($faculdades as $f)
                                <option value="{{ $f->slug }}" @selected($facultadAtiva->id === $f->id)>{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            @if(session('error'))
                <div class="alert alert-danger demo-funnel__alert" role="alert">{{ session('error') }}</div>
            @endif

            <article class="demo-filter-card">
                <header class="demo-filter-card__head">
                    <div>
                        <h1 class="demo-filter-card__title">{{ __('demo.configurar.filter_title') }}</h1>
                        @if($facultadAtiva)
                            <p class="demo-filter-card__hint">{{ __('demo.configurar.filter_hint') }}</p>
                        @endif
                    </div>
                    @if($facultadAtiva)
                        <span class="lp-badge">{{ __('demo.configurar.badge_demo') }}</span>
                    @endif
                </header>

                @if(!$facultadAtiva)
                    <p class="demo-filter-card__empty">{{ __('demo.configurar.no_faculty') }}</p>
                @elseif(empty($comboMeta))
                    <p class="demo-filter-card__empty">{{ __('demo.configurar.empty_materias') }}</p>
                @else
                    <form method="post" action="{{ route('demo.iniciar') }}" id="demoFilterForm" novalidate>
                        @csrf
                        <input type="hidden" name="faculdade_slug" value="{{ $facultadAtiva->slug }}">
                        <input type="hidden" name="materia_id" id="cfg_materia_id" value="" required>
                        <input type="hidden" name="catedra_id" id="cfg_catedra_id" value="">
                        <input type="hidden" name="parcial" id="cfg_parcial" value="">

                        <div class="demo-filter-grid">
                            <div class="demo-filter-field">
                                <span class="demo-filter-field__lab">{{ __('demo.configurar.field_materia_uc') }}</span>
                                <div class="demo-dd" id="demoMateriaDd">
                                    <button type="button" class="demo-dd__btn" id="demoMateriaBtn" aria-expanded="false"
                                            aria-haspopup="listbox" aria-controls="demoMateriaList">
                                        <span class="demo-dd__btn-text" data-placeholder="{{ __('demo.configurar.materia_placeholder') }}">{{ __('demo.configurar.materia_placeholder') }}</span>
                                        <i class="bi bi-chevron-down demo-dd__chev" aria-hidden="true"></i>
                                    </button>
                                    <div class="demo-dd__panel" id="demoMateriaPanel" hidden>
                                        <input type="search" class="demo-dd__search" id="demoMateriaSearch"
                                               autocomplete="off"
                                               placeholder="{{ __('demo.configurar.materia_search_placeholder') }}"
                                               aria-label="{{ __('demo.configurar.materia_search_placeholder') }}">
                                        <ul class="demo-dd__list" id="demoMateriaList" role="listbox"></ul>
                                    </div>
                                </div>
                            </div>

                            <div class="demo-filter-field">
                                <span class="demo-filter-field__lab">{{ __('demo.configurar.field_temas_uc') }}</span>
                                <div class="demo-dd" id="demoTemaDd">
                                    <button type="button" class="demo-dd__btn" id="demoTemaBtn" disabled
                                            aria-expanded="false" aria-haspopup="listbox" aria-controls="demoTemaListLabel">
                                        <span class="demo-dd__btn-text" id="demoTemaBtnLabel">{{ __('demo.configurar.tema_pick_materia') }}</span>
                                        <i class="bi bi-chevron-down demo-dd__chev" aria-hidden="true"></i>
                                    </button>
                                    <div class="demo-dd__panel" id="demoTemaPanel" hidden>
                                        <span id="demoTemaListLabel" class="visually-hidden">{{ __('demo.configurar.field_temas') }}</span>
                                        <input type="search" class="demo-dd__search" id="demoTemaSearch"
                                               autocomplete="off"
                                               placeholder="{{ __('demo.configurar.tema_search_placeholder') }}"
                                               aria-label="{{ __('demo.configurar.tema_search_placeholder') }}"
                                               disabled>
                                        <label class="demo-tema-row demo-tema-row--all">
                                            <input type="checkbox" class="form-check-input demo-tema-check" id="demoTemaAll" checked disabled>
                                            <span class="demo-tema-row__name">{{ __('demo.configurar.tema_todos') }}</span>
                                            <span class="demo-tema-row__count" id="demoTemaAllCount">(0)</span>
                                        </label>
                                        <div class="demo-tema-scroll" id="demoTemaRows"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="demo-filter-field">
                                <label class="demo-filter-field__lab" for="cfg_orden">{{ __('demo.configurar.field_orden_uc') }}</label>
                                <select name="orden" id="cfg_orden" class="demo-select--funnel">
                                    <option value="random">{{ __('demo.configurar.orden_random') }}</option>
                                    <option value="banco">{{ __('demo.configurar.orden_banco') }}</option>
                                </select>
                            </div>
                        </div>

                        <footer class="demo-filter-card__footer">
                            <span id="demoAvailableCount" class="demo-filter-count" data-template="{{ __('demo.configurar.questions_available_n') }}"></span>
                            <button type="submit" class="btn lp-btn-primary lp-btn-lg" id="demoFilterSubmit" disabled>
                                <i class="bi bi-play-fill" aria-hidden="true"></i>
                                {{ __('demo.configurar.btn_filter') }}
                            </button>
                        </footer>
                    </form>
                @endif
            </article>
        </div>
    </section>
@endsection

@if($facultadAtiva && !empty($comboMeta))
@push('scripts')
<script>
(function () {
    var DEMO_MAX = {{ (int) ($demoMax ?? 5) }};
    var combos = @json($comboMeta);
    var tplCount = document.getElementById('demoAvailableCount').getAttribute('data-template');
    var STR_TEMA_TODOS = @json(__('demo.configurar.tema_todos'));
    var STR_TEMA_CUSTOM = @json(__('demo.configurar.tema_custom'));

    var materiaDd = document.getElementById('demoMateriaDd');
    var materiaBtn = document.getElementById('demoMateriaBtn');
    var materiaPanel = document.getElementById('demoMateriaPanel');
    var materiaSearch = document.getElementById('demoMateriaSearch');
    var materiaList = document.getElementById('demoMateriaList');

    var temaDd = document.getElementById('demoTemaDd');
    var temaBtn = document.getElementById('demoTemaBtn');
    var temaPanel = document.getElementById('demoTemaPanel');
    var temaSearch = document.getElementById('demoTemaSearch');
    var temaAll = document.getElementById('demoTemaAll');
    var temaAllCount = document.getElementById('demoTemaAllCount');
    var temaRows = document.getElementById('demoTemaRows');
    var temaBtnLabel = document.getElementById('demoTemaBtnLabel');

    var hidMat = document.getElementById('cfg_materia_id');
    var hidCat = document.getElementById('cfg_catedra_id');
    var hidPar = document.getElementById('cfg_parcial');
    var countEl = document.getElementById('demoAvailableCount');
    var submitBtn = document.getElementById('demoFilterSubmit');

    var selectedIdx = null;

    function normKey(s) {
        return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function closeAllDd(except) {
        document.querySelectorAll('.demo-dd').forEach(function (dd) {
            if (except && dd === except) return;
            var btn = dd.querySelector('.demo-dd__btn');
            var panel = dd.querySelector('.demo-dd__panel');
            if (btn && panel) {
                btn.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
            }
        });
    }

    function setMateriaButtonLabel(main, sub) {
        var ph = materiaBtn.querySelector('.demo-dd__btn-text').getAttribute('data-placeholder');
        var span = materiaBtn.querySelector('.demo-dd__btn-text');
        if (!main) {
            span.innerHTML = '';
            span.textContent = ph;
            return;
        }
        span.innerHTML = '';
        var t = document.createElement('span');
        t.className = 'demo-dd__picked-title';
        t.textContent = main;
        span.appendChild(t);
        if (sub) {
            var m = document.createElement('span');
            m.className = 'demo-dd__picked-meta';
            m.textContent = sub;
            span.appendChild(m);
        }
    }

    function renderMateriaOptions() {
        materiaList.innerHTML = '';
        combos.forEach(function (c) {
            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.className = 'demo-dd__option';
            li.setAttribute('data-index', String(c.index));
            li.setAttribute('data-match', normKey(c.label + ' ' + (c.subtitle || '') + ' ' + (c.agrupamento || '')));

            var badge = document.createElement('span');
            badge.className = 'demo-dd__opt-badge';
            badge.textContent = (c.agrupamento || '').trim() || '—';

            var mid = document.createElement('div');
            mid.className = 'demo-dd__opt-body';
            var t = document.createElement('span');
            t.className = 'demo-dd__opt-title';
            t.textContent = c.label;
            mid.appendChild(t);
            if (c.subtitle) {
                var sm = document.createElement('span');
                sm.className = 'demo-dd__opt-sub';
                sm.textContent = c.subtitle;
                mid.appendChild(sm);
            }

            var num = document.createElement('span');
            num.className = 'demo-dd__opt-n';
            num.textContent = '(' + c.total + ')';

            li.appendChild(badge);
            li.appendChild(mid);
            li.appendChild(num);
            li.addEventListener('click', function () {
                pickCombo(c.index);
                closeAllDd(null);
                materiaBtn.setAttribute('aria-expanded', 'false');
                materiaPanel.hidden = true;
            });
            materiaList.appendChild(li);
        });
        filterMateriaList();
    }

    function filterMateriaList() {
        var q = normKey(materiaSearch.value.trim());
        materiaList.querySelectorAll('.demo-dd__option').forEach(function (li) {
            var m = li.getAttribute('data-match') || '';
            li.classList.toggle('is-hidden', q !== '' && m.indexOf(q) === -1);
        });
    }

    function renderTemaRows(meta) {
        temaRows.innerHTML = '';
        (meta.temas || []).forEach(function (tm) {
            var row = document.createElement('label');
            row.className = 'demo-tema-row';
            row.setAttribute('data-match', normKey(tm.tema));

            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'form-check-input demo-tema-check';
            cb.name = 'temas[]';
            cb.value = tm.tema;
            cb.disabled = temaAll.checked;

            var nm = document.createElement('span');
            nm.className = 'demo-tema-row__name';
            nm.textContent = tm.tema;

            var cnt = document.createElement('span');
            cnt.className = 'demo-tema-row__count';
            cnt.textContent = '(' + tm.count + ')';

            row.appendChild(cb);
            row.appendChild(nm);
            row.appendChild(cnt);
            temaRows.appendChild(row);
        });
        temaSearch.disabled = (meta.temas || []).length === 0;
        filterTemaList();
    }

    function filterTemaList() {
        var q = normKey(temaSearch.value.trim());
        temaRows.querySelectorAll('.demo-tema-row').forEach(function (row) {
            var m = row.getAttribute('data-match') || '';
            row.classList.toggle('is-hidden', q !== '' && m.indexOf(q) === -1);
        });
    }

    function selectedCombo() {
        if (selectedIdx === null) return null;
        for (var i = 0; i < combos.length; i++) {
            if (combos[i].index === selectedIdx) {
                return combos[i];
            }
        }
        return null;
    }

    function computeEligibleCount() {
        var c = selectedCombo();
        if (!c) return 0;
        if (temaAll.checked) return c.total;
        var sum = 0;
        temaRows.querySelectorAll('input[name="temas[]"]').forEach(function (cb) {
            if (cb.checked) {
                var row = cb.closest('.demo-tema-row');
                var raw = (row.querySelector('.demo-tema-row__count') || {}).textContent || '(0)';
                var n = parseInt(raw.replace(/\D/g, ''), 10) || 0;
                sum += n;
            }
        });
        return sum;
    }

    function refreshCount() {
        var n = computeEligibleCount();
        var pack = Math.min(DEMO_MAX, n);
        countEl.textContent = tplCount.replace(':n', String(n)).replace(':pack', String(pack));
        submitBtn.disabled = n < 1 || selectedIdx === null;
    }

    function pickCombo(idx) {
        selectedIdx = idx;
        var c = selectedCombo();
        if (!c) return;
        hidMat.value = String(c.materia_id);
        hidCat.value = c.catedra_id ? String(c.catedra_id) : '';
        hidPar.value = c.parcial || '';
        setMateriaButtonLabel(c.label, c.subtitle);

        temaBtn.disabled = false;
        temaAll.checked = true;
        temaAll.disabled = false;
        temaBtnLabel.textContent = STR_TEMA_TODOS;
        temaAllCount.textContent = '(' + c.total + ')';
        renderTemaRows(c);
        temaRows.querySelectorAll('input[name="temas[]"]').forEach(function (cb) {
            cb.checked = false;
            cb.disabled = true;
        });
        refreshCount();
    }

    materiaBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var open = materiaPanel.hidden;
        closeAllDd(open ? materiaDd : null);
        materiaPanel.hidden = !open;
        materiaBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) materiaSearch.focus();
    });

    materiaSearch.addEventListener('input', filterMateriaList);

    temaBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (temaBtn.disabled) return;
        var open = temaPanel.hidden;
        closeAllDd(open ? temaDd : null);
        temaPanel.hidden = !open;
        temaBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open && !temaSearch.disabled) temaSearch.focus();
    });

    temaAll.addEventListener('change', function () {
        temaRows.querySelectorAll('input[name="temas[]"]').forEach(function (cb) {
            cb.disabled = temaAll.checked;
            if (temaAll.checked) cb.checked = false;
        });
        temaBtnLabel.textContent = temaAll.checked ? STR_TEMA_TODOS : STR_TEMA_CUSTOM;
        refreshCount();
    });

    temaRows.addEventListener('change', function (e) {
        var t = e.target;
        if (!t || !t.matches || !t.matches('input[name="temas[]"]')) return;
        if (t.checked) temaAll.checked = false;
        var any = false;
        temaRows.querySelectorAll('input[name="temas[]"]').forEach(function (cb) {
            if (cb.checked) any = true;
        });
        if (!any) temaAll.checked = true;
        temaRows.querySelectorAll('input[name="temas[]"]').forEach(function (cb) {
            cb.disabled = temaAll.checked;
        });
        temaBtnLabel.textContent = temaAll.checked ? STR_TEMA_TODOS : STR_TEMA_CUSTOM;
        refreshCount();
    });

    temaSearch.addEventListener('input', filterTemaList);

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.demo-dd')) closeAllDd(null);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllDd(null);
    });

    var selFac = document.getElementById('cfg_faculdade');
    if (selFac) {
        selFac.addEventListener('change', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('faculdade', selFac.value);
            window.location.href = url.toString();
        });
    }

    renderMateriaOptions();
    countEl.textContent = tplCount.replace(':n', '0').replace(':pack', '0');
})();
</script>
@endpush
@endif
