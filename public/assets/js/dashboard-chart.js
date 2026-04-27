(function () {
    'use strict';

    function initChart(root) {
        if (!root || root.dataset.dashChartReady === '1') return;
        root.dataset.dashChartReady = '1';

        var bars = Array.prototype.slice.call(root.querySelectorAll('.dash-painel-chart-bar-wrap'));
        if (!bars.length) return;

        var card = root.closest('.dash-painel-chart-card');
        var readout = card ? card.querySelector('.dash-painel-chart-readout') : null;
        var readoutLabel = readout ? readout.querySelector('.dash-painel-chart-readout-label') : null;
        var readoutValue = readout ? readout.querySelector('.dash-painel-chart-readout-value') : null;
        var defaultText = readoutLabel ? (readoutLabel.dataset.default || readoutLabel.textContent) : '';

        function setReadout(bar) {
            if (!readout) return;
            if (!bar) {
                readout.classList.remove('is-active');
                if (readoutLabel) readoutLabel.textContent = defaultText;
                if (readoutValue) {
                    readoutValue.hidden = true;
                    readoutValue.textContent = '';
                }
                return;
            }
            var label = bar.dataset.label || '';
            var value = parseFloat(bar.dataset.value || '0');
            var pct = Math.round(value);
            readout.classList.add('is-active');
            if (readoutLabel) readoutLabel.textContent = label;
            if (readoutValue) {
                readoutValue.hidden = false;
                readoutValue.textContent = pct + '%';
            }
        }

        function activate(bar) {
            bars.forEach(function (b) {
                b.classList.toggle('is-hover', b === bar);
            });
            setReadout(bar);
        }

        function clearActive() {
            bars.forEach(function (b) { b.classList.remove('is-hover'); });
            var selected = root.querySelector('.dash-painel-chart-bar-wrap.is-selected');
            setReadout(selected || null);
        }

        bars.forEach(function (bar) {
            bar.addEventListener('mouseenter', function () { activate(bar); });
            bar.addEventListener('focus', function () { activate(bar); });
            bar.addEventListener('mouseleave', clearActive);
            bar.addEventListener('blur', clearActive);
            bar.addEventListener('click', function () {
                var wasSelected = bar.classList.contains('is-selected');
                bars.forEach(function (b) { b.classList.remove('is-selected'); });
                if (!wasSelected) {
                    bar.classList.add('is-selected');
                    setReadout(bar);
                } else {
                    setReadout(null);
                }
            });
        });

        requestAnimationFrame(function () {
            root.classList.add('is-animated');
        });
    }

    function boot() {
        document.querySelectorAll('.js-dash-chart').forEach(initChart);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
