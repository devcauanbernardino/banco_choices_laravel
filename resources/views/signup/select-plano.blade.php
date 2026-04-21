@extends('layouts.public')

@section('title', __('signup.page_title.plano'))

@section('body_attr')
 class="signup-plano-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-materias.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-plano.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
@endpush

@section('content')
<div class="signup-plano-wrap">
    <div class="signup-plano-blobs" aria-hidden="true">
        <div class="signup-plano-blobs__one"></div>
        <div class="signup-plano-blobs__two"></div>
    </div>

    <header class="signup-materias-topbar">
        <a href="{{ route('signup.materias') }}" class="signup-materias-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;" aria-hidden="true">arrow_back</span>
            <span>{{ __('signup.back_materias') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="signup-materias-main">
        <div class="signup-plano-inner">
            <div class="signup-plano-hero">
                <h1 class="signup-plano-hero__title">
                    {{ __('signup.plano.hero_prefix') }}<span class="signup-plano-hero__gradient">{{ __('signup.plano.hero_highlight') }}</span>
                </h1>
                <p class="signup-plano-hero__lead">{{ __('signup.plano.lead') }}</p>
            </div>

            <div class="signup-materias-steps" aria-label="{{ __('signup.steps.aria') }}">
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">1</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.materias') }}</div>
                </div>
                <div class="signup-materias-steps__item is-active" aria-current="step">
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

            <div class="signup-plano-selected">
                <h2 class="signup-plano-selected__head">
                    <span class="material-symbols-outlined signup-plano-icon--fill" aria-hidden="true">check_circle</span>
                    {{ __('signup.plano.selected') }}
                </h2>
                <div class="signup-plano-selected__tags">
                    @foreach ($materiasInfo as $materia)
                        <span class="signup-plano-tag">
                            <span class="material-symbols-outlined" style="font-size: 1rem;" aria-hidden="true">menu_book</span>
                            <span>{{ $materia->nome }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="signup-plano-grid">
                @foreach ($plans as $plan)
                    <form method="POST" class="signup-plano-form" action="{{ route('signup.plano') }}">
                        @csrf
                        <article
                            class="signup-plano-card {{ $plan['popular'] ? 'signup-plano-card--featured' : 'signup-plano-card--surface' }}"
                            aria-labelledby="plan-title-{{ $plan['id'] }}"
                        >
                            @if ($plan['popular'])
                                <span class="signup-plano-card__ribbon">{{ __('signup.plano.most_popular') }}</span>
                            @endif

                            <div class="signup-plano-card__inner">
                                <header class="signup-plano-card__head">
                                    <h3 class="signup-plano-card__title" id="plan-title-{{ $plan['id'] }}">{{ $plan['name'] }}</h3>
                                    <p class="signup-plano-card__desc">{{ $plan['description'] }}</p>
                                </header>

                                <section class="signup-plano-card__pricing" aria-label="{{ $plan['name'] }}">
                                    <div class="signup-plano-card__price-row">
                                        <span class="signup-plano-card__currency">$</span>
                                        <span class="signup-plano-card__amount">{{ number_format($plan['price'] * count($materiasInfo), 2, ',', '.') }}</span>
                                        <span class="signup-plano-card__ars">ARS</span>
                                    </div>
                                    <p class="signup-plano-card__period">{{ __('signup.plano.period_total', ['duration' => $plan['duration']]) }}</p>
                                    @if (!empty($plan['badge']))
                                        <span class="signup-plano-card__badge-inline">{{ $plan['badge'] }}</span>
                                    @endif
                                </section>

                                <ul class="signup-plano-card__features" role="list">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="signup-plano-card__feature">
                                            <span class="material-symbols-outlined signup-plano-icon--fill" aria-hidden="true">check_circle</span>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <footer>
                                    <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                                    <button type="submit" class="signup-plano-card__cta {{ $plan['popular'] ? 'signup-plano-card__cta--featured' : 'signup-plano-card__cta--surface' }}">
                                        {{ $plan['popular'] ? __('signup.plano.cta_featured') : __('signup.plano.choose') }}
                                    </button>
                                </footer>
                            </div>
                        </article>
                    </form>
                @endforeach
            </div>

            <section class="signup-plano-social" aria-labelledby="signup-plano-social-title">
                <h2 id="signup-plano-social-title" class="signup-plano-social__title">{{ __('signup.plano.social_title') }}</h2>
                <div class="signup-plano-testimonials">
                    <figure class="signup-plano-testimonials__card">
                        <blockquote class="signup-plano-testimonials__quote">{{ __('signup.plano.testimonial.1.quote') }}</blockquote>
                        <figcaption class="signup-plano-testimonials__person">
                            <div class="signup-plano-testimonials__avatar" aria-hidden="true">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div>
                                <p class="signup-plano-testimonials__name">{{ __('signup.plano.testimonial.1.name') }}</p>
                                <p class="signup-plano-testimonials__role">{{ __('signup.plano.testimonial.1.role') }}</p>
                            </div>
                        </figcaption>
                    </figure>
                    <figure class="signup-plano-testimonials__card signup-plano-testimonials__card--alt">
                        <blockquote class="signup-plano-testimonials__quote">{{ __('signup.plano.testimonial.2.quote') }}</blockquote>
                        <figcaption class="signup-plano-testimonials__person">
                            <div class="signup-plano-testimonials__avatar" aria-hidden="true">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div>
                                <p class="signup-plano-testimonials__name">{{ __('signup.plano.testimonial.2.name') }}</p>
                                <p class="signup-plano-testimonials__role">{{ __('signup.plano.testimonial.2.role') }}</p>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            </section>

            <div class="signup-plano-trust">
                <div class="signup-plano-trust__item">
                    <span class="material-symbols-outlined">verified_user</span>
                    <span class="signup-plano-trust__label">{{ __('signup.plano.trust.hipaa') }}</span>
                </div>
                <div class="signup-plano-trust__item">
                    <span class="material-symbols-outlined">security</span>
                    <span class="signup-plano-trust__label">{{ __('signup.plano.trust.gdpr') }}</span>
                </div>
                <div class="signup-plano-trust__item">
                    <span class="material-symbols-outlined">encrypted</span>
                    <span class="signup-plano-trust__label">{{ __('signup.plano.trust.ssl') }}</span>
                </div>
                <div class="signup-plano-trust__item">
                    <span class="material-symbols-outlined">medical_services</span>
                    <span class="signup-plano-trust__label">{{ __('signup.plano.trust.iso') }}</span>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
