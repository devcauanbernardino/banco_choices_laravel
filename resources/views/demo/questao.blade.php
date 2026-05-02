@extends('layouts.public')

@section('title', __('demo.questao.page_title'))

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-mock.css') }}">
@endpush

@section('body_attr')
 class="quiz-mock-body"
@endsection

@section('content')
@php
    $opcoes = $questao->getOpcoes();
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    $demoSignupUrl = route('signup.materias', array_filter(['materia_id' => $materiaDemoId ?? null]));
@endphp

<div class="quiz-mock">
    <header class="quiz-mock-header">
        <div class="quiz-mock-brand">
            <a href="{{ route('demo.show') }}" class="text-decoration-none d-inline-flex">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" class="quiz-mock-logo" width="40" height="40">
            </a>
        </div>
        <div class="quiz-mock-header-info">
            <div class="quiz-mock-meta">
                <p class="quiz-mock-meta-title">{{ __('demo.questao.demo_mode') }}</p>
                <p class="quiz-mock-meta-sub">{{ $materiaNome }}</p>
            </div>
        </div>
    </header>

    <main class="quiz-mock-canvas">
        <section class="quiz-mock-question-col">
            <div class="quiz-mock-breadcrumb">
                <nav class="quiz-mock-breadcrumb-nav" aria-label="{{ __('quiz.breadcrumb_aria') }}">
                    <span>{{ __('demo.questao.demo_mode') }}</span>
                    <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                    <span>{{ __('quiz.question_of', ['current' => $indice, 'total' => $total]) }}</span>
                </nav>
                <span class="quiz-mock-tag">{{ $materiaNome }}</span>
            </div>

            @if (! empty($quiz_translation_overlay_missing))
                <div class="quiz-mock-i18n-note" role="status">
                    <span class="material-symbols-outlined quiz-mock-i18n-note__ico" aria-hidden="true">translate</span>
                    <p class="quiz-mock-i18n-note__text">{{ __('quiz.bank_original_language_notice') }}</p>
                </div>
            @endif

            <article class="quiz-mock-question-card">
                <h2 class="quiz-mock-question-text">{{ $questao->getPergunta() }}</h2>

                @if (count($opcoes) === 0)
                    <div class="alert alert-warning mb-0" role="alert">{{ __('quiz.no_options') }}</div>
                @endif

                <div class="quiz-mock-options" id="demoOpts" role="radiogroup" aria-label="{{ __('quiz.options_aria') }}">
                    @foreach ($opcoes as $i => $opcao)
                        <label class="quiz-mock-option" data-opt="{{ $i }}">
                            <input type="radio" name="demo_resposta" value="{{ $i }}">
                            <span class="quiz-mock-option-letter" aria-hidden="true">{{ $letras[$i] ?? ($i + 1) }}</span>
                            <span class="quiz-mock-option-text">{{ $opcao }}</span>
                        </label>
                    @endforeach
                </div>

                <div id="demoFb" class="d-none quiz-mock-feedback mt-3"></div>

                <div class="quiz-mock-actions mt-4">
                    <span class="quiz-mock-btn quiz-mock-btn-ghost is-disabled" aria-hidden="true">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span>{{ __('quiz.prev') }}</span>
                    </span>
                    <div class="quiz-mock-progress-mini">
                        <div class="quiz-mock-progress-bar">
                            <div class="quiz-mock-progress-fill" style="width: {{ ($indice / max(1, $total)) * 100 }}%"></div>
                        </div>
                        <span class="quiz-mock-progress-label">{{ __('quiz.exam_progress') }}</span>
                    </div>
                    <button type="button" class="quiz-mock-btn quiz-mock-btn-primary d-none" id="demoBtnNext">
                        <span>{{ __('demo.questao.btn_next') }}</span>
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                    </button>
                </div>
            </article>
        </section>

        <aside class="quiz-mock-sidebar d-none d-md-flex">
            <div class="quiz-mock-card">
                <div class="quiz-mock-card-head">
                    <h3 class="quiz-mock-card-title">{{ __('demo.questao.sidebar_title') }}</h3>
                    <span class="quiz-mock-card-meta">{{ $indice }} / {{ $total }}</span>
                </div>
                <p class="small text-secondary mb-0">{{ __('demo.questao.sidebar_hint') }}</p>
            </div>
        </aside>
    </main>
</div>

<div class="modal fade" id="demoPaywallModal" tabindex="-1" aria-labelledby="demoPaywallModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-2">
                    <span class="material-symbols-outlined text-primary fs-3" aria-hidden="true">favorite</span>
                </div>
                <div>
                    <h2 class="modal-title h5 fw-bold mb-1" id="demoPaywallModalLabel">{{ __('demo.paywall_modal.title') }}</h2>
                    <p class="small text-muted mb-0">{{ __('demo.paywall_modal.lead') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('referral.modal_close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-grid gap-2">
                    <a class="btn btn-primary btn-lg" id="demoPaywallLinkSignup" href="{{ $demoSignupUrl }}">{{ __('demo.paywall_modal.cta_signup') }}</a>
                    <a class="btn btn-outline-secondary" href="{{ route('login') }}">{{ __('demo.paywall_modal.cta_login') }}</a>
                    <button type="button" class="btn btn-link text-decoration-none" data-bs-dismiss="modal">{{ __('demo.paywall_modal.cta_later') }}</button>
                    <a class="btn btn-link text-muted text-decoration-none small" href="{{ route('demo.show') }}">{{ __('demo.paywall_modal.cta_change_subject') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
    var action = {{ json_encode(route('demo.responder')) }};
    var opts = document.getElementById('demoOpts');
    var fb = document.getElementById('demoFb');
    var btnNext = document.getElementById('demoBtnNext');
    var answering = false;
    if (!opts || !btnNext) return;

    function showPaywallModal() {
        var el = document.getElementById('demoPaywallModal');
        if (!el || typeof bootstrap === 'undefined') return;
        bootstrap.Modal.getOrCreateInstance(el).show();
    }

    opts.addEventListener('click', function (e) {
        var lbl = e.target.closest('.quiz-mock-option');
        if (!lbl || answering) return;
        var inp = lbl.querySelector('input[type="radio"]');
        if (!inp || inp.disabled) return;
        document.querySelectorAll('#demoOpts .quiz-mock-option').forEach(function (o) { o.classList.remove('is-selected'); });
        lbl.classList.add('is-selected');
        inp.checked = true;
        answering = true;

        opts.querySelectorAll('input[type="radio"]').forEach(function (r) { r.disabled = true; });

        fetch(action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ resposta: String(inp.value) }),
        }).then(function (r) {
            return r.json().then(function (j) { return { ok: r.ok, status: r.status, j: j }; });
        }).then(function (res) {
            if (!res.ok && res.status === 403 && res.j && res.j.paywall_url) {
                window.location.href = res.j.paywall_url;
                return;
            }
            var j = res.j || {};
            if (!res.ok || !j.ok) {
                answering = false;
                opts.querySelectorAll('input[type="radio"]').forEach(function (r) { r.disabled = false; });
                return;
            }
            fb.classList.remove('d-none');
            fb.className = 'mt-3 quiz-mock-feedback ' + (j.acertou ? 'is-correct' : 'is-incorrect');
            fb.innerHTML =
                '<div class="quiz-mock-feedback-head"><span class="material-symbols-outlined" aria-hidden="true">' +
                (j.acertou ? 'task_alt' : 'error') + '</span><strong>' +
                (j.acertou ? {{ json_encode(__('quiz.correct')) }} : {{ json_encode(__('quiz.incorrect')) }}) +
                '</strong></div>';
            var body = '';
            if (j.feedback) {
                body += '<p class="quiz-mock-feedback-body">' + String(j.feedback).replace(/</g, '&lt;') + '</p>';
            }
            body += '<p class="quiz-mock-feedback-body mb-0 small text-muted">{{ __('demo.questao.correct_was') }}: ' +
                String(j.resposta_correta || '') + '</p>';
            fb.innerHTML += body;
            btnNext.classList.remove('d-none');
            btnNext.onclick = function () {
                if (j.done) {
                    showPaywallModal();
                    return;
                }
                if (j.next_url) {
                    window.location.href = j.next_url;
                }
            };
        }).catch(function () {
            answering = false;
            opts.querySelectorAll('input[type="radio"]').forEach(function (r) { r.disabled = false; });
        });
    });
})();
</script>
@endpush
