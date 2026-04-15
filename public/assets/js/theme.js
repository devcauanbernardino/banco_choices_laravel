(function () {
  var KEY = 'bancochoices-theme';

  function getStored() {
    try {
      return localStorage.getItem(KEY);
    } catch (e) {
      return null;
    }
  }

  function setStored(theme) {
    try {
      localStorage.setItem(KEY, theme);
    } catch (e) {
      /* ignore */
    }
  }

  function applyTheme(theme) {
    if (theme !== 'dark' && theme !== 'light') {
      theme = 'light';
    }
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
    setStored(theme);
    syncControls(theme);
  }

  function syncControls(theme) {
    var dark = theme === 'dark';
    document.querySelectorAll('.js-theme-toggle').forEach(function (el) {
      if (el.type === 'checkbox') {
        el.checked = dark;
      }
    });
    document.querySelectorAll('.js-theme-toggle-btn').forEach(function (btn) {
      btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
      btn.classList.toggle('is-dark', dark);
    });
    document.querySelectorAll('.js-theme-mode-btn').forEach(function (btn) {
      var want = btn.getAttribute('data-theme');
      var pressed = want === theme;
      btn.setAttribute('aria-pressed', pressed ? 'true' : 'false');
      btn.classList.toggle('is-active', pressed);
    });
  }

  function initFromStorage() {
    var stored = getStored();
    if (stored === 'dark' || stored === 'light') {
      applyTheme(stored);
    } else {
      applyTheme('light');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFromStorage();

    document.querySelectorAll('.js-theme-toggle').forEach(function (el) {
      el.addEventListener('change', function () {
        applyTheme(el.checked ? 'dark' : 'light');
      });
    });

    document.querySelectorAll('.js-theme-toggle-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
      });
    });

    document.querySelectorAll('.js-theme-mode-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = btn.getAttribute('data-theme');
        if (next === 'dark' || next === 'light') {
          applyTheme(next);
        }
      });
    });
  });
})();
