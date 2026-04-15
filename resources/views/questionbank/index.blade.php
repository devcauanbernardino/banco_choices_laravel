@extends('layouts.app')

@section('title', __('bank.page_title'))
@section('mobile_title', __('bank.mobile_title'))

@section('topbar_title', __('bank.mobile_title'))

@section('content')
    <div class="container py-4" style="max-width: 750px;">
        <div class="bc-card overflow-hidden">
            {{-- Header with gradient --}}
            <div class="bc-setup-header">
                <div class="mb-2">
                    <span class="material-icons fs-1">psychology</span>
                </div>
                <h2 class="fw-bold mb-1">{{ __('bank.header.title') }}</h2>
                <p class="opacity-75 mb-0">{{ __('bank.header.sub') }}</p>
            </div>

            {{-- Form body --}}
            <div class="bc-setup-body">
                <form action="{{ route('simulation.create') }}" method="post">
                    @csrf

                    {{-- Subject selection --}}
                    <select class="form-select form-select-lg mb-4" name="materia" required>
                        <option value="" selected disabled>{{ __('bank.select_subject') }}</option>
                        @forelse ($materias as $materia)
                            <option value="{{ $materia->id }}">{{ $materia->nome }}</option>
                        @empty
                            <option disabled>{{ __('bank.no_subjects') }}</option>
                        @endforelse
                    </select>

                    <div class="row gx-4 gy-5 mb-4">
                        {{-- Number of questions --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-2 d-block">{{ __('bank.num_questions') }}</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 rounded-start-3" style="background: var(--app-surface-2);">
                                    <span class="material-icons text-muted">format_list_numbered</span>
                                </span>
                                <input type="number" class="form-control border-start-0" name="quantidade"
                                       min="1" max="200" value="20" style="border-radius: 0 12px 12px 0;">
                            </div>
                            <div class="form-text mt-2 mb-0">{{ __('bank.num_questions_hint') }}</div>
                        </div>

                        {{-- Estimated time --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-2 d-block">{{ __('bank.time_label') }}</label>
                            <div class="d-flex align-items-center p-2 px-3 rounded-3" style="height: 48px; background: var(--app-surface-2);">
                                <span class="material-icons text-primary me-2">schedule</span>
                                <span class="fw-bold">{{ __('bank.time_value') }}</span>
                            </div>
                            <div class="form-text mt-2 mb-0">{{ __('bank.time_hint') }}</div>
                        </div>
                    </div>

                    {{-- Mode selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('bank.mode_label') }}</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="form-check-input" name="modo" value="estudo" id="radioEstudo" checked>
                                <label for="radioEstudo" class="bc-mode-option">
                                    <span class="bc-mode-option__title">{{ __('bank.mode_study.title') }}</span>
                                    <span class="bc-mode-option__desc">{{ __('bank.mode_study.desc') }}</span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="form-check-input" name="modo" value="exame" id="radioExame">
                                <label for="radioExame" class="bc-mode-option">
                                    <span class="bc-mode-option__title">{{ __('bank.mode_exam.title') }}</span>
                                    <span class="bc-mode-option__desc">{{ __('bank.mode_exam.desc') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Exam warning --}}
                    <div id="examWarning" class="bc-exam-warning mb-4 d-none">
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

                    {{-- Submit button --}}
                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-2">
                        <span class="material-icons">rocket_launch</span>
                        {{ __('bank.submit') }}
                    </button>
                </form>
            </div>

            <div class="p-3 text-center border-top" style="background: var(--app-surface-2);">
                <small class="text-muted">&copy; {{ date('Y') }} {{ __('bank.footer_copy') }}</small>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const radioEstudo = document.getElementById('radioEstudo');
    const radioExame = document.getElementById('radioExame');
    const examWarning = document.getElementById('examWarning');

    function toggleWarning() {
        examWarning.classList.toggle('d-none', !radioExame.checked);
    }

    radioEstudo.addEventListener('change', toggleWarning);
    radioExame.addEventListener('change', toggleWarning);
</script>
@endpush
