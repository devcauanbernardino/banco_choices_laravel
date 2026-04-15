@extends('layouts.public')

@section('title', __('index.page_title'))

@section('body_attr')
 id="top"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/landing-page.css') }}">
@endpush

@section('content')
    <nav class="navbar navbar-expand-lg navbar-light sticky-top py-2 py-lg-3 landing-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="#top"
               aria-label="{{ __('index.page_title') }}">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt=""
                     width="200" height="56" decoding="async">
            </a>
            <button class="navbar-toggler landing-navbar-toggler" type="button"
                    data-bs-target="#navbarNav" data-bs-toggle="collapse"
                    aria-controls="navbarNav" aria-expanded="false"
                    aria-label="{{ __('index.nav.menu_aria') }}">
                <svg class="landing-navbar-toggler-svg" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 8h14M5 12h14M5 16h14" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-lg-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#caracteristicas">{{ __('index.nav.features') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#materias">{{ __('index.nav.subjects') }}</a>
                    </li>
                </ul>
                <div class="navbar-actions navbar-actions--landing">
                    <div class="navbar-actions__inner">
                        <x-language-selector />
                        <span class="navbar-actions__divider" aria-hidden="true"></span>
                        <a class="btn btn-nav-login text-decoration-none" href="{{ route('login') }}">{{ __('index.nav.login') }}</a>
                        <a class="btn btn-reverse navbar-cta-register text-decoration-none shadow-sm"
                           href="{{ route('signup.materias') }}">{{ __('index.nav.register') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill badge-soft mb-3">{{ __('index.hero.badge') }}</span>
                    <h1 class="display-3 fw-bold mb-4 text-dark">{{ __('index.hero.title') }}</h1>
                    <p class="lead mb-5">{{ __('index.hero.lead') }}</p>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a class="btn btn-reverse btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm"
                           href="{{ route('signup.materias') }}">{{ __('index.hero.cta1') }}</a>
                        <a class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm"
                           href="#caracteristicas">{{ __('index.hero.cta2') }}</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img alt="{{ __('index.hero.alt') }}" class="img-fluid w-100"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJrVuD5WbRNO1zzzt4UoFwBkgCqUzoRtNiqP7qKv66p8z6aWUXsnAbInu5f7fNYmO-OVq7q9Iz1QNQODMSbVpgi_Fuoek1UW2ktjoYWTuZNjr_OWwAa_bvgoh6UTzJHOWMjIha4zbIIhYsOmTvUdQKfa0QF5fF97ZeOo_w79ao6ZmrprXqPZKiocyGjSrMDBz577aDiuKT7rR9BM33rIXv_8DyNPxSBVSLIcjQVMpmA77tpGYy8m8J1saNHHTxjNCnRay_2tkPmaN5">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 bg-light" id="caracteristicas">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">{{ __('index.features.title') }}</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">{{ __('index.features.lead') }}</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">library_books</span>
                        </div>
                        <h4 class="fw-bold mb-3">{{ __('index.features.c1t') }}</h4>
                        <p class="text-muted mb-0">{{ __('index.features.c1p') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">leaderboard</span>
                        </div>
                        <h4 class="fw-bold mb-3">{{ __('index.features.c2t') }}</h4>
                        <p class="text-muted mb-0">{{ __('index.features.c2p') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">assignment_turned_in</span>
                        </div>
                        <h4 class="fw-bold mb-3">{{ __('index.features.c3t') }}</h4>
                        <p class="text-muted mb-0">{{ __('index.features.c3p') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white" id="materias">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">{{ __('index.subjects.title') }}</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">{{ __('index.subjects.lead') }}</p>
            </div>
            <div class="row justify-content-center g-4">
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">biotech</span>
                        </div>
                        <h3 class="fw-bold mb-3">{{ __('index.subjects.micro') }}</h3>
                        <p class="text-muted">{{ __('index.subjects.microp') }}</p>
                        <span class="badge rounded-pill bg-success px-3 py-2">{{ __('index.subjects.available') }}</span>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">science</span>
                        </div>
                        <h3 class="fw-bold mb-3">{{ __('index.subjects.bio') }}</h3>
                        <p class="text-muted">{{ __('index.subjects.biop') }}</p>
                        <span class="badge rounded-pill bg-success px-3 py-2">{{ __('index.subjects.available') }}</span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <p class="text-muted italic">{{ __('index.subjects.more') }}</p>
            </div>
        </div>
    </section>

    <section class="py-5 text-white text-center" id="listo">
        <div class="container py-5">
            <h2 class="display-4 fw-bold mb-4">{{ __('index.cta.title') }}</h2>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 800px;">{{ __('index.cta.lead') }}</p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm"
                   href="{{ route('signup.materias') }}">{{ __('index.cta.btn') }}</a>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container py-5">
            <div class="row g-4 g-lg-5 align-items-start">
                <div class="col-lg-5">
                    <a class="d-inline-block mb-3" href="#top" aria-label="{{ __('index.back_to_top') }}">
                        <img class="footer-min-logo" src="{{ \App\Support\Branding::logoUrl() }}"
                             alt="" width="180" height="48" decoding="async">
                    </a>
                    <p class="footer-min-tagline mb-0">{{ __('index.footer.brand') }}</p>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <p class="footer-min-heading mb-0">{{ __('index.footer.platform') }}</p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="{{ route('login') }}">{{ __('index.footer.f1') }}</a></li>
                        <li><a class="footer-min-link" href="{{ route('login') }}">{{ __('index.footer.f2') }}</a></li>
                        <li><a class="footer-min-link" href="#materias">{{ __('index.footer.f3') }}</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <p class="footer-min-heading mb-0">{{ __('index.footer.support') }}</p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="{{ route('login') }}">{{ __('index.footer.s1') }}</a></li>
                        <li><a class="footer-min-link" href="#contacto">{{ __('index.footer.s2') }}</a></li>
                        <li><a class="footer-min-link" href="#caracteristicas">{{ __('index.footer.s3') }}</a></li>
                    </ul>
                </div>
                <div class="col-md-4 col-lg-3">
                    <p class="footer-min-heading mb-0">{{ __('index.footer.legal') }}</p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="#terminos">{{ __('index.footer.l1') }}</a></li>
                        <li><a class="footer-min-link" href="#privacidad">{{ __('index.footer.l2') }}</a></li>
                        <li><a class="footer-min-link" href="#lgpd">{{ __('index.footer.l3') }}</a></li>
                        <li><a class="footer-min-link" href="#cookies">{{ __('index.footer.l4') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-min-cta mt-5" id="contacto">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <p class="footer-min-cta-title mb-1">{{ __('index.footer.cta_title') }}</p>
                        <p class="footer-min-cta-sub mb-0">{{ __('index.footer.cta_sub') }}</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 justify-content-lg-end">
                            <a class="btn btn-primary rounded-pill px-4 fw-semibold"
                               href="{{ route('signup.materias') }}">{{ __('index.footer.cta_btn') }}</a>
                            <a class="footer-min-mail d-inline-flex align-items-center justify-content-center px-3 py-2"
                               href="mailto:contato@bancodechoices.com">contato@bancodechoices.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-min-legal-box mt-5 pt-4 border-top footer-min-rule">
                <p id="terminos" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_terms_head') }}</strong> {{ __('index.footer.legal_terms_p') }}
                </p>
                <p id="privacidad" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_privacy_head') }}</strong> {{ __('index.footer.legal_privacy_p') }}
                </p>
                <p id="lgpd" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_lgpd_head') }}</strong> {{ __('index.footer.legal_lgpd_p') }}
                </p>
                <p id="cookies" class="mb-0" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_cookies_head') }}</strong> {{ __('index.footer.legal_cookies_p') }}
                </p>
            </div>

            <div class="mt-4 pt-4 border-top footer-min-rule d-flex flex-column flex-md-row gap-3 justify-content-between align-items-start align-items-md-center">
                <p class="footer-min-bottom mb-0">&copy; {{ date('Y') }} BancodeChoices. {{ __('index.footer.rights_reserved') }}</p>
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-4 align-items-start align-items-sm-center text-md-end">
                    <p class="footer-min-bottom mb-0" style="max-width: 22rem;">{{ __('index.footer.trust_line') }}</p>
                    <a class="footer-min-site" href="https://bancodechoices.com" target="_blank" rel="noopener noreferrer">bancodechoices.com</a>
                </div>
            </div>
        </div>
    </footer>

    <button type="button" class="btn btn-primary btn-back-to-top" id="backToTop"
            aria-label="{{ __('index.back_to_top') }}" title="{{ __('index.back_to_top') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 15l-6-6-6 6"/>
        </svg>
    </button>
@endsection

@push('scripts')
<script>
    (function () {
        var btn = document.getElementById('backToTop');
        if (!btn) return;
        var threshold = 400;
        function updateVisibility() {
            btn.classList.toggle('is-visible', window.scrollY > threshold);
        }
        window.addEventListener('scroll', updateVisibility, { passive: true });
        updateVisibility();
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>
@endpush
