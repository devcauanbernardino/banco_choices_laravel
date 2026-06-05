@extends('layouts.public')

@section('title', __('demo.questao.page_title'))
@section('body_attr', ' class="lp-body demo-body"')

@push('styles')
    @include('pages.partials.landing-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}?v={{ filemtime(public_path('assets/css/demo.css')) }}">
@endpush

@section('content')
@php
    $opcoes = $questao->getOpcoes();
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    $progressPct = $total > 0 ? (int) round(($indice / $total) * 100) : 0;
@endphp

<div class="demo-quiz">
    <header class="demo-quiz__topbar">
        <a href="{{ route('home') }}" class="demo-quiz__brand">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="Banco de Choices" class="demo-quiz__logo" width="36" height="36">
        </a>
        <div class="demo-quiz__progress">
            <div class="demo-quiz__progress-bar" style="width: {{ $progressPct }}%"></div>
        </div>
        <span class="demo-quiz__progress-label">
            {{ __('demo.questao.progress', ['n' => $indice, 'total' => $total]) }}
        </span>
    </header>

    <main class="demo-quiz__canvas">
        <article class="demo-quiz__card" id="demoQuizCard">
            <p class="demo-quiz__materia">{{ $materiaNome }}</p>
            <h1 class="demo-quiz__enunciado">{!! nl2br(e($questao->getPergunta())) !!}</h1>

            <ul class="demo-quiz__opts" role="radiogroup" id="demoOpts">
                @foreach($opcoes as $i => $opt)
                    @php $letra = $letras[$i] ?? ''; @endphp
                    <li>
                        <label class="demo-quiz__opt" data-letter="{{ $letra }}">
                            <input type="radio" name="resposta" value="{{ $letra }}" class="demo-quiz__opt-input">
                            <span class="demo-quiz__opt-letter">{{ $letra }}</span>
                            <span class="demo-quiz__opt-text">{!! nl2br(e($opt)) !!}</span>
                        </label>
                    </li>
                @endforeach
            </ul>

            <div class="demo-quiz__feedback" id="demoFeedback" hidden>
                <div class="demo-quiz__feedback-head">
                    <span class="lp-badge">{{ __('demo.questao.justification') }}</span>
                </div>
                <div class="demo-quiz__feedback-body" id="demoFeedbackText"></div>
                <p class="demo-quiz__feedback-source" id="demoFeedbackSource" hidden>
                    <strong>{{ __('demo.questao.source') }}:</strong>
                    <span id="demoFeedbackSourceText"></span>
                </p>
            </div>

            <div class="demo-quiz__actions">
                <button type="button" class="btn lp-btn-primary demo-quiz__btn" id="demoBtnRespond" disabled>
                    {{ __('demo.questao.respond') }}
                </button>
            </div>
        </article>
    </main>

    <footer class="demo-quiz__exitbar">
        <a href="{{ route('demo.show') }}" class="demo-quiz__exit-link">
            <i class="bi bi-x-lg"></i> {{ __('demo.questao.exit') }}
        </a>
    </footer>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var optsEl = document.getElementById('demoOpts');
    var btn = document.getElementById('demoBtnRespond');
    var feedback = document.getElementById('demoFeedback');
    var feedbackText = document.getElementById('demoFeedbackText');
    var feedbackSrc = document.getElementById('demoFeedbackSource');
    var feedbackSrcText = document.getElementById('demoFeedbackSourceText');
    var idx = {{ (int) $indice }};
    var total = {{ (int) $total }};
    var responded = false;
    var nextUrl = null;
    var responderUrl = @json(route('demo.responder'));
    var csrf = @json(csrf_token());

    optsEl.addEventListener('change', function () {
        if (responded) return;
        var v = optsEl.querySelector('input:checked');
        btn.disabled = !v;
    });

    btn.addEventListener('click', function () {
        if (!responded) {
            submitAnswer();
        } else if (nextUrl) {
            window.location.href = nextUrl;
        }
    });

    function submitAnswer() {
        var v = optsEl.querySelector('input:checked');
        if (!v) return;
        btn.disabled = true;
        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('resposta', v.value);

        fetch(responderUrl, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (r.status === 403 || r.status === 400) {
                    return r.json().then(function (j) {
                        if (j && j.paywall_url) window.location.href = j.paywall_url;
                        return null;
                    });
                }
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.ok) return;
                responded = true;
                renderFeedback(j);
                btn.disabled = false;
                if (j.done) {
                    btn.textContent = @json(__('demo.questao.see_result'));
                    nextUrl = j.paywall_url;
                } else {
                    btn.textContent = @json(__('demo.questao.next'));
                    nextUrl = j.next_url;
                }
            })
            .catch(function () {
                btn.disabled = false;
            });
    }

    function letterFromAnswer(raw, numOpts) {
        var s = String(raw == null ? '' : raw).trim();
        if (s === '') return '';
        var up = s.toUpperCase();
        if (/^[A-Z]$/.test(up)) return up;
        var i = parseInt(s, 10);
        if (!isNaN(i) && i >= 0 && i < numOpts) {
            return String.fromCharCode(65 + i);
        }
        return up;
    }

    function renderFeedback(j) {
        var numOpts = document.querySelectorAll('.demo-quiz__opt').length;
        var correct = letterFromAnswer(j.resposta_correta, numOpts);
        var user = letterFromAnswer(j.resposta_usuario, numOpts);
        document.querySelectorAll('.demo-quiz__opt').forEach(function (lbl) {
            var letter = String(lbl.getAttribute('data-letter') || '').toUpperCase();
            lbl.classList.add('is-locked');
            if (letter === correct) lbl.classList.add('is-correct');
            if (letter === user && letter !== correct) lbl.classList.add('is-wrong');
        });
        document.querySelectorAll('.demo-quiz__opt-input').forEach(function (i) { i.disabled = true; });
        var fb = String(j.feedback || '').trim();
        var showedPanel = false;
        if (!fb && correct) {
            fb = @json(__('demo.questao.correct_was')) + ': ' + correct;
            if (typeof j.acertou === 'boolean') {
                fb += j.acertou ? ' ✓' : ' ✗';
            }
            feedbackText.textContent = fb;
            showedPanel = true;
        } else if (fb) {
            feedbackText.innerHTML = String(j.feedback).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            showedPanel = true;
        } else {
            feedbackText.textContent = '';
        }
        feedback.hidden = !showedPanel;
    }
})();
</script>
@endpush
