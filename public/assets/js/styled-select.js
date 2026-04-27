(function () {
    'use strict';

    var ID = 0;

    function nextId() { ID += 1; return 'bc-ss-' + ID; }

    function buildOption(option, listId) {
        var item = document.createElement('li');
        item.className = 'bc-styled-select__item';
        item.setAttribute('role', 'option');
        item.id = listId + '-opt-' + (option.value || 'empty');
        item.dataset.value = option.value;
        if (option.disabled) {
            item.setAttribute('aria-disabled', 'true');
            item.classList.add('is-disabled');
        }
        item.textContent = option.textContent.trim();
        return item;
    }

    function enhance(select) {
        if (select.dataset.bcSsReady === '1') return;
        if (select.multiple) return;
        select.dataset.bcSsReady = '1';

        var wrap = document.createElement('div');
        wrap.className = 'bc-styled-select-wrap';
        Array.prototype.forEach.call(select.classList, function (cls) {
            if (cls !== 'bc-styled-select') wrap.classList.add(cls);
        });

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'bc-styled-select bc-styled-select__toggle';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        var aria = select.getAttribute('aria-label');
        if (aria) button.setAttribute('aria-label', aria);

        var labelSpan = document.createElement('span');
        labelSpan.className = 'bc-styled-select__label';
        button.appendChild(labelSpan);

        var listId = nextId();
        var list = document.createElement('ul');
        list.className = 'bc-styled-select__list';
        list.id = listId;
        list.setAttribute('role', 'listbox');
        if (aria) list.setAttribute('aria-label', aria);
        button.setAttribute('aria-controls', listId);

        var items = [];
        Array.prototype.forEach.call(select.options, function (opt) {
            var item = buildOption(opt, listId);
            list.appendChild(item);
            items.push(item);
        });

        function syncFromSelect() {
            var current = select.options[select.selectedIndex];
            labelSpan.textContent = current ? current.textContent.trim() : '';
            items.forEach(function (it) {
                var active = it.dataset.value === (current ? current.value : '');
                it.classList.toggle('is-selected', active);
                it.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function setOpen(open) {
            wrap.classList.toggle('is-open', open);
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                var activeIdx = Math.max(0, select.selectedIndex);
                focusItem(activeIdx);
                document.addEventListener('mousedown', onDocClick, true);
                document.addEventListener('keydown', onDocKey);
            } else {
                document.removeEventListener('mousedown', onDocClick, true);
                document.removeEventListener('keydown', onDocKey);
            }
        }

        function focusItem(idx) {
            items.forEach(function (it, i) {
                it.classList.toggle('is-focus', i === idx);
                if (i === idx) {
                    it.scrollIntoView({ block: 'nearest' });
                    list.setAttribute('aria-activedescendant', it.id);
                }
            });
        }

        function pick(idx) {
            if (idx < 0 || idx >= items.length) return;
            var item = items[idx];
            if (item.classList.contains('is-disabled')) return;
            select.value = item.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncFromSelect();
            setOpen(false);
            button.focus();
        }

        function onDocClick(e) {
            if (!wrap.contains(e.target)) setOpen(false);
        }

        function onDocKey(e) {
            var current = items.findIndex(function (it) { return it.classList.contains('is-focus'); });
            if (current < 0) current = Math.max(0, select.selectedIndex);
            if (e.key === 'Escape') {
                e.preventDefault();
                setOpen(false);
                button.focus();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusItem(Math.min(items.length - 1, current + 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusItem(Math.max(0, current - 1));
            } else if (e.key === 'Home') {
                e.preventDefault();
                focusItem(0);
            } else if (e.key === 'End') {
                e.preventDefault();
                focusItem(items.length - 1);
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                pick(current);
            }
        }

        button.addEventListener('click', function () {
            setOpen(!wrap.classList.contains('is-open'));
        });
        button.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                setOpen(true);
            }
        });

        items.forEach(function (item, idx) {
            item.addEventListener('mouseenter', function () { focusItem(idx); });
            item.addEventListener('click', function () { pick(idx); });
        });

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(button);
        wrap.appendChild(list);
        wrap.appendChild(select);
        select.classList.add('bc-styled-select__native');

        syncFromSelect();
        select.addEventListener('change', syncFromSelect);
    }

    function boot() {
        document.querySelectorAll('select.bc-styled-select').forEach(enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
