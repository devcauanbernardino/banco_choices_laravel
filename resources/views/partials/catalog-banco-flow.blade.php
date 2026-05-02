@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };

    function u(url, q) {
        return fetch(url + q, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }}).then(function (r) { return r.json(); });
    }

    function setSel(sel, rows, placeholder) {
        sel.innerHTML = '';
        var o0 = document.createElement('option');
        o0.value = ''; o0.textContent = placeholder; sel.appendChild(o0);
        (rows || []).forEach(function (row) {
            var o = document.createElement('option');
            o.value = row.id; o.textContent = row.nome; sel.appendChild(o);
        });
    }

    var URLs = {
        fac: '{{ route('api.catalogo.faculdades') }}',
        agr: '{{ route('api.catalogo.agrupamentos') }}',
        mat: '{{ route('api.catalogo.materias') }}',
        cat: '{{ route('api.catalogo.catedras') }}',
        parc: '{{ route('api.catalogo.parciais') }}',
        tem: '{{ route('api.catalogo.temas') }}',
    };

    function resetDown(from) {
        if (from <= 1) { setSel($('qb_agr'), [], '{{ __('bank.catalog.pick_agr') }}'); $('qb_agr').disabled = true; }
        if (from <= 2) { setSel($('qb_mat'), [], '{{ __('bank.catalog.pick_mat') }}'); $('qb_mat').disabled = true; }
        if (from <= 3) {
            var qc = $('qb_cat');
            if (qc) { qc.innerHTML = ''; qc.onchange = null; qc.disabled = true; }
            if ($('qb_cat_hint')) { $('qb_cat_hint').classList.add('d-none'); }
        }
        if (from <= 4) { $('qb_parciais').innerHTML = ''; $('qb_temas').innerHTML = ''; }
        $('qb_materia_hidden').value = '';
        $('qb_catedra_hidden').value = '';
    }

    u(URLs.fac, '').then(function (j) { setSel($('qb_fac'), j.data || [], '{{ __('bank.catalog.pick_fac') }}'); });

    $('qb_fac').addEventListener('change', function () {
        resetDown(1);
        if (!this.value) return;
        $('qb_agr').disabled = false;
        u(URLs.agr, '?faculdade_id=' + encodeURIComponent(this.value)).then(function (j) {
            setSel($('qb_agr'), j.data || [], '{{ __('bank.catalog.pick_agr') }}');
        });
    });

    $('qb_agr').addEventListener('change', function () {
        resetDown(2);
        if (!this.value) return;
        $('qb_mat').disabled = false;
        u(URLs.mat, '?agrupamento_id=' + encodeURIComponent(this.value)).then(function (j) {
            setSel($('qb_mat'), j.data || [], '{{ __('bank.catalog.pick_mat') }}');
        });
    });

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
            var mapLbl = {'1':'{{ __('bank.parc.label_1') }}','2':'{{ __('bank.parc.label_2') }}','3':'{{ __('bank.parc.label_3') }}','final':'{{ __('bank.parc.final') }}'};
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
                return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
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

            rows.forEach(function (tm) {
                var row = document.createElement('div');
                row.className = 'qb-tema-row py-2 border-bottom border-opacity-10';
                row.setAttribute('data-tema-match', normKey(tm));

                var lab = document.createElement('label');
                lab.className = 'small d-flex align-items-start gap-2 mb-0';

                lab.style.cursor = 'pointer';

                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.className = 'form-check-input mt-1 flex-shrink-0';
                cb.name = 'tema[]';
                cb.value = tm;

                var tx = document.createElement('span');
                tx.className = 'flex-grow-1';

                tx.textContent = tm;

                lab.appendChild(cb);
                lab.appendChild(tx);

                row.appendChild(lab);
                listBox.appendChild(row);

            });

            search.addEventListener('input', function () {
                var q = normKey(search.value.trim());
                listBox.querySelectorAll('.qb-tema-row').forEach(function (rw) {

                    var m = rw.getAttribute('data-tema-match') || '';
                    rw.classList.toggle('d-none', q !== '' && m.indexOf(q) === -1);
                });

            });

        });
    }

    $('qb_mat').addEventListener('change', function () {
        var mid = this.value || '';
        $('qb_materia_hidden').value = mid;
        $('qb_catedra_hidden').value = '';
        $('qb_parciais').innerHTML = '';
        $('qb_temas').innerHTML = '';
        var catSel = $('qb_cat');
        if (catSel) {
            catSel.innerHTML = '';
            catSel.onchange = null;
            catSel.disabled = true;
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
            setSel(catSel, rows, '{{ __('bank.catalog.pick_cat_req') }}');
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
    });
})();
</script>
@endpush

<input type="hidden" name="materia" id="qb_materia_hidden" value="" required form="bc-qbank-form">
<input type="hidden" name="catedra_id" id="qb_catedra_hidden" value="" form="bc-qbank-form">

<div class="mb-4">
    <label class="form-label" for="qb_fac">{{ __('bank.catalog.pick_fac') }}</label>
    <select class="form-select" id="qb_fac"></select>
</div>
<div class="mb-4">
    <label class="form-label" for="qb_agr">{{ __('bank.catalog.pick_agr') }}</label>
    <select class="form-select" id="qb_agr" disabled></select>
</div>
<div class="mb-4">
    <label class="form-label" for="qb_mat">{{ __('bank.catalog.pick_mat') }}</label>
    <select class="form-select" id="qb_mat" disabled></select>
</div>
<div class="mb-4">
    <label class="form-label" for="qb_cat">{{ __('bank.catalog.pick_cat_opt') }}</label>
    <select class="form-select" id="qb_cat" disabled></select>
    <div id="qb_cat_hint" class="form-text small d-none">{{ __('bank.catalog.catedra_obrig') }}</div>
</div>
<div class="mb-3">
    <span class="form-label d-block">{{ __('bank.parc.heading') }}</span>
    <div id="qb_parciais" class="d-flex flex-wrap gap-3"></div>
    <p class="small text-muted mt-2 mb-0">{{ __('bank.help_final_covers_partials') }}</p>
</div>
<div id="qb_temas" class="mb-3"></div>
