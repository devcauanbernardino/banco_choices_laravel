(function () {
  var KEY = 'bancochoices-sidebar-collapsed';
  var html = document.documentElement;

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
    html.classList.toggle('sidebar-collapsed', collapsed);
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
