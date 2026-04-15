(function () {
  var KEY = 'bancochoices-sidebar-collapsed';
  var html = document.documentElement;

  function apply(collapsed) {
    html.classList.toggle('sidebar-collapsed', collapsed);
    try {
      localStorage.setItem(KEY, collapsed ? '1' : '0');
    } catch (e) {
      /* ignore */
    }
    document.querySelectorAll('.js-sidebar-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      btn.setAttribute('title', collapsed ? 'Expandir painel lateral' : 'Recolher painel lateral');
      var icon = btn.querySelector('.app-sidebar-collapse-ico');
      if (icon) {
        icon.textContent = collapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left';
      }
      var label = btn.querySelector('.app-sidebar-collapse-label');
      if (label) {
        label.textContent = collapsed ? 'Expandir painel' : 'Recolher painel';
      }
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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
