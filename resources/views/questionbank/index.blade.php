@extends('layouts.app')

@section('title', __('bank.page_title'))
@section('mobile_title', __('bank.mobile_title'))

@section('topbar_title', __('bank.mobile_title'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/page-banco-config.css') }}">
@endpush

@section('content')
    @php
        $subjectIcons = ['science', 'biotech', 'genetics', 'microbiology', 'menu_book', 'school', 'psychology'];
    @endphp
    <div class="bc-mock-banco-page py-4 px-3 px-md-4">
        <div class="bc-mock-banco-page__glow" aria-hidden="true"></div>
        <div class="bc-mock-banco-page__inner">
            <header class="bc-mock-editorial">
                @php
                    $titleWords = preg_split('/\s+/u', trim((string) __('bank.header.title')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $titleLast = count($titleWords) > 1 ? array_pop($titleWords) : null;
                    $titleRest = $titleLast !== null ? implode(' ', $titleWords) : (string) __('bank.header.title');
                @endphp
                <h1 class="bc-mock-editorial__title">
                    @if ($titleLast !== null)
                        {{ $titleRest }} <span class="bc-mock-editorial__accent">{{ $titleLast }}</span>
                    @else
                        {{ $titleRest }}
                    @endif
                </h1>
                <p class="bc-mock-editorial__lead">{{ __('bank.lead_paragraph') }}</p>
            </header>

            @if ($materias->isEmpty())
                <section class="bc-mock-panel">
                    <p class="text-muted mb-0">{{ __('bank.no_subjects') }}</p>
                </section>
            @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <form action="{{ route('simulation.create') }}" method="post" id="bc-qbank-form">
                        @csrf

                        <section class="bc-mock-panel">
                            <div class="bc-mock-panel__head">
                                <h2 class="bc-mock-panel__title">{{ __('bank.section_subjects') }}</h2>
                                <span class="bc-mock-pill">{{ __('bank.select_subject') }}</span>
                            </div>
                                <div class="bc-mock-subject-grid">
                                    @foreach ($materias as $idx => $materia)
                                        @php $ico = $subjectIcons[$idx % count($subjectIcons)]; @endphp
                                        <label class="bc-mock-subject-card">
                                            <input type="radio" name="materia" value="{{ $materia->id }}" required
                                                   @checked($loop->first)>
                                            <span class="bc-mock-subject-card__box">
                                                <span class="material-symbols-outlined bc-mock-subject-card__ico" aria-hidden="true">{{ $ico }}</span>
                                                <span class="bc-mock-subject-card__label">{{ $materia->nome }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                        </section>

                        <div class="bc-mock-two-col">
                            <section class="bc-mock-panel mb-0">
                                <h2 class="bc-mock-panel__title mb-4">{{ __('bank.section_questions') }}</h2>
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <span class="bc-mock-range-display" id="qbankQtyDisplay">20</span>
                                    <span class="bc-mock-range-meta">{{ __('bank.num_questions') }}</span>
                                </div>
                                <input type="range" class="bc-mock-range-input" id="qbankQtyRange" min="1" max="200" value="20"
                                       aria-valuemin="1" aria-valuemax="200" aria-valuenow="20"
                                       aria-labelledby="qbankQtyDisplay">
                                <div class="bc-mock-range-ticks">
                                    <span>1</span>
                                    <span>200</span>
                                </div>
                                <input type="hidden" name="quantidade" id="qbankQtyHidden" value="20">
                                <p class="small text-muted mt-3 mb-0">{{ __('bank.num_questions_hint') }}</p>
                            </section>
                            <section class="bc-mock-panel mb-0">
                                <h2 class="bc-mock-panel__title mb-4">{{ __('bank.time_label') }}</h2>
                                <div class="bc-mock-time-row">
                                    <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
                                    <span class="fw-bold">{{ __('bank.time_value') }}</span>
                                </div>
                                <p class="small text-muted mt-3 mb-0">{{ __('bank.time_hint') }}</p>
                            </section>
                        </div>

                        <section class="bc-mock-panel">
                            <h2 class="bc-mock-panel__title mb-4">{{ __('bank.mode_label') }}</h2>
                            <div class="bc-mock-mode-grid">
                                <label class="bc-mock-mode-card">
                                    <input type="radio" name="modo" value="estudo" id="radioEstudo" checked>
                                    <span class="bc-mock-mode-card__box">
                                        <span class="bc-mock-mode-card__check" aria-hidden="true">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </span>
                                        <div class="bc-mock-mode-card__row pe-5">
                                            <span class="material-symbols-outlined" aria-hidden="true">school</span>
                                            <span class="bc-mock-mode-card__title">{{ __('bank.mode_study.title') }}</span>
                                        </div>
                                        <p class="bc-mock-mode-card__desc">{{ __('bank.mode_study.desc') }}</p>
                                    </span>
                                </label>
                                <label class="bc-mock-mode-card">
                                    <input type="radio" name="modo" value="exame" id="radioExame">
                                    <span class="bc-mock-mode-card__box">
                                        <span class="bc-mock-mode-card__check" aria-hidden="true">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </span>
                                        <div class="bc-mock-mode-card__row pe-5">
                                            <span class="material-symbols-outlined" aria-hidden="true">timer</span>
                                            <span class="bc-mock-mode-card__title">{{ __('bank.mode_exam.title') }}</span>
                                        </div>
                                        <p class="bc-mock-mode-card__desc">{{ __('bank.mode_exam.desc') }}</p>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <div id="examWarning" class="bc-exam-warning bc-mock-exam-warn d-none">
                            <div class="d-flex gap-3">
                                <span class="material-icons text-warning">warning</span>
                                <div>
                                    <h6 class="fw-bold mb-1">{{ __('bank.exam_warn.title') }}</h6>
                                    <ul class="mb-0 small text-muted ps-3">
                                        <li>{{ __('bank.exam_warn.li1') }}</li>
                                        <li>{{ __('bank.exam_warn.li2') }}</li>
                                        <li>{{ __('bank.exam_warn.li3') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4 bc-mock-summary-col">
                    <div class="bc-mock-summary-sticky">
                        <div class="bc-mock-glass-card">
                            <h3 class="bc-mock-glass-card__title">{{ __('bank.summary_title') }}</h3>
                            <ul class="bc-mock-summary-list">
                                <li>
                                    <span>{{ __('bank.summary_questions') }}</span>
                                    <span id="qbankSummaryQty">20</span>
                                </li>
                                <li>
                                    <span>{{ __('bank.summary_time') }}</span>
                                    <span>{{ __('bank.time_value') }}</span>
                                </li>
                            </ul>
                            <button type="submit" form="bc-qbank-form" class="bc-mock-btn-start">
                                <span class="material-symbols-outlined" aria-hidden="true">play_arrow</span>
                                {{ __('bank.submit') }}
                            </button>
                        </div>
                        <div class="bc-mock-badges-grid">
                            <div class="bc-mock-badge-mini">
                                <span class="material-symbols-outlined" aria-hidden="true">history_edu</span>
                                <p>{{ __('bank.badge_reviewed') }}</p>
                            </div>
                            <div class="bc-mock-badge-mini">
                                <span class="material-symbols-outlined" aria-hidden="true">bolt</span>
                                <p>{{ __('bank.badge_focus') }}</p>
                            </div>
                            <div class="bc-mock-badge-mini">
                                <span class="material-symbols-outlined" aria-hidden="true">verified</span>
                                <p>{{ __('bank.badge_sources') }}</p>
                            </div>
                            <div class="bc-mock-badge-mini">
                                <span class="material-symbols-outlined" aria-hidden="true">leaderboard</span>
                                <p>{{ __('bank.badge_track') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <p class="bc-mock-footer-note mb-0">&copy; {{ date('Y') }} {{ __('bank.footer_copy') }}</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const range = document.getElementById('qbankQtyRange');
    const hidden = document.getElementById('qbankQtyHidden');
    const display = document.getElementById('qbankQtyDisplay');
    const summary = document.getElementById('qbankSummaryQty');
    if (!range || !hidden || !display) return;
    function sync() {
        var v = range.value;
        hidden.value = v;
        display.textContent = v;
        if (summary) summary.textContent = v;
        range.setAttribute('aria-valuenow', v);
    }
    range.addEventListener('input', sync);
    sync();

    const radioEstudo = document.getElementById('radioEstudo');
    const radioExame = document.getElementById('radioExame');
    const examWarning = document.getElementById('examWarning');
    function toggleWarning() {
        if (examWarning) examWarning.classList.toggle('d-none', !radioExame.checked);
    }
    if (radioEstudo) radioEstudo.addEventListener('change', toggleWarning);
    if (radioExame) radioExame.addEventListener('change', toggleWarning);
})();
</script>
@endpush
