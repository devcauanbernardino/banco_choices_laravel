@extends('layouts.public')

@section('title', __('signup.page_title.materias'))

@section('body_attr')
 class="signup-materias-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-materias.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
@endpush

@section('content')
<div class="signup-materias-wrap">
    <div class="signup-materias-blobs" aria-hidden="true">
        <div class="signup-materias-blobs__one"></div>
        <div class="signup-materias-blobs__two"></div>
    </div>

    <header class="signup-materias-topbar">
        <a href="{{ route('home') }}" class="signup-materias-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;" aria-hidden="true">arrow_back</span>
            <span>{{ __('signup.back_home') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="signup-materias-main">
        <div class="signup-materias-inner">
            <div class="signup-materias-lead">
                <h1 class="signup-materias-lead__title">{{ __('signup.materias.h1') }}</h1>
                <p class="signup-materias-lead__text">
                    {{ __('signup.materias.lead_before') }}
                    <strong>{{ __('signup.materias.lead_strong') }}</strong>
                    {{ __('signup.materias.lead_after') }}
                </p>
            </div>

            <div class="signup-materias-steps" aria-label="{{ __('signup.steps.aria') }}">
                <div class="signup-materias-steps__item is-active" aria-current="step">
                    <div class="signup-materias-steps__num">1</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.materias') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">2</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.plan') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">3</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.pago') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">4</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.confirmacion') }}</div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form id="materiasForm" action="{{ route('signup.materias') }}" method="POST">
                @csrf
                <div class="signup-materias-grid">
                    @foreach ($materias as $materia)
                        @php
                            $icon = \App\Support\MateriaLayout::materialIcon($materia->nome);
                            $hintKey = \App\Support\MateriaLayout::hintKey($materia->nome);
                        @endphp
                        <label class="signup-materias-card" id="card-{{ $materia->id }}">
                            <input type="checkbox" name="materias[]" value="{{ $materia->id }}"
                                   class="visually-hidden"
                                   @checked(in_array($materia->id, (array) old('materias', []), false))>
                            <span class="signup-materias-card__icon" aria-hidden="true">
                                <span class="material-symbols-outlined">{{ $icon }}</span>
                            </span>
                            <span class="signup-materias-card__body">
                                <span class="signup-materias-card__name">{{ $materia->nome }}</span>
                                <span class="signup-materias-card__hint">{{ __('signup.materia.hint.' . $hintKey) }}</span>
                            </span>
                            <span class="signup-materias-card__check" aria-hidden="true">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="signup-materias-actions">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold px-4 px-md-5 py-3 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                        {{ __('signup.btn.continue_plan') }}
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/signup-select-materias.js') }}"></script>
@endpush
