(function () {
    var toasts = document.querySelectorAll('[data-bc-toast]');
    toasts.forEach(function (el, i) {
        setTimeout(function () {
            el.addEventListener('animationend', function () { el.remove(); }, { once: true });
            el.classList.add('bc-toast--out');
        }, 3200 + i * 250);
    });
})();
