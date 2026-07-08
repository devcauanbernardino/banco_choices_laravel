@push('scripts')
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}"></script>
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };

    var BC_TEMA_SENT = @json(\App\Services\Questions\QuestionExamBuilder::TEMA_FILTRO_SEM_ETIQUETA);
    var BC_TEMA_SENT_LBL = @json(__('bank.tema_sem_etiqueta'));

    function u(url, q) {
        return fetch(url + q, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }}).then(function (r) { return r.json(); });
    }

    function refreshSel(sel) {
        if (!sel || typeof window.bcRefreshStyledSelect !== 'function') return;
        window.bcRefreshStyledSelect(sel);
    }

    function setSel(sel, rows, placeholder) {
        sel.innerHTML = '';
        var o0 = document.createElement('option');
        o0.value = ''; o0.textContent = placeholder; sel.appendChild(o0);
        (rows || []).forEach(function (row) {
            var o = document.createElement('option');
            o.value = row.id; o.textContent = row.nome; sel.appendChild(o);
        });
        refreshSel(sel);
    }

    var URLs = {
        cat: '{{ route('api.catalogo.catedras') }}',
        parc: '{{ route('api.catalogo.parciais') }}',
        tem: '{{ route('api.catalogo.temas') }}',
    };

    function loadFiltros() {
        $('qb_parciais').innerHTML = '';
        $('qb_temas').innerHTML = '';
        var mid = $('qb_materia_hidden').value;
        var cid = $('qb_catedra_hidden').value;
        if (!mid) return;
        var qc = cid ? '&catedra_id=' + encodeURIComponent(cid) : '';
        u(URLs.parc, '?materia_id=' + encodeURIComponent(mid) + qc).then(function (r) {
            var ps = r.data || [];
            var finals = !!r.hay_final_pool;
            var mapLbl = {'1':'{{ __('bank.parc.label_1') }}','2':'{{ __('bank.parc.label_2') }}','3':'{{ __('bank.parc.label_3') }}','final':'{{ __('bank.parc.final') }}','libre':'{{ __('bank.parc.libre') }}'};
            ps.forEach(function (p) {
                if (!p) return;
                var id = 'parc_' + String(p).replace(/\W+/g,'_');
                var wrap = document.createElement('div');
                wrap.className = 'form-check form-check-inline';
                wrap.innerHTML = '<input class="form-check-input" type="checkbox" name="parcial[]" value="'+p+'" id="'+id+'">' +
                    '<label class="form-check-label" for="'+id+'">'+(mapLbl[String(p)] || ('Parcial '+p))+'</label>';
                $('qb_parciais').appendChild(wrap);
            });
            if (finals) {
                var w = document.createElement('div');
                w.className = 'form-check form-check-inline';
                w.innerHTML = '<input class="form-check-input" type="checkbox" name="parcial[]" value="final" id="parc_fin">'+
                    '<label class="form-check-label" for="parc_fin">{{ __('bank.parc.final') }}</label>';
                $('qb_parciais').appendChild(w);
            }
        });
        u(URLs.tem, '?materia_id=' + encodeURIComponent(mid) + qc).then(function (t) {
            var rows = t.data || [];
            if (!rows.length) return;
            function normKey(s) {
                return String(s).toLowerCase().normalize('NFD').split('').filter(function (ch) {
                    var code = ch.codePointAt(0);
                    return code < 0x300 || code > 0x36f;
                }).join('');
            }

            var wrap = $('qb_temas');
            var lbl = document.createElement('span');
            lbl.className = 'form-label small d-block mb-1';
            lbl.textContent = '{{ __('bank.temas.label') }}';
            wrap.appendChild(lbl);

            var search = document.createElement('input');
            search.type = 'search';
            search.className = 'form-control form-control-sm mb-2';
            search.setAttribute('autocomplete', 'off');
            search.setAttribute('placeholder', '{{ __("bank.temas.search_placeholder") }}');
            search.setAttribute('aria-label', '{{ __("bank.temas.search_aria") }}');
            search.id = 'qb_tema_search';
            wrap.appendChild(search);

            var listBox = document.createElement('div');
            listBox.className = 'qb-tema-list rounded border px-2 py-2 mb-2';
            listBox.style.maxHeight = '220px';
            listBox.style.overflow = 'auto';
            wrap.appendChild(listBox);

            rows.forEach(function (item) {
                var tm = item.tema !== undefined ? item.tema : item;
                var tmParciais = item.parciais || [];
                var display = (tm === BC_TEMA_SENT) ? BC_TEMA_SENT_LBL : tm;

                var row = document.createElement('div');
                row.className = 'qb-tema-row py-2 border-bottom border-opacity-10';
                row.setAttribute('data-tema-match', normKey(display + ' ' + tm));
                row.setAttribute('data-tema-parciais', JSON.stringify(tmParciais));

                var lab = document.createElement('label');
                lab.className = 'small d-flex align-items-start gap-2 mb-0';
                lab.style.cursor = 'pointer';

                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.className = 'form-check-input mt-1 flex-shrink-0';
                cb.name = 'tema[]';
                cb.value = tm;

                cb.addEventListener('change', function () {
                    if (!this.checked) return;
                    tmParciais.forEach(function (p) {
                        var parcCb = document.querySelector('#qb_parciais input[value="' + p + '"]');
                        if (parcCb && !parcCb.checked) {
                            parcCb.checked = true;
                        }
                    });
                });

                var tx = document.createElement('span');
                tx.className = 'flex-grow-1';
                tx.textContent = display;

                lab.appendChild(cb);
                lab.appendChild(tx);
                row.appendChild(lab);
                listBox.appendChild(row);
            });

            function filterTemasByParciais() {
                var checked = Array.from($('qb_parciais').querySelectorAll('input[type=checkbox]:checked')).map(function (el) { return el.value; });
                var searchQ = normKey(search.value.trim());
                listBox.querySelectorAll('.qb-tema-row').forEach(function (rw) {
                    var matchSearch = searchQ === '' || (rw.getAttribute('data-tema-match') || '').indexOf(searchQ) !== -1;
                    var tmParcs = JSON.parse(rw.getAttribute('data-tema-parciais') || '[]');
                    var matchParc = checked.length === 0 || tmParcs.length === 0 || tmParcs.some(function (p) { return checked.indexOf(p) !== -1; });
                    rw.classList.toggle('d-none', !matchSearch || !matchParc);
                });
            }

            $('qb_parciais').addEventListener('change', filterTemasByParciais);

            search.addEventListener('input', filterTemasByParciais);
        });
    }

    function onMateriaChange(mid) {
        $('qb_materia_hidden').value = mid || '';
        $('qb_catedra_hidden').value = '';
        $('qb_parciais').innerHTML = '';
        $('qb_temas').innerHTML = '';
        var catSel = $('qb_cat');
        if (catSel) {
            catSel.innerHTML = '';
            catSel.onchange = null;
            catSel.disabled = true;
            refreshSel(catSel);
        }
        var qh = $('qb_cat_hint');
        if (qh) qh.classList.add('d-none');
        if (! mid) return;

        u(URLs.cat, '?materia_id=' + encodeURIComponent(mid)).then(function (j) {
            var rows = j.data || [];
            if (! rows.length) {
                loadFiltros();
                return;
            }
            if (qh) qh.classList.remove('d-none');
            catSel.disabled = false;
            setSel(catSel, rows, '{{ __('catalog.placeholder') }}');
            catSel.onchange = function () {
                $('qb_catedra_hidden').value = this.value || '';
                $('qb_parciais').innerHTML = '';
                $('qb_temas').innerHTML = '';
                if (! this.value) {
                    return;
                }
                loadFiltros();
            };
        });
    }

    var matSel = $('qb_mat');
    if (matSel) {
        matSel.addEventListener('change', function () { onMateriaChange(this.value); });
        if (matSel.value) { onMateriaChange(matSel.value); }
    }

    function selectFaculdade(fi) {
        document.querySelectorAll('.qb-pick-anos').forEach(function (el) {
            el.classList.toggle('d-none', el.getAttribute('data-fi') !== String(fi));
        });
        document.querySelectorAll('.qb-pick-materias').forEach(function (el) {
            el.classList.add('d-none');
        });
        onMateriaChange('');
        maybeAutoAno(fi);
    }

    function maybeAutoAno(fi) {
        var wrap = document.getElementById('qb_anos_' + fi);
        if (!wrap) return;
        var radios = wrap.querySelectorAll('input[type="radio"]');
        if (radios.length === 1) {
            radios[0].checked = true;
            selectAno(fi, radios[0].value);
        }
    }

    function selectAno(fi, ai) {
        document.querySelectorAll('.qb-pick-materias').forEach(function (el) {
            var match = el.getAttribute('data-fi') === String(fi) && el.getAttribute('data-ai') === String(ai);
            el.classList.toggle('d-none', !match);
        });
        onMateriaChange('');
        maybeAutoMateria(fi, ai);
    }

    function maybeAutoMateria(fi, ai) {
        var wrap = $('qb_materias_' + fi + '_' + ai);
        if (!wrap) return;
        var radios = wrap.querySelectorAll('input[type="radio"]');
        if (radios.length === 1) {
            radios[0].checked = true;
            onMateriaChange(radios[0].value);
        }
    }

    document.querySelectorAll('#qb_pick_faculdade input[type="radio"]').forEach(function (r) {
        r.addEventListener('change', function () { selectFaculdade(this.value); });
    });
    document.querySelectorAll('.qb-pick-anos input[type="radio"]').forEach(function (r) {
        r.addEventListener('change', function () { selectAno(this.getAttribute('data-fi'), this.value); });
    });
    document.querySelectorAll('.qb-pick-materias input[type="radio"]').forEach(function (r) {
        r.addEventListener('change', function () { onMateriaChange(this.value); });
    });

    var facRadios = document.querySelectorAll('#qb_pick_faculdade input[type="radio"]');
    if (facRadios.length === 1) {
        facRadios[0].checked = true;
        selectFaculdade(facRadios[0].value);
    }
})();
</script>
@endpush

<input type="hidden" name="materia" id="qb_materia_hidden" value="" required form="bc-qbank-form">
<input type="hidden" name="catedra_id" id="qb_catedra_hidden" value="" form="bc-qbank-form">

@php
    $multiplasFaculdades = $materias->pluck('agrupamento.faculdade.id')->filter()->unique()->count() > 1;
    $materiaLabel = function ($m) use ($multiplasFaculdades) {
        if (! $multiplasFaculdades || ! $m->agrupamento?->faculdade) {
            return $m->nome;
        }

        return $m->nome.' ('.$m->agrupamento->faculdade->nome.')';
    };
@endphp
@if ($materias->count() === 1)
    @php $unicaMateria = $materias->first(); @endphp
    <div class="mb-4">
        <span class="form-label d-block">{{ __('bank.catalog.pick_mat') }}</span>
        <div class="bc-mock-single-materia">
            <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
            <span class="fw-semibold">{{ $materiaLabel($unicaMateria) }}</span>
        </div>
    </div>
    <select id="qb_mat" hidden>
        <option value="{{ $unicaMateria->id }}" selected>{{ $materiaLabel($unicaMateria) }}</option>
    </select>
@else
    @php
        $arvoreBanco = $materias
            ->groupBy(fn ($m) => $m->agrupamento?->faculdade?->id ?? 0)
            ->map(function ($porFaculdade) {
                $faculdade = $porFaculdade->first()->agrupamento?->faculdade;
                $anos = $porFaculdade
                    ->groupBy(fn ($m) => $m->agrupamento?->id ?? 0)
                    ->map(fn ($porAno) => [
                        'agrupamento' => $porAno->first()->agrupamento,
                        'materias' => $porAno->sortBy('nome')->values(),
                    ])
                    ->sortBy(fn ($a) => $a['agrupamento']->ordem ?? 0)
                    ->values();

                return ['faculdade' => $faculdade, 'anos' => $anos];
            })
            ->sortBy(fn ($f) => $f['faculdade']->ordem ?? 0)
            ->values();
    @endphp

    <div class="mb-4">
        <label class="form-label">{{ __('bank.catalog.pick_fac') }}</label>
        <div class="bc-mock-subject-grid" id="qb_pick_faculdade">
            @foreach ($arvoreBanco as $fi => $grupo)
                <label class="bc-mock-subject-card">
                    <input type="radio" name="_qb_pick_fac" value="{{ $fi }}">
                    <span class="bc-mock-subject-card__box">
                        <span class="material-symbols-outlined bc-mock-subject-card__ico" aria-hidden="true">account_balance</span>
                        <span class="bc-mock-subject-card__label">{{ $grupo['faculdade']->nome ?? __('bank.catalog.pick_mat') }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    @foreach ($arvoreBanco as $fi => $grupo)
        <div class="mb-4 qb-pick-anos d-none" data-fi="{{ $fi }}" id="qb_anos_{{ $fi }}">
            <label class="form-label">{{ __('bank.catalog.pick_agr') }}</label>
            <div class="bc-mock-subject-grid">
                @foreach ($grupo['anos'] as $ai => $ano)
                    <label class="bc-mock-subject-card">
                        <input type="radio" name="_qb_pick_ano_{{ $fi }}" value="{{ $ai }}" data-fi="{{ $fi }}">
                        <span class="bc-mock-subject-card__box">
                            <span class="material-symbols-outlined bc-mock-subject-card__ico" aria-hidden="true">calendar_today</span>
                            <span class="bc-mock-subject-card__label">{{ $ano['agrupamento']->nome ?? '—' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    @foreach ($arvoreBanco as $fi => $grupo)
        @foreach ($grupo['anos'] as $ai => $ano)
            <div class="mb-4 qb-pick-materias d-none" data-fi="{{ $fi }}" data-ai="{{ $ai }}" id="qb_materias_{{ $fi }}_{{ $ai }}">
                <label class="form-label">{{ __('bank.catalog.pick_mat') }}</label>
                <div class="bc-mock-subject-grid">
                    @foreach ($ano['materias'] as $m)
                        <label class="bc-mock-subject-card">
                            <input type="radio" name="_qb_pick_materia_{{ $fi }}_{{ $ai }}" value="{{ $m->id }}">
                            <span class="bc-mock-subject-card__box">
                                <span class="material-symbols-outlined bc-mock-subject-card__ico" aria-hidden="true">menu_book</span>
                                <span class="bc-mock-subject-card__label">{{ $m->nome }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endforeach

    <select id="qb_mat" hidden></select>
@endif
<div class="mb-4">
    <label class="form-label" for="qb_cat">{{ __('bank.catalog.pick_cat_opt') }}</label>
    <select class="bc-styled-select bc-styled-select--fluid" id="qb_cat" disabled></select>
    <div id="qb_cat_hint" class="form-text small d-none">{{ __('bank.catalog.catedra_obrig') }}</div>
</div>
<div class="mb-3">
    <span class="form-label d-block">{{ __('bank.parc.heading') }}</span>
    <div id="qb_parciais" class="d-flex flex-wrap gap-3"></div>
    <p class="small text-muted mt-2 mb-0">{{ __('bank.help_final_covers_partials') }}</p>
</div>
<div id="qb_temas" class="mb-3"></div>
