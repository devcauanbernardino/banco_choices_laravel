@extends('layouts.public')

@section('title', __('index.page_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --accent-purple: #6a0392;
        --accent-purple-light: rgba(106, 3, 146, 0.08);
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* Hero */
    .landing-hero {
        background: linear-gradient(135deg, #6a0392 0%, #460161 100%);
        color: #fff;
        padding: 5rem 1.5rem 4rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .landing-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.06) 0%, transparent 50%);
        pointer-events: none;
    }
    .landing-hero h1 {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: clamp(2rem, 5vw, 3.2rem);
        margin-bottom: 1rem;
    }
    .landing-hero .lead {
        font-size: 1.15rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto 2rem;
    }

    /* Features */
    .landing-features {
        padding: 4rem 1.5rem;
        background: var(--app-surface-1, #f8f9fa);
    }
    .feature-card {
        background: #fff;
        border-radius: 16px;
        padding: 2rem 1.5rem;
        text-align: center;
        border: 1px solid #e5e7eb;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(106, 3, 146, 0.12);
    }
    .feature-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: var(--accent-purple-light);
        margin-bottom: 1.25rem;
    }
    .feature-icon .material-icons {
        font-size: 32px;
        color: var(--accent-purple);
    }

    /* Subjects */
    .landing-subjects {
        padding: 4rem 1.5rem;
    }

    /* Footer */
    .landing-footer {
        background: #1a1a2e;
        color: rgba(255,255,255,0.7);
        padding: 2.5rem 1.5rem;
        text-align: center;
    }
    .landing-footer a {
        color: rgba(255,255,255,0.85);
        text-decoration: none;
    }
    .landing-footer a:hover {
        color: #fff;
    }
</style>
@endpush

@section('content')
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #6a0392;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('img/logo-bd-transparente.png') }}" alt="Banco de Choices" height="36">
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">{{ __('index.nav_login') }}</a>
                <a href="{{ route('signup.materias') }}" class="btn btn-light btn-sm px-3 fw-bold">{{ __('index.nav_signup') }}</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="landing-hero">
        <div class="container">
            <h1>{{ __('index.hero_title') }}</h1>
            <p class="lead">{{ __('index.hero_lead') }}</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('signup.materias') }}" class="btn btn-light btn-lg fw-bold px-4 py-3 rounded-pill shadow-sm">
                    {{ __('index.hero_cta_primary') }}
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                    {{ __('index.hero_cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="landing-features">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">{{ __('index.features_title') }}</h2>
            <p class="text-center text-muted mb-5">{{ __('index.features_subtitle') }}</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">psychology</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('index.feature1_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('index.feature1_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">insights</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('index.feature2_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('index.feature2_desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <span class="material-icons">emoji_events</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('index.feature3_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('index.feature3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Subjects --}}
    <section class="landing-subjects">
        <div class="container text-center">
            <h2 class="fw-bold mb-2">{{ __('index.subjects_title') }}</h2>
            <p class="text-muted mb-4">{{ __('index.subjects_subtitle') }}</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                <span class="badge bg-light text-dark p-2 px-3 rounded-pill border fs-6">{{ __('index.subject_example1') }}</span>
                <span class="badge bg-light text-dark p-2 px-3 rounded-pill border fs-6">{{ __('index.subject_example2') }}</span>
                <span class="badge bg-light text-dark p-2 px-3 rounded-pill border fs-6">{{ __('index.subject_example3') }}</span>
            </div>
            <a href="{{ route('signup.materias') }}" class="btn btn-primary btn-lg fw-bold px-4 py-3 rounded-pill shadow-sm">
                {{ __('index.subjects_cta') }}
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="landing-footer">
        <div class="container">
            <div class="mb-3">
                <img src="{{ asset('img/logo-bd-transparente.png') }}" alt="Banco de Choices" height="28" class="opacity-75">
            </div>
            <nav class="d-flex flex-wrap justify-content-center gap-3 mb-3 small">
                <a href="#privacidad">{{ __('index.footer_privacy') }}</a>
                <a href="#terminos">{{ __('index.footer_terms') }}</a>
                <a href="mailto:contato@bancodechoices.com">{{ __('index.footer_contact') }}</a>
            </nav>
            <p class="small mb-0 opacity-75">&copy; {{ date('Y') }} {{ __('index.footer_copy') }}</p>
        </div>
    </footer>
@endsection
