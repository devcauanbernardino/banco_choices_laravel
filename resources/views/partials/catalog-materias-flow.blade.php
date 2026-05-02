@props([
    'excludeIdsCsv' => '',
    'presetMateriaId' => 0,
])

@push('scripts')
<script>
(function () {
    var exclude = new Set(String(@json($excludeIdsCsv)).split(',').map(function (x) {
        var n = parseInt(String(x).trim(), 10);
        return Number.isFinite(n) && n > 0 ? n : null;
    }).filter(Boolean));
    var urls = {
        fac: "{{ route('catalogo.public.faculdades') }}",
        agr: "{{ route('catalogo.public.agrupamentos') }}",
        mat: "{{ route('catalogo.public.materias') }}",
        cat: "{{ route('catalogo.public.catedras') }}",
    };
    var preMateria = {{ (int) $presetMateriaId }};

    function el(id) { return document.getElementById(id); }
    function setOptions(select, items, placeholder) {
        if (!select) return;
        select.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        select.appendChild(opt0);
        items.forEach(function (it) {
            var o = document.createElement('option');
            o.value = String(it.id);
            o.textContent = it.nome;
            select.appendChild(o);
        });
    }

    var selFac = el('catalog_sel_faculdade');
    var selAgr = el('catalog_sel_agrupamiento');
    var selMat = el('catalog_sel_materia');
    var selCat = el('catalog_sel_catedra');
    var hintCat = el('catalog_catedra_hint');
    var boxSelected = el('catalog_selected_ids');
    var btnAdd = el('catalog_btn_add');
    var errBox = el('catalog_err');

    function showErr(msg) {
        if (!errBox) return;
        errBox.textContent = msg || '';
        errBox.classList.toggle('d-none', !msg);
    }

    function fetchJson(url) {
        return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }}).then(function (r) {
            return r.ok ? r.json() : Promise.reject(new Error('http'));
        });
    }

    fetchJson(urls.fac).then(function (j) {
        setOptions(selFac, (j.data || []), '{{ __('signup.catalog.pick_faculdade') }}');
    }).catch(function () {
        showErr('{{ __('signup.catalog.err_load') }}');
    });

    if (selFac) selFac.addEventListener('change', function () {
        showErr('');
        selAgr.disabled = !selFac.value;
        selMat.disabled = true;
        selCat.disabled = true;
        setOptions(selAgr, [], '{{ __('signup.catalog.pick_agrupamiento') }}');
        setOptions(selMat, [], '{{ __('signup.catalog.pick_materia') }}');
        selCat.innerHTML = '';
        if (!selFac.value) return;
        fetchJson(urls.agr + '?faculdade_id=' + encodeURIComponent(selFac.value)).then(function (j) {
            setOptions(selAgr, (j.data || []), '{{ __('signup.catalog.pick_agrupamiento') }}');
            selAgr.disabled = false;
        });
    });

    if (selAgr) selAgr.addEventListener('change', function () {
        showErr('');
        selMat.disabled = !selAgr.value;
        selCat.disabled = true;
        selCat.innerHTML = '';
        setOptions(selMat, [], '{{ __('signup.catalog.pick_materia') }}');
        if (!selAgr.value) return;
        var q = '?agrupamento_id=' + encodeURIComponent(selAgr.value);
        if (exclude.size) q += '&exclude_ids=' + encodeURIComponent(Array.from(exclude).join(','));
        fetchJson(urls.mat + q).then(function (j) {
            var rows = j.data || [];
            setOptions(selMat, rows, '{{ __('signup.catalog.pick_materia') }}');
            selMat.disabled = false;
        });
    });

    function syncCatedra() {
        selCat.innerHTML = '';
        selCat.disabled = true;
        if (hintCat) hintCat.classList.add('d-none');
        if (!selMat || !selMat.value) return;
        fetchJson(urls.cat + '?materia_id=' + encodeURIComponent(selMat.value)).then(function (j) {
            var rows = j.data || [];
            if (!rows.length) return;
            if (hintCat) hintCat.classList.remove('d-none');
            selCat.disabled = false;
            var opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = '{{ __('signup.catalog.pick_catedra_req') }}';
            selCat.appendChild(opt0);
            rows.forEach(function (it) {
                var o = document.createElement('option');
                o.value = String(it.id);
                o.textContent = it.nome;
                selCat.appendChild(o);
            });
        });
    }

    if (selMat) selMat.addEventListener('change', syncCatedra);

    function renderHidden(selectedSet) {
        if (!boxSelected) return;
        boxSelected.innerHTML = '';
        selectedSet.forEach(function (mid) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'materias[]';
            inp.value = String(mid);
            boxSelected.appendChild(inp);
        });
    }

    var selected = new Map();

    if (btnAdd) btnAdd.addEventListener('click', function (ev) {
        ev.preventDefault();
        showErr('');
        var mid = parseInt(selMat && selMat.value, 10);
        if (!(mid > 0)) {
            showErr('{{ __('signup.catalog.err_pick_materia') }}'); return;
        }
        fetchJson(urls.cat + '?materia_id=' + encodeURIComponent(mid)).then(function (j) {
            var cats = j.data || [];
            if (cats.length && (!selCat || !selCat.value)) {
                showErr('{{ __('signup.catalog.err_pick_catedra') }}'); return;
            }
            var labelParts = [];
            if (selFac && selFac.options[selFac.selectedIndex]) labelParts.push(selFac.options[selFac.selectedIndex].text);
            if (selMat && selMat.options[selMat.selectedIndex]) labelParts.push(selMat.options[selMat.selectedIndex].text);
            if (cats.length && selCat && selCat.options[selCat.selectedIndex]) labelParts.push(selCat.options[selCat.selectedIndex].text);
            if (selected.has(mid)) return;
            selected.set(mid, labelParts.join(' — '));
            var list = el('catalog_chips');
            if (list) {
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.dataset.mid = String(mid);
                li.innerHTML = '<span>' + labelParts.join(' — ') +
                    '</span><button type="button" class="btn btn-sm btn-outline-danger" data-rm="'+mid+'">{{ __('signup.catalog.remove') }}</button>';
                list.appendChild(li);
            }
            renderHidden(selected);
        });
    });

    document.addEventListener('click', function (ev) {
        var t = ev.target;
        if (t && t.matches && t.matches('button[data-rm]')) {
            var id = parseInt(t.getAttribute('data-rm'), 10);
            selected.delete(id);
            var row = document.querySelector('#catalog_chips li[data-mid="'+id+'"]');
            if (row) row.remove();
            renderHidden(selected);
        }
    });

    if (preMateria > 0 && !exclude.has(preMateria)) {
        selected.set(preMateria, '#'+preMateria);
        var list = el('catalog_chips');
        if (list) {
            var li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.dataset.mid = String(preMateria);
            li.innerHTML = '<span>{{ __('signup.catalog.preloaded_materia') }}</span>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-rm="'+preMateria+'">{{ __('signup.catalog.remove') }}</button>';
            list.appendChild(li);
        }
        renderHidden(selected);
    }
})();
</script>
@endpush

<div class="mb-3">
    <div id="catalog_err" class="alert alert-warning py-2 small d-none" role="alert"></div>
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <label class="form-label small" for="catalog_sel_faculdade">{{ __('signup.catalog.label_faculdade') }}</label>
            <select class="form-select" id="catalog_sel_faculdade"></select>
        </div>
        <div class="col-md-6">
            <label class="form-label small" for="catalog_sel_agrupamiento">{{ __('signup.catalog.label_agrupamiento') }}</label>
            <select class="form-select" id="catalog_sel_agrupamiento" disabled></select>
        </div>
        <div class="col-md-6">
            <label class="form-label small" for="catalog_sel_materia">{{ __('signup.catalog.label_materia') }}</label>
            <select class="form-select" id="catalog_sel_materia" disabled></select>
        </div>
        <div class="col-md-6">
            <label class="form-label small" for="catalog_sel_catedra">{{ __('signup.catalog.label_catedra') }}</label>
            <select class="form-select" id="catalog_sel_catedra" disabled></select>
            <div id="catalog_catedra_hint" class="form-text small d-none">{{ __('signup.catalog.catedra_obrigatoria') }}</div>
        </div>
    </div>
    <button type="button" class="btn btn-outline-primary mb-3" id="catalog_btn_add">{{ __('signup.catalog.btn_add') }}</button>
    <div class="catalog-selected-summary">
        <p class="small text-muted mb-1">{{ __('signup.catalog.selected_heading') }}</p>
        <ul class="list-group list-group-flush border rounded mb-2" id="catalog_chips"></ul>
        <div id="catalog_selected_ids"></div>
    </div>
</div>
