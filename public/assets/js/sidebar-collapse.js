(function () {
  /** @see resources/views/layouts/app.blade.php (inline script before first paint) */
  var KEY = 'bancochoices-sidebar-collapsed';
  var html = document.documentElement;
  var expandRevealSession = null;

  function clearSidebarExpandReveal() {
    if (!expandRevealSession) {
      html.classList.remove('sidebar-width-animating');
      return;
    }
    var s = expandRevealSession;
    window.clearTimeout(s.tid);
    s.aside.removeEventListener('transitionend', s.onEnd);
    expandRevealSession = null;
    html.classList.remove('sidebar-width-animating');
  }

  /** Mantém rótulos ocultos até a largura da sidebar terminar de animar (expandir). */
  function scheduleSidebarExpandReveal(aside) {
    clearSidebarExpandReveal();
    var desktop = window.matchMedia('(min-width: 992px)').matches;
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!desktop || reduced || aside.classList.contains('app-sidebar--embedded')) {
      return;
    }

    html.classList.add('sidebar-width-animating');
    var done = false;
    function finish() {
      if (done) {
        return;
      }
      done = true;
      clearSidebarExpandReveal();
    }

    function onEnd(e) {
      if (e.target !== aside || e.propertyName !== 'width') {
        return;
      }
      finish();
    }

    aside.addEventListener('transitionend', onEnd);
    var tid = window.setTimeout(finish, 450);
    expandRevealSession = { aside: aside, tid: tid, onEnd: onEnd };
  }

  function refreshSidebarTooltips() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
      return;
    }
    var collapsed = html.classList.contains('sidebar-collapsed');
    var desktop = window.matchMedia('(min-width: 992px)').matches;

    document.querySelectorAll('[data-sidebar-tooltip]').forEach(function (el) {
      var existing = bootstrap.Tooltip.getInstance(el);
      if (existing) {
        existing.dispose();
      }
      if (collapsed && desktop) {
        new bootstrap.Tooltip(el, {
          placement: 'right',
          customClass: 'app-sidebar-tooltip',
          title: el.getAttribute('data-sidebar-tooltip'),
          trigger: 'hover focus',
          container: 'body',
          popperConfig: {
            strategy: 'fixed',
          },
        });
      }
    });
  }

  function apply(collapsed) {
    var wasCollapsed = html.classList.contains('sidebar-collapsed');
    html.classList.toggle('sidebar-collapsed', collapsed);

    var aside = document.querySelector('#appSidebarDesktop');
    if (wasCollapsed && !collapsed && aside) {
      window.requestAnimationFrame(function () {
        scheduleSidebarExpandReveal(aside);
      });
    } else {
      clearSidebarExpandReveal();
    }

    try {
      localStorage.setItem(KEY, collapsed ? '1' : '0');
    } catch (e) {
      /* ignore */
    }
    document.querySelectorAll('.js-sidebar-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      var expand = btn.getAttribute('data-tooltip-collapsed');
      var collapse = btn.getAttribute('data-tooltip-expanded');
      if (expand && collapse) {
        btn.setAttribute('data-sidebar-tooltip', collapsed ? expand : collapse);
        btn.setAttribute('aria-label', collapsed ? expand : collapse);
      }
      var icon = btn.querySelector('.app-sidebar-collapse-ico');
      if (icon) {
        icon.textContent = collapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
      }
      var label = btn.querySelector('.app-sidebar-collapse-label');
      if (label) {
        var le = btn.getAttribute('data-label-expanded');
        var lc = btn.getAttribute('data-label-collapsed');
        if (le && lc) {
          label.textContent = collapsed ? lc : le;
        }
      }
    });
    refreshSidebarTooltips();
  }

  /** Com o menu de idiomas aberto, o tooltip não pode ficar por cima do dropdown. */
  function bindLangDropdownTooltipGuard() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip || !bootstrap.Dropdown) {
      return;
    }
    document.querySelectorAll('.bc-lang-selector .dropdown-toggle').forEach(function (btn) {
      btn.addEventListener('show.bs.dropdown', function () {
        var tip = bootstrap.Tooltip.getInstance(btn);
        if (tip) {
          tip.hide();
          if (typeof tip.disable === 'function') {
            tip.disable();
          }
        }
      });
      btn.addEventListener('hidden.bs.dropdown', function () {
        var tip = bootstrap.Tooltip.getInstance(btn);
        if (tip && typeof tip.enable === 'function') {
          tip.enable();
        }
      });
    });
  }

  function init() {
    var collapsed = false;
    try {
      collapsed = localStorage.getItem(KEY) === '1';
    } catch (e) {
      /* ignore */
    }
    apply(collapsed);

    document.querySelectorAll('.js-sidebar-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        apply(!html.classList.contains('sidebar-collapsed'));
      });
    });

    bindLangDropdownTooltipGuard();

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(refreshSidebarTooltips, 150);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
