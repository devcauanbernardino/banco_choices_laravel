/**
 * Motor global do Pomodoro: estado persistido em localStorage com timestamps
 * absolutos (imune a throttling de aba em segundo plano), pra sobreviver a
 * navegacao entre paginas (o site nao e SPA, cada pagina recarrega do zero).
 * Carregado em todas as paginas autenticadas (layouts/app.blade.php) antes do
 * script proprio de resources/views/pomodoro/index.blade.php e do widget
 * flutuante (partials/pomodoro-widget.blade.php).
 */
window.PomodoroEngine = (function () {
    var STORAGE_KEY = 'bancochoices-pomodoro-session';
    var ABANDON_MS = 3 * 60 * 60 * 1000;

    var state = null;
    var tickHandle = null;
    var audioCtx = null;
    var noiseNode = null;
    var noiseFilter = null;
    var noiseGain = null;

    function nowMs() { return Date.now(); }

    function loadState() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            var parsed = JSON.parse(raw);
            return (parsed && typeof parsed === 'object') ? parsed : null;
        } catch (e) { return null; }
    }

    function persist() {
        try {
            if (!state || state.mode === 'idle') {
                localStorage.removeItem(STORAGE_KEY);
            } else {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            }
        } catch (e) { /* ignore */ }
    }

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    function logCycle() {
        var url = window.POMODORO_LOG_URL;
        if (!url) return;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                materia_id: state.materiaId,
                sessao_uid: state.sessaoUid,
                duracao_minutos: state.focusMin
            })
        }).then(function (r) { return r.json(); }).then(function (data) {
            window.dispatchEvent(new CustomEvent('pomodoro:cycle-logged', { detail: data }));
        }).catch(function () { /* silencioso: nao interrompe o timer */ });
    }

    function beep() {
        try {
            var ctx = ensureAudioCtx();
            var o = ctx.createOscillator();
            var g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.value = 660;
            g.gain.setValueAtTime(0.0001, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.4);
            o.start();
            o.stop(ctx.currentTime + 0.4);
        } catch (e) { /* ignore */ }
    }

    // ---- Som ambiente sintetizado (Web Audio) ----
    function ensureAudioCtx() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') {
            // Se esta chamada ja partiu de um gesto do usuario (ex.: clique no
            // botao de som ambiente), resolve na hora. Senao (ex.: retomado
            // automaticamente ao carregar a pagina), fica pendente ate o
            // primeiro clique/toque/tecla.
            audioCtx.resume().catch(function () { /* ignore */ });
            var resume = function () {
                audioCtx.resume();
                document.removeEventListener('click', resume);
                document.removeEventListener('touchstart', resume);
                document.removeEventListener('keydown', resume);
            };
            document.addEventListener('click', resume);
            document.addEventListener('touchstart', resume);
            document.addEventListener('keydown', resume);
        }
        return audioCtx;
    }

    function makeNoiseBuffer(ctx, kind, seconds) {
        var length = Math.floor(ctx.sampleRate * seconds);
        var buffer = ctx.createBuffer(1, length, ctx.sampleRate);
        var data = buffer.getChannelData(0);
        var i, white;

        if (kind === 'pink') {
            var b0 = 0, b1 = 0, b2 = 0, b3 = 0, b4 = 0, b5 = 0, b6 = 0;
            for (i = 0; i < length; i++) {
                white = Math.random() * 2 - 1;
                b0 = 0.99886 * b0 + white * 0.0555179;
                b1 = 0.99332 * b1 + white * 0.0750759;
                b2 = 0.96900 * b2 + white * 0.1538520;
                b3 = 0.86650 * b3 + white * 0.3104856;
                b4 = 0.55000 * b4 + white * 0.5329522;
                b5 = -0.7616 * b5 - white * 0.0168980;
                data[i] = (b0 + b1 + b2 + b3 + b4 + b5 + b6 + white * 0.5362) * 0.11;
                b6 = white * 0.115926;
            }
        } else if (kind === 'brown') {
            var last = 0;
            for (i = 0; i < length; i++) {
                white = Math.random() * 2 - 1;
                last = (last + 0.02 * white) / 1.02;
                data[i] = last * 3.5;
            }
        } else if (kind === 'rain') {
            var lastR = 0;
            for (i = 0; i < length; i++) {
                white = Math.random() * 2 - 1;
                lastR = (lastR + 0.08 * white) / 1.08;
                var drop = (Math.random() < 0.02) ? (Math.random() * 2 - 1) * 0.35 : 0;
                data[i] = lastR * 2.2 + drop;
            }
        } else {
            for (i = 0; i < length; i++) data[i] = Math.random() * 2 - 1;
        }

        return buffer;
    }

    function stopAmbient() {
        if (noiseNode) { try { noiseNode.stop(); } catch (e) { /* ignore */ } noiseNode.disconnect(); noiseNode = null; }
        if (noiseFilter) { noiseFilter.disconnect(); noiseFilter = null; }
        if (noiseGain) { noiseGain.disconnect(); noiseGain = null; }
    }

    function startAmbient(kind, volume) {
        stopAmbient();
        if (!kind || kind === 'none') return;
        var ctx = ensureAudioCtx();
        noiseNode = ctx.createBufferSource();
        noiseNode.buffer = makeNoiseBuffer(ctx, kind, kind === 'rain' ? 6 : 3);
        noiseNode.loop = true;
        noiseGain = ctx.createGain();
        noiseGain.gain.value = typeof volume === 'number' ? volume : 0.4;

        if (kind === 'rain') {
            noiseFilter = ctx.createBiquadFilter();
            noiseFilter.type = 'lowpass';
            noiseFilter.frequency.value = 3200;
            noiseNode.connect(noiseFilter);
            noiseFilter.connect(noiseGain);
        } else {
            noiseNode.connect(noiseGain);
        }
        noiseGain.connect(ctx.destination);
        noiseNode.start();
    }

    function setAmbient(kind) {
        if (!state) return;
        state.ambient = kind;
        persist();
        if (state.mode !== 'idle') startAmbient(kind, state.ambientVolume);
        else stopAmbient();
    }

    function setAmbientVolume(v) {
        if (!state) return;
        state.ambientVolume = v;
        persist();
        if (noiseGain) noiseGain.gain.value = v;
    }

    // ---- Cronometro ----
    function computeRemainingMs() {
        if (!state || state.mode === 'idle') return 0;
        if (!state.running) return state.remainingMs || 0;
        return Math.max(0, (state.phaseEndsAt || 0) - nowMs());
    }

    function publicState() {
        if (!state) return { mode: 'idle', running: false, remainingSec: 0, totalSec: 0, cycles: 0, ambient: 'none', ambientVolume: 0.4 };
        var totalSec = (state.mode === 'focus' ? state.focusMin : state.breakMin) * 60;
        return {
            mode: state.mode,
            running: state.running,
            remainingSec: Math.ceil(computeRemainingMs() / 1000),
            totalSec: totalSec,
            cycles: state.cycles,
            materiaId: state.materiaId,
            materiaNome: state.materiaNome,
            focusMin: state.focusMin,
            breakMin: state.breakMin,
            ambient: state.ambient,
            ambientVolume: state.ambientVolume,
            audioState: audioCtx ? audioCtx.state : 'none'
        };
    }

    function emitUpdate() {
        window.dispatchEvent(new CustomEvent('pomodoro:update', { detail: publicState() }));
    }

    function handlePhaseEnd() {
        beep();
        if (state.mode === 'focus') {
            state.cycles += 1;
            logCycle();
            state.mode = 'break';
        } else {
            state.mode = 'focus';
        }
        var phaseMs = (state.mode === 'focus' ? state.focusMin : state.breakMin) * 60000;
        state.phaseEndsAt = nowMs() + phaseMs;
        if (!state.running) state.remainingMs = phaseMs;
        persist();
    }

    function tick() {
        if (!state || state.mode === 'idle' || !state.running) return;
        if (computeRemainingMs() <= 0) handlePhaseEnd();
        emitUpdate();
    }

    function startTickLoop() {
        if (tickHandle) return;
        tickHandle = setInterval(tick, 1000);
    }
    function stopTickLoop() {
        if (tickHandle) { clearInterval(tickHandle); tickHandle = null; }
    }

    function start(opts) {
        state = {
            userId: window.POMODORO_USER_ID || null,
            materiaId: opts.materiaId,
            materiaNome: opts.materiaNome,
            mode: 'focus',
            running: true,
            phaseEndsAt: nowMs() + opts.focusMin * 60000,
            remainingMs: null,
            focusMin: opts.focusMin,
            breakMin: opts.breakMin,
            cycles: 0,
            sessaoUid: 'pm-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10),
            ambient: (state && state.ambient) || 'none',
            ambientVolume: (state && typeof state.ambientVolume === 'number') ? state.ambientVolume : 0.4
        };
        persist();
        startTickLoop();
        if (state.ambient !== 'none') startAmbient(state.ambient, state.ambientVolume);
        emitUpdate();
    }

    function pause() {
        if (!state || !state.running) return;
        state.remainingMs = computeRemainingMs();
        state.running = false;
        persist();
        stopTickLoop();
        stopAmbient();
        emitUpdate();
    }

    function resume() {
        if (!state || state.running || state.mode === 'idle') return;
        var phaseTotalMs = (state.mode === 'focus' ? state.focusMin : state.breakMin) * 60000;
        var remainingMs = typeof state.remainingMs === 'number' ? state.remainingMs : phaseTotalMs;
        state.phaseEndsAt = nowMs() + remainingMs;
        state.remainingMs = null;
        state.running = true;
        persist();
        startTickLoop();
        if (state.ambient !== 'none') startAmbient(state.ambient, state.ambientVolume);
        emitUpdate();
    }

    function skip() {
        if (!state || state.mode === 'idle') return;
        handlePhaseEnd();
        emitUpdate();
    }

    function finish() {
        if (!state) return { cycles: 0, totalMin: 0 };
        var cycles = state.cycles;
        var totalMin = cycles * state.focusMin;
        stopTickLoop();
        stopAmbient();
        state = null;
        persist();
        window.dispatchEvent(new CustomEvent('pomodoro:finished', { detail: { cycles: cycles, totalMin: totalMin } }));
        emitUpdate();
        return { cycles: cycles, totalMin: totalMin };
    }

    function init() {
        var loaded = loadState();
        var currentUserId = window.POMODORO_USER_ID || null;
        if (loaded && loaded.mode && loaded.mode !== 'idle' && loaded.userId === currentUserId) {
            state = loaded;
            if (state.running) {
                var overdueMs = nowMs() - state.phaseEndsAt;
                if (overdueMs > ABANDON_MS) {
                    // Sessao abandonada (aba fechada por muito tempo) - nao credita ciclo retroativo.
                    state = null;
                    persist();
                } else {
                    if (overdueMs > 0) handlePhaseEnd();
                    startTickLoop();
                    if (state.ambient !== 'none') startAmbient(state.ambient, state.ambientVolume);
                }
            }
        }
        emitUpdate();
    }

    // init() so nao toca DOM (so localStorage/timer/audio) - roda de imediato, sem
    // esperar DOMContentLoaded, pra que getState() ja reflita a sessao restaurada
    // quando o script da pagina (carregado depois deste, no fim do body) executar.
    init();

    return {
        init: init,
        getState: publicState,
        start: start,
        pause: pause,
        resume: resume,
        skip: skip,
        finish: finish,
        setAmbient: setAmbient,
        setAmbientVolume: setAmbientVolume
    };
})();
