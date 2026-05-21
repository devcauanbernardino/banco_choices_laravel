/**
 * Offcanvas Bootstrap 5: scroll:false chama ScrollBarHelper.hide() (padding no body).
 * Isso desloca a topbar sticky. Offcanvas NÃO adiciona modal-open — fixes anteriores não corriam.
 *
 * Solução: scroll:true (sem padding do BS) + overflow:hidden só no html (scrollbar-gutter: stable).
 */
(function () {
    'use strict';

    var OFFCANVAS_IDS = ['lpOffcanvas', 'sidebarMobile'];
    var FIXED = '.fixed-top, .fixed-bottom, .is-fixed, .sticky-top, .sticky-bottom, .lp-topbar';

    function openOffcanvasCount() {
        return document.querySelectorAll('.offcanvas.show').length;
    }

    function lockRoot() {
        document.documentElement.classList.add('bc-offcanvas-open');
    }

    function unlockRoot() {
        if (openOffcanvasCount() === 0) {
            document.documentElement.classList.remove('bc-offcanvas-open');
        }
    }

    function initOffcanvas(id) {
        var el = document.getElementById(id);
        if (!el || !window.bootstrap || !bootstrap.Offcanvas) {
            return;
        }

        bootstrap.Offcanvas.getOrCreateInstance(el, {
            scroll: true,
            backdrop: true,
        });

        el.addEventListener('show.bs.offcanvas', lockRoot);
        el.addEventListener('hidden.bs.offcanvas', unlockRoot);
    }

    function stripModalPadding() {
        if (!document.body.classList.contains('modal-open')) {
            return;
        }

        document.body.style.setProperty('padding-right', '0', 'important');
        document.body.style.setProperty('padding-inline-end', '0', 'important');
        document.body.style.setProperty('margin-right', '0', 'important');

        document.querySelectorAll(FIXED).forEach(function (node) {
            node.style.setProperty('padding-right', '0', 'important');
            node.style.setProperty('margin-right', '0', 'important');
        });
    }

    function boot() {
        OFFCANVAS_IDS.forEach(initOffcanvas);

        ['show.bs.modal', 'shown.bs.modal'].forEach(function (evt) {
            document.addEventListener(evt, stripModalPadding);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
