@extends('layouts.public')

@section('title', 'Banco de Choices')
@section('body_attr', ' class="lp-body"')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/landing-v2.css') }}?v={{ filemtime(public_path('assets/css/landing-v2.css')) }}">
@endpush

@section('public_topbar')
    @include('pages.partials.topbar')
@endsection

@section('public_offcanvas')
    @include('pages.partials.offcanvas-public')
@endsection

@section('public_footer')
    @include('pages.partials.footer')
@endsection

@section('content')
    {{-- 1.2 Hero --}}
    <section class="lp-hero">
        <div class="lp-container">
            <div class="lp-hero__grid">
                <div>
                    <span class="lp-badge lp-hero__badge-anim">{{ __('landing.hero.badge') }}</span>
                    <h1 class="lp-hero__title">
                        <em>{{ __('landing.hero.title_pre') }}</em>{{ __('landing.hero.title_rest') }}
                    </h1>
                    <p class="lp-hero__subtitle">
                        {!! __('landing.hero.subtitle', ['preguntas' => '<b>47.000+</b>']) !!}
                    </p>
                    <div class="lp-hero__ctas lp-hero__ctas-anim">
                        <a href="{{ route('demo.show') }}" class="btn lp-btn-primary lp-btn-lg">
                            {{ __('landing.hero.cta_primary') }}
                        </a>
                        <a href="#planes" class="btn lp-btn-outline">
                            {{ __('landing.hero.cta_secondary') }}
                        </a>
                    </div>
                </div>
                <div>
                    <div class="lp-hero__visual lp-hero__visual-anim">
                        <div class="lp-hero-scene" aria-hidden="true">
                            <div class="lp-hero-scene__glow"></div>
                            <aside class="lp-hero-scene__panel lp-hero-scene__panel--back">
                                <span class="lp-hero-panel__label">{{ __('landing.hero.mock_stats_title') }}</span>
                                <div class="lp-hero-panel__bars" role="presentation">
                                    <span style="--lp-bar-h: 42%"></span>
                                    <span style="--lp-bar-h: 68%"></span>
                                    <span style="--lp-bar-h: 55%"></span>
                                    <span style="--lp-bar-h: 88%"></span>
                                </div>
                                <div class="lp-hero-panel__spark"></div>
                            </aside>
                            <div class="lp-hero-scene__panel lp-hero-scene__panel--main">
                                <header class="lp-hero-q__head">
                                    <span class="lp-hero-q__pill">{{ __('landing.hero.mock_pill') }}</span>
                                    <span class="lp-hero-q__progress">{{ __('landing.hero.mock_progress') }}</span>
                                </header>
                                <p class="lp-hero-q__stem">{{ __('landing.hero.mock_question') }}</p>
                                <ul class="lp-hero-q__opts">
                                    <li class="lp-hero-q__opt lp-hero-q__opt--a">
                                        <span class="lp-hero-q__letter" aria-hidden="true">A</span>
                                        <span class="lp-hero-q__txt">{{ __('landing.hero.mock_opt_a') }}</span>
                                    </li>
                                    <li class="lp-hero-q__opt lp-hero-q__opt--b">
                                        <span class="lp-hero-q__letter" aria-hidden="true">B</span>
                                        <span class="lp-hero-q__txt">{{ __('landing.hero.mock_opt_b') }}</span>
                                    </li>
                                    <li class="lp-hero-q__opt lp-hero-q__opt--c lp-hero-q__opt--correct">
                                        <span class="lp-hero-q__letter" aria-hidden="true">C</span>
                                        <span class="lp-hero-q__txt">{{ __('landing.hero.mock_opt_c') }}</span>
                                        <span class="lp-hero-q__tick" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </li>
                                    <li class="lp-hero-q__opt lp-hero-q__opt--d">
                                        <span class="lp-hero-q__letter" aria-hidden="true">D</span>
                                        <span class="lp-hero-q__txt">{{ __('landing.hero.mock_opt_d') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 1.3 Stats banner --}}
    <section class="lp-stats" aria-label="Estadísticas">
        <div class="lp-container">
            <div class="lp-stats__grid">
                @foreach($stats as $i => $s)
                    <div class="lp-stats__cell lp-reveal" data-delay="{{ $i + 1 }}">
                        <div class="lp-stats__num">{{ $s['numero'] }}</div>
                        <div class="lp-stats__label">{{ __($s['label_key']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 1.4 Modalidades --}}
    <section class="lp-section" id="modalidades">
        <div class="lp-container">
            <div class="lp-section__header lp-reveal">
                <span class="lp-badge">{{ __('landing.modalidades.badge') }}</span>
                <h2 class="lp-h-section">{{ __('landing.modalidades.title') }}</h2>
            </div>

            <div class="lp-fac-grid">
                @forelse($faculdades as $i => $faculdade)
                    <div class="lp-reveal" data-delay="{{ ($i % 4) + 1 }}">
                        @include('pages.partials.faculdade-card', ['faculdade' => $faculdade])
                    </div>
                @empty
                    <div class="lp-fac-card lp-fac-card--soon lp-reveal">
                        <div class="lp-fac-card__head">
                            <h3 class="lp-fac-card__title">{{ __('demo.questao.no_questions') }}</h3>
                        </div>
                    </div>
                @endforelse

                <div class="lp-reveal" data-delay="5">
                    @include('pages.partials.faculdade-card', [
                        'faculdade' => null,
                        'proximamente' => true,
                        'titulo' => __('landing.modalidades.proximamente_card'),
                        'descricao' => __('landing.modalidades.proximamente_desc'),
                    ])
                </div>
            </div>
        </div>
    </section>

    {{-- 1.5 Cómo funciona --}}
    <section class="lp-section lp-section--alt" id="como-funciona">
        <div class="lp-container">
            <div class="lp-section__header lp-reveal">
                <span class="lp-badge">{{ __('landing.como.badge') }}</span>
                <h2 class="lp-h-section">{{ __('landing.como.title') }}</h2>
            </div>

            <div class="lp-steps">
                @foreach([
                    ['icon' => 'bi-mortarboard', 'n' => 1, 'title' => 'landing.como.s1.title', 'desc' => 'landing.como.s1.desc'],
                    ['icon' => 'bi-list-check', 'n' => 2, 'title' => 'landing.como.s2.title', 'desc' => 'landing.como.s2.desc'],
                    ['icon' => 'bi-graph-up-arrow', 'n' => 3, 'title' => 'landing.como.s3.title', 'desc' => 'landing.como.s3.desc'],
                ] as $i => $step)
                    <article class="lp-step lp-reveal" data-delay="{{ $i + 1 }}">
                        <span class="lp-step__num" aria-hidden="true">{{ $step['n'] }}</span>
                        <span class="lp-step__icon"><i class="bi {{ $step['icon'] }}"></i></span>
                        <h3 class="lp-step__title">{{ __($step['title']) }}</h3>
                        <p class="lp-step__desc">{{ __($step['desc']) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 1.6 Planes --}}
    <section class="lp-section" id="planes">
        <div class="lp-container">
            <div class="lp-section__header lp-reveal">
                <span class="lp-badge">{{ __('landing.planes.badge') }}</span>
                <h2 class="lp-h-section">{{ __('landing.planes.title') }}</h2>
            </div>

            <div class="lp-reveal" data-anim="scale">
                @include('pages.partials.planes-grid')
            </div>
        </div>
    </section>

    {{-- 1.7 FAQ --}}
    <section class="lp-section lp-section--alt" id="faq">
        <div class="lp-container">
            <div class="lp-faq__grid">
                <div class="lp-reveal" data-anim="left">
                    <span class="lp-faq__intro-icon" aria-hidden="true">?</span>
                    <h2 class="lp-faq__intro-title">{{ __('landing.faq.title') }}</h2>
                    <p class="lp-faq__intro-desc">{{ __('landing.faq.desc') }}</p>
                </div>
                <div class="lp-reveal" data-anim="right">
                    @include('pages.partials.faq-accordion')
                </div>
            </div>
        </div>
    </section>

    <button type="button" class="lp-back-to-top" id="lpBackToTop" aria-label="Voltar ao topo" hidden>
        <i class="bi bi-chevron-up"></i>
    </button>

    {{-- 1.8 CTA Final --}}
    <section class="lp-cta-final">
        <div class="lp-container lp-cta-final__inner lp-reveal" data-anim="scale">
            <span class="lp-badge">{{ __('landing.cta_final.badge') }}</span>
            <h2 class="lp-cta-final__title">
                {{ __('landing.cta_final.title_pre') }}<em>{{ __('landing.cta_final.title_em') }}</em>{{ __('landing.cta_final.title_post') }}
            </h2>
            <p class="lp-cta-final__subtitle">{{ __('landing.cta_final.subtitle') }}</p>
            <a href="{{ route('demo.show') }}" class="btn lp-btn-primary lp-btn-lg">
                {{ __('landing.cta_final.cta') }}
            </a>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var topbar = document.getElementById('lpTopbar');
            var backToTop = document.getElementById('lpBackToTop');
            var brandLink = document.getElementById('lpBrandLink');

            function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            var onScroll = function () {
                if (topbar) {
                    if (window.scrollY > 8) topbar.classList.add('is-scrolled');
                    else topbar.classList.remove('is-scrolled');
                }
                if (backToTop) {
                    if (window.scrollY > 320) backToTop.classList.add('is-visible');
                    else backToTop.classList.remove('is-visible');
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            if (backToTop) backToTop.addEventListener('click', scrollToTop);

            // Logo: smooth scroll to top when already on home
            if (brandLink) {
                brandLink.addEventListener('click', function (e) {
                    var path = window.location.pathname.replace(/\/$/, '');
                    var target = (new URL(brandLink.href)).pathname.replace(/\/$/, '');
                    if (path === target) {
                        e.preventDefault();
                        scrollToTop();
                    }
                });
            }

            // smooth scroll para âncoras (ignora href="#" puro)
            document.querySelectorAll('a[href^="#"]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    var id = a.getAttribute('href');
                    if (!id || id === '#') return;
                    var el = document.querySelector(id);
                    if (!el) return;
                    e.preventDefault();
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            // Fecha offcanvas ao clicar em qualquer link interno
            var oc = document.getElementById('lpOffcanvas');
            if (oc && window.bootstrap && bootstrap.Offcanvas) {
                oc.querySelectorAll('a').forEach(function (a) {
                    a.addEventListener('click', function () {
                        var inst = bootstrap.Offcanvas.getInstance(oc);
                        if (inst) inst.hide();
                    });
                });
            }

            // Reveal-on-scroll
            var reveals = document.querySelectorAll('.lp-reveal');
            if (!('IntersectionObserver' in window)) {
                reveals.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            reveals.forEach(function (el) { io.observe(el); });
        })();
    </script>
@endpush
