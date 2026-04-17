@extends('layouts.public')

@section('title', __('index.page_title'))

@section('body_attr')
 class="lp-body"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/landing-page.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/landing-exemplo.css') }}">
@endpush

@php
    $currencyId = config('mercadopago.currency_id', 'ARS');
    $currencySymbol = match ($currencyId) {
        'BRL' => 'R$',
        'USD' => 'US$',
        default => '$',
    };
    $planOrder = ['monthly', 'semester', 'annual'];
    $heroImg = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDeUnabwx_oacvn4bzAIMgJBvkp2ILXbgLxOY-gn6rjd-M27uBE4xgqaAfUW-Z3aiawzVG3yDkI4OsmDmMTsIIQ-TdsR3CB2-vINdhEClQqLEG9BT4fNl6D9fVpK7AznbbNGcarGY5cQrYbsBETosWT_bHmThe3dpONXfBgxtmVMkJMuJL5z2CFM8QuIcfXhjmuopX4bv6oICqiRyk4ezRMf6-uTyyodiCYz94whqKB9gRKxd--YfGgR95_HYiaZceZaRiw7MsiQ5w';
    $bentoDashImg = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCIQI4Xpr5gWlyJF1IU2cGGES6TAr5WSw-XtFak7Ld63byMQVU101nfxBNojrZ_Ex3wH7FBDWBsEcDhLO4Y8eJ0WPE8RLFbA9V35-HVcKUyH60XFy9wlAu9s2ymCPZ9XF5NIUzNVZy_Am3TKbLjWKKW43KXfTTnVVy_1kBJYJ8lELSq19XQMLkmvlYfvR220CD0_k-E_OHHan7ogD2GwZxIjFTxu4czBOMBuLhmLfX1CwFFkazGivwVXexWFz3odqDrsbq6GFacPLU';
    $bentoStatsImg = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDwJLuBEb6Yk6CC3oPFOBIbdawEzLGs3NhxWJhSZuQUagxV55YSst9UAkcHY0el9lIRKyKceX8aUs8jfCqAtZO8c898Ih5lzDtJgUBSQZqSr8_bDmSlN6K3r_MBEKZYxlfCRK1vByzxXjR-ULlVcJ2YvPnVM7aCs8FOl9O--o0cgxGkaDllaQ7c9Qtr5aqiGbZO3DMqrJW6vAzqiYFfasQb772o-Wti9F6QB_bwikh-WT2J7ZUakOQmpydCkdO6FmNaBvj3rs7O8ls';
@endphp

@section('content')
<div class="lp-shell" id="top">
    <nav class="navbar navbar-expand-lg navbar-light landing-navbar lp-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center js-scroll-anchor"
               href="{{ route('home') }}" data-scroll-target="top"
               aria-label="{{ __('index.page_title') }}">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="160" height="40" decoding="async" class="d-inline-block">
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
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 py-2 py-lg-0">
                    <li class="nav-item">
                        <a class="nav-link lp-nav-link js-scroll-anchor" href="{{ route('home') }}"
                           data-scroll-target="ferramentas">{{ __('index.nav.benefits') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link lp-nav-link js-scroll-anchor" href="{{ route('home') }}"
                           data-scroll-target="pricing">{{ __('index.nav.pricing') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link lp-nav-link js-scroll-anchor" href="{{ route('home') }}"
                           data-scroll-target="listo">{{ __('index.nav.faq') }}</a>
                    </li>
                </ul>
                <div class="navbar-actions navbar-actions--landing ms-lg-3">
                    <div class="navbar-actions__inner">
                        <x-language-selector />
                        <span class="navbar-actions__divider" aria-hidden="true"></span>
                        <a class="btn btn-nav-login text-decoration-none" href="{{ route('login') }}">{{ __('index.nav.login') }}</a>
                        <a class="lp-btn lp-btn--primary text-decoration-none rounded-3 px-4 py-2"
                           href="{{ route('signup.materias') }}">{{ __('index.nav.register') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="lp-hero">
            <div class="container position-relative">
                <div class="row align-items-center g-5 g-lg-5">
                    <div class="col-lg-6 position-relative" style="z-index: 1;">
                        <div class="lp-hero-badge mb-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified</span>
                            {{ __('index.hero.badge') }}
                        </div>
                        <h1 class="lp-hero-title lp-headline">{{ __('index.hero.title') }}</h1>
                        <p class="lp-hero-lead">{{ __('index.hero.lead') }}</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="lp-btn lp-btn--primary" href="{{ route('signup.materias') }}">{{ __('index.hero.cta1') }}</a>
                            <a class="lp-btn lp-btn--ghost js-scroll-anchor" href="{{ route('home') }}"
                               data-scroll-target="pricing">{{ __('index.hero.cta2') }}</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="lp-hero-visual">
                            <div class="lp-hero-glow" aria-hidden="true"></div>
                            <div class="lp-glass-frame">
                                <img src="{{ $heroImg }}" alt="{{ __('index.hero.alt') }}" class="lp-hero-img" width="960" height="640" decoding="async">
                                <div class="lp-hero-float">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="lp-hero-float-icon">
                                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">query_stats</span>
                                        </div>
                                        <div>
                                            <p class="small text-uppercase fw-bold text-secondary mb-0" style="letter-spacing: 0.06em;">{{ __('index.lp.hero_metric_label') }}</p>
                                            <p class="mb-0 lp-headline fs-3 lp-fw-900" style="color: var(--lp-primary-container);">{{ __('index.lp.hero_metric_value') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-bento" id="ferramentas">
            <div class="container">
                <h2 class="lp-bento-title lp-headline">{{ __('index.lp.bento.section_title') }}</h2>
                <div class="lp-bento-rule" role="presentation"></div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="lp-glass-card h-100">
                            <div class="row align-items-center g-4">
                                <div class="col-md-6">
                                    <span class="material-symbols-outlined text-primary mb-2" style="font-size: 2.5rem;">database</span>
                                    <h3 class="h4 fw-bold mb-3">{{ __('index.lp.bento.bank_title') }}</h3>
                                    <p class="text-secondary mb-3">{{ __('index.lp.bento.bank_p') }}</p>
                                    <ul class="list-unstyled mb-0 small fw-semibold" style="color: var(--lp-primary-container);">
                                        <li class="d-flex align-items-center gap-2 mb-2">
                                            <span class="material-symbols-outlined" style="font-size: 1.1rem;">check_circle</span>
                                            {{ __('index.lp.bento.bank_li1') }}
                                        </li>
                                        <li class="d-flex align-items-center gap-2">
                                            <span class="material-symbols-outlined" style="font-size: 1.1rem;">check_circle</span>
                                            {{ __('index.lp.bento.bank_li2') }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="rounded-4 overflow-hidden bg-light" style="aspect-ratio: 16/10;">
                                        <img src="{{ $bentoDashImg }}" alt="" class="lp-bento-img w-100 h-100" width="640" height="400" decoding="async">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="lp-bento-purple h-100">
                            <span class="material-symbols-outlined mb-3" style="font-size: 2.5rem;">clinical_notes</span>
                            <h3 class="h4 fw-bold">{{ __('index.lp.bento.sim_title') }}</h3>
                            <p class="mb-0">{{ __('index.lp.bento.sim_p') }}</p>
                            <a class="lp-bento-link" href="{{ route('login') }}">
                                {{ __('index.lp.bento.sim_cta') }}
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="lp-glass-card">
                            <div class="row align-items-center g-4 g-lg-5">
                                <div class="col-lg-4 text-center text-lg-start">
                                    <img src="{{ $bentoStatsImg }}" alt="" class="lp-bento-wide-img w-100" width="480" height="360" decoding="async">
                                </div>
                                <div class="col-lg-8">
                                    <div class="d-inline-flex p-3 rounded-4 mb-3" style="background: var(--lp-secondary-fixed);">
                                        <span class="material-symbols-outlined" style="color: #66288d;">analytics</span>
                                    </div>
                                    <h3 class="h2 fw-bold mb-3">{{ __('index.lp.bento.stats_title') }}</h3>
                                    <p class="text-secondary lead fs-6 mb-4">{{ __('index.lp.bento.stats_p') }}</p>
                                    <div class="row g-3" style="max-width: 28rem;">
                                        <div class="col-6">
                                            <div class="lp-stat-pill">
                                                <strong>{{ __('index.lp.bento.stats_kpi1') }}</strong>
                                                <span>{{ __('index.lp.bento.stats_kpi1_label') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="lp-stat-pill">
                                                <strong>{{ __('index.lp.bento.stats_kpi2') }}</strong>
                                                <span>{{ __('index.lp.bento.stats_kpi2_label') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-pricing" id="pricing">
            <div class="container">
                <div class="lp-pricing-intro">
                    <h2 class="lp-headline">{{ __('index.lp.pricing.title') }}</h2>
                    <p>{{ __('index.lp.pricing.lead') }}</p>
                </div>
                <div class="row g-4 justify-content-center align-items-stretch">
                    @foreach ($planOrder as $planId)
                        @isset($landingPlans[$planId])
                            @php $plan = $landingPlans[$planId]; @endphp
                            <div class="col-md-6 col-xl-4 d-flex">
                                <div class="lp-pricing-card w-100 @if ($plan['popular']) lp-pricing-card--featured @endif">
                                    @if ($plan['popular'])
                                        <span class="lp-pricing-badge">{{ __('index.lp.pricing.popular') }}</span>
                                    @endif
                                    <span class="lp-pricing-label">{{ __("index.lp.pricing.plan_{$planId}") }}</span>
                                    <div class="lp-pricing-amount">
                                        <span class="lp-pricing-cur">{{ $currencySymbol }}</span>
                                        <span class="lp-pricing-num">{{ $plan['per_month_fmt'] }}</span>
                                        <span class="lp-pricing-suffix">/ {{ __('index.lp.pricing.per_month_hint') }}</span>
                                    </div>
                                    <p class="lp-pricing-sub mb-0">
                                        {{ __('index.lp.pricing.period_total', ['currency' => $currencySymbol, 'amount' => $plan['price_total_fmt'], 'days' => $plan['days']]) }}
                                    </p>
                                    <ul class="lp-pricing-list">
                                        <li>
                                            <span class="material-symbols-outlined">check</span>
                                            {{ __('index.lp.pricing.bullet1') }}
                                        </li>
                                        <li>
                                            <span class="material-symbols-outlined">check</span>
                                            {{ __('index.lp.pricing.bullet2') }}
                                        </li>
                                        @if ($plan['popular'])
                                            <li>
                                                <span class="material-symbols-outlined">star</span>
                                                {{ __('index.lp.pricing.bullet_extra1') }}
                                            </li>
                                            <li>
                                                <span class="material-symbols-outlined">check</span>
                                                {{ __('index.lp.pricing.bullet_extra2') }}
                                            </li>
                                        @else
                                            <li>
                                                <span class="material-symbols-outlined">check</span>
                                                {{ __('index.lp.pricing.bullet3') }}
                                            </li>
                                        @endif
                                    </ul>
                                    <a class="lp-pricing-cta" href="{{ route('signup.materias') }}">
                                        {{ $plan['popular'] ? __('index.lp.pricing.cta_start') : __('index.lp.pricing.cta_select') }}
                                    </a>
                                </div>
                            </div>
                        @endisset
                    @endforeach
                </div>
            </div>
        </section>

        <section class="lp-cta-strip" id="listo">
            <div class="container py-2">
                <h2 class="h3 lp-headline">{{ __('index.cta.title') }}</h2>
                <p class="mb-0">{{ __('index.cta.lead') }}</p>
                <a class="lp-btn lp-btn--primary mt-4 d-inline-flex" href="{{ route('signup.materias') }}">{{ __('index.cta.btn') }}</a>
            </div>
        </section>
    </main>

    <footer class="lp-footer">
        <div class="container">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-6">
                    <div class="lp-footer-brand-row">
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="120" height="32" decoding="async">
                        <span class="lp-headline fw-bold text-purple-900" style="color: #3b0764;">{{ __('index.page_title') }}</span>
                    </div>
                    <p class="text-secondary small mb-0">&copy; {{ date('Y') }} {{ __('index.page_title') }}. {{ __('index.footer.brand') }}</p>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <h4>{{ __('index.footer.platform') }}</h4>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a class="js-scroll-anchor" href="{{ route('home') }}" data-scroll-target="privacidad">{{ __('index.footer.l2') }}</a></li>
                        <li class="mb-2"><a class="js-scroll-anchor" href="{{ route('home') }}" data-scroll-target="terminos">{{ __('index.footer.l1') }}</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <h4>{{ __('index.footer.support') }}</h4>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a class="js-scroll-anchor" href="{{ route('home') }}" data-scroll-target="listo">{{ __('index.footer.s2') }}</a></li>
                        <li class="mb-2"><a href="{{ route('login') }}">{{ __('index.footer.s1') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-min-legal-box mt-5 pt-4 border-top footer-min-rule">
                <p id="terminos" class="small" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_terms_head') }}</strong> {{ __('index.footer.legal_terms_p') }}
                </p>
                <p id="privacidad" class="small" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_privacy_head') }}</strong> {{ __('index.footer.legal_privacy_p') }}
                </p>
                <p id="lgpd" class="small" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_lgpd_head') }}</strong> {{ __('index.footer.legal_lgpd_p') }}
                </p>
                <p id="cookies" class="small mb-0" style="scroll-margin-top: 5rem;">
                    <strong>{{ __('index.footer.legal_cookies_head') }}</strong> {{ __('index.footer.legal_cookies_p') }}
                </p>
            </div>

            <div class="mt-4 pt-3 border-top footer-min-rule d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                <p class="footer-min-bottom mb-0 small">{{ __('index.footer.rights_reserved') }}</p>
                <a class="footer-min-site small" href="https://bancodechoices.com" target="_blank" rel="noopener noreferrer">bancodechoices.com</a>
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
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function isHomePath() {
            var p = window.location.pathname || '';
            return p === '/' || p === '';
        }

        var rawHash = window.location.hash;
        if (rawHash && rawHash.length > 1) {
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }

        function closeNavbarCollapseIfOpen() {
            var nav = document.getElementById('navbarNav');
            if (!nav || !nav.classList.contains('show')) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                var inst = bootstrap.Collapse.getInstance(nav);
                if (inst) inst.hide();
            }
        }

        document.querySelectorAll('a.js-scroll-anchor[data-scroll-target]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                if (!isHomePath()) return;
                var target = a.getAttribute('data-scroll-target');
                if (!target) return;
                e.preventDefault();
                closeNavbarCollapseIfOpen();
                if (target === 'top') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                var el = document.getElementById(target);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        if (rawHash && rawHash.length > 1) {
            var id = rawHash.slice(1);
            requestAnimationFrame(function () {
                var el = document.getElementById(id);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        var btnTop = document.getElementById('backToTop');
        if (btnTop) {
            var threshold = 400;
            function updateVisibility() {
                btnTop.classList.toggle('is-visible', window.scrollY > threshold);
            }
            window.addEventListener('scroll', updateVisibility, { passive: true });
            updateVisibility();
            btnTop.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

    })();
</script>
@endpush
