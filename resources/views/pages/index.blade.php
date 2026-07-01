@extends('layouts.public')

@section('title', 'Banco de Choices')
@section('body_attr')
    class="lp-body lp-body--dark-hero"
@endsection

@push('styles')
    @include('pages.partials.landing-styles')
    <style>
        :root {
            --scene-show: block;
        }
        @media (max-width: 991.98px) {
            :root {
                --scene-show: none;
            }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes glowPulse {
            0%, 100% { opacity: .55; }
            50% { opacity: .88; }
        }
        @keyframes barGrow {
            0%, 100% { transform: scaleY(1); opacity: .65; }
            50% { transform: scaleY(1.12); opacity: 1; }
        }
        @keyframes panelMain {
            0%, 100% { transform: rotateY(-10deg) rotateX(5deg) translateZ(12px); }
            50% { transform: rotateY(-7deg) rotateX(4deg) translateZ(22px) translateY(-8px); }
        }
        @keyframes panelBack {
            0%, 100% { transform: rotateY(-18deg) rotateX(7deg) translateZ(-22px) translateX(14px); }
            50% { transform: rotateY(-13deg) rotateX(5deg) translateZ(-13px) translateX(9px) translateY(-5px); }
        }
        @keyframes mockProgress {
            0%, 22% { width: 28%; }
            43%, 81% { width: 62%; }
            88%, 100% { width: 28%; }
        }
        @keyframes mockOptB {
            0%, 25%, 89%, 100% { border-color: rgba(15,23,42,.1); background: rgba(255,255,255,.9); color: #6b7280; box-shadow: none; }
            27%, 41% { border-color: rgba(106,3,146,.52); background: rgba(106,3,146,.08); color: #1c1c1f; }
            43%, 87% { border-color: rgba(22,163,74,.55); background: rgba(22,163,74,.09); color: #15803d; box-shadow: 0 4px 18px rgba(22,163,74,.18); }
        }
        @keyframes mockLetterB {
            0%, 25%, 89%, 100% { background: rgba(106,3,146,.07); color: #6a0392; border-color: rgba(106,3,146,.12); }
            27%, 41% { background: rgba(106,3,146,.15); color: #6a0392; border-color: rgba(106,3,146,.3); }
            43%, 87% { background: rgba(22,163,74,.13); color: #15803d; border-color: rgba(22,163,74,.28); }
        }
        @keyframes mockTick {
            0%, 44%, 88%, 100% { opacity: 0; transform: scale(.3) rotate(-10deg); }
            47%, 85% { opacity: 1; transform: scale(1) rotate(0deg); }
        }

        /* Garantia mobile: independe de cache no docroot /assets */
        @media (max-width: 991.98px) {
            html {
                scrollbar-gutter: auto !important;
            }

            .lp-hero__grid {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }

            .lp-hero__grid>div {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }

            .lp-hero__visual {
                width: 100% !important;
                max-width: 100% !important;
                margin: 20px 0 0 !important;
                aspect-ratio: auto !important;
                height: auto !important;
            }

            .lp-hero__mock-mobile {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }

            .lp-hero__mock-mobile .lp-hero-scene__panel--main {
                position: relative !important;
                left: auto !important;
                bottom: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                max-height: none !important;
                transform: none !important;
                animation: none !important;
            }

            .lp-stats__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                width: 100% !important;
            }

            .lp-stats__cell {
                min-width: 0 !important;
                overflow-wrap: anywhere;
            }

            .lp-container,
            .lp-topbar__inner {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
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
        <span class="lp-hero__fade" aria-hidden="true"></span>
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

                    <div class="lp-hero__trust lp-hero__ctas-anim">
                        <div class="lp-hero__trust-avatars">
                            <span class="lp-hero__trust-avatar lp-hero__trust-avatar--1">JM</span>
                            <span class="lp-hero__trust-avatar lp-hero__trust-avatar--2">AR</span>
                            <span class="lp-hero__trust-avatar lp-hero__trust-avatar--3">CS</span>
                        </div>
                        <span class="lp-hero__trust-text">{!! __('landing.hero.trust_count') !!}</span>
                        <span class="lp-hero__trust-rating">
                            <span class="lp-hero__trust-stars" aria-hidden="true">★★★★★</span>
                            {{ __('landing.hero.trust_rating') }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="lp-hero__visual lp-hero__visual-anim">
                        <div class="lp-hero__mock-mobile d-lg-none">
                            <div class="lp-hero-scene__panel lp-hero-scene__panel--main">
                                @include('pages.partials.hero-mock-card')
                            </div>
                        </div>
                        <!-- RIGHT: Visual -->
                        <div>
                            <!-- Desktop 3D scene -->
                            <div style="display: var(--scene-show);">
                                <div
                                    style="position: relative; width: 100%; aspect-ratio: 1 / 1.06; max-width: 510px; margin: 0 0 0 auto; animation: fadeUp .9s .14s ease both, float 7s 1.3s ease-in-out infinite; will-change: transform; overflow: visible;">
                                    <!-- Glow blob -->
                                    <div
                                        style="position: absolute; inset: 12% 6%; background: radial-gradient(ellipse at 50% 50%, rgba(106,3,146,.3), transparent 65%); filter: blur(44px); border-radius: 50%; pointer-events: none; animation: glowPulse 5.5s ease-in-out infinite;">
                                    </div>
                                    <!-- Perspective container -->
                                    <div
                                        style="position: absolute; inset: 0; perspective: 1100px; perspective-origin: 52% 42%; overflow: visible;">
                                        <!-- Back stats panel -->
                                        <div
                                            style="position: absolute; top: 4%; right: 2%; width: 54%; padding: 1rem 1.1rem; background: linear-gradient(165deg, #170c2c, #100820); border: 1px solid rgba(255,255,255,.11); border-radius: 20px; box-shadow: 0 24px 64px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.07); transform-style: preserve-3d; backface-visibility: hidden; animation: panelBack 9s ease-in-out infinite;">
                                            <span
                                                style="display: block; font-size: .58rem; font-weight: 700; letter-spacing: .11em; text-transform: uppercase; color: rgba(255,255,255,.38); margin-bottom: 11px;">Desempenho
                                                Semanal</span>
                                            <div style="display: flex; align-items: flex-end; gap: 5px; height: 66px;">
                                                <div
                                                    style="flex:1; height:40%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, rgba(192,132,252,.7), rgba(106,3,146,.22)); transform-origin: bottom; animation: barGrow 4s ease-in-out infinite;">
                                                </div>
                                                <div
                                                    style="flex:1; height:64%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, rgba(192,132,252,.78), rgba(106,3,146,.28)); transform-origin: bottom; animation: barGrow 4s .18s ease-in-out infinite;">
                                                </div>
                                                <div
                                                    style="flex:1; height:51%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, rgba(192,132,252,.72), rgba(106,3,146,.24)); transform-origin: bottom; animation: barGrow 4s .35s ease-in-out infinite;">
                                                </div>
                                                <div
                                                    style="flex:1; height:87%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, rgba(216,180,254,.9), rgba(168,85,247,.45)); transform-origin: bottom; animation: barGrow 4s .52s ease-in-out infinite;">
                                                </div>
                                                <div
                                                    style="flex:1; height:69%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, rgba(192,132,252,.78), rgba(106,3,146,.3)); transform-origin: bottom; animation: barGrow 4s .7s ease-in-out infinite;">
                                                </div>
                                            </div>
                                            <div
                                                style="display: flex; justify-content: space-between; margin-top: 8px; align-items: center;">
                                                <span style="font-size: .57rem; color: rgba(255,255,255,.28);">Seg –
                                                    Sex</span>
                                                <span
                                                    style="font-size: .68rem; font-weight: 700; background: linear-gradient(135deg, #e9d5ff, #c084fc); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">↑
                                                    88%</span>
                                            </div>
                                        </div>

                                        <!-- Main quiz card -->
                                        <div
                                            style="position: absolute; left: 0; bottom: 2%; width: 91%; background: linear-gradient(168deg, #fff 0%, #f6f2fd 100%); border-radius: 22px; box-shadow: 0 36px 88px rgba(0,0,0,.4), 0 0 0 1px rgba(255,255,255,.07), inset 0 1px 0 rgba(255,255,255,.95); transform-style: preserve-3d; backface-visibility: hidden; animation: panelMain 9s ease-in-out infinite; overflow: hidden; padding: 1.25rem 1.3rem 1.35rem 1.7rem;">
                                            <div
                                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                                <span
                                                    style="padding: .28rem .65rem; border-radius: 999px; background: rgba(106,3,146,.09); border: 1px solid rgba(106,3,146,.15); color: #6a0392; font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;">{{ __('landing.hero.mock_pill') }}</span>
                                                <span style="font-size: .68rem; font-weight: 600; color: #9ca3af;">{{ __('landing.hero.mock_progress') }}</span>
                                            </div>
                                            <div
                                                style="height: 3px; background: rgba(15,23,42,.07); border-radius: 99px; overflow: hidden; margin-bottom: 13px;">
                                                <div
                                                    style="height: 100%; border-radius: 99px; background: linear-gradient(90deg, #8b1fb8, #6a0392); animation: mockProgress 10s ease-in-out infinite;">
                                                </div>
                                            </div>
                                            <p
                                                style="font-size: .82rem; font-weight: 600; line-height: 1.52; color: #1c1c1f; margin-bottom: 12px;">
                                                {{ __('landing.hero.mock_question') }}</p>
                                            <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;padding: 0px">
                                                <li
                                                    style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.9); font-size: .74rem; color: #6b7280;">
                                                    <span
                                                        style="flex-shrink:0; width:20px; height:20px; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.62rem; background:rgba(106,3,146,.07); color:#6a0392; border:1px solid rgba(106,3,146,.12);">A</span>
                                                    <span>{{ __('landing.hero.mock_opt_a') }}</span>
                                                </li>
                                                <li
                                                    style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.9); font-size: .74rem; color: #6b7280;">
                                                    <span
                                                        style="flex-shrink:0; width:20px; height:20px; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.62rem; background:rgba(106,3,146,.07); color:#6a0392; border:1px solid rgba(106,3,146,.12);">B</span>
                                                    <span>{{ __('landing.hero.mock_opt_b') }}</span>
                                                </li>
                                                <li
                                                    style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.9); font-size: .74rem; color: #6b7280; animation: mockOptB 10s ease-in-out infinite;">
                                                    <span
                                                        style="flex-shrink:0; width:20px; height:20px; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.62rem; background:rgba(106,3,146,.07); color:#6a0392; border:1px solid rgba(106,3,146,.12); animation:mockLetterB 10s ease-in-out infinite;">C</span>
                                                    <span style="flex:1;">{{ __('landing.hero.mock_opt_c') }}</span>
                                                    <span
                                                        style="flex-shrink:0; width:19px; height:19px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#16a34a; color:#fff; font-size:.66rem; animation:mockTick 10s ease-in-out infinite;">✓</span>
                                                </li>
                                                <li
                                                    style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.9); font-size: .74rem; color: #6b7280;">
                                                    <span
                                                        style="flex-shrink:0; width:20px; height:20px; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.62rem; background:rgba(106,3,146,.07); color:#6a0392; border:1px solid rgba(106,3,146,.12);">D</span>
                                                    <span>{{ __('landing.hero.mock_opt_d') }}</span>
                                                </li>
                                            </ul>
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

    {{-- 1.3 Stats banner --}}
    <section class="lp-stats" aria-label="Estadísticas">
        <div class="lp-container">
            <div class="lp-stats__grid">
                @foreach ($stats as $i => $s)
                    <div class="lp-stats__cell lp-reveal" data-delay="{{ $i + 1 }}">
                        <div class="lp-stats__num">{{ $s['numero'] }}</div>
                        <div class="lp-stats__label">{{ __($s['label_key']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 1.3.5 Funcionalidades --}}
    <section class="lp-section lp-features" id="funcionalidades">
        <div class="lp-container">
            <div class="lp-section__header lp-reveal">
                <span class="lp-badge">{{ __('landing.features.badge') }}</span>
                <h2 class="lp-h-section">{!! __('landing.features.title') !!}</h2>
                <p class="lp-section__subtitle">{!! __('landing.features.subtitle') !!}</p>
            </div>

            <div class="lp-features__grid">
                @foreach ([
                    ['icon' => 'bi-clipboard2-pulse', 'key' => 'f1'],
                    ['icon' => 'bi-layers', 'key' => 'f2'],
                    ['icon' => 'bi-boxes', 'key' => 'f3'],
                    ['icon' => 'bi-mic', 'key' => 'f4'],
                    ['icon' => 'bi-robot', 'key' => 'f5'],
                    ['icon' => 'bi-map', 'key' => 'f6'],
                    ['icon' => 'bi-controller', 'key' => 'f7'],
                    ['icon' => 'bi-camera-video', 'key' => 'f8'],
                    ['icon' => 'bi-stopwatch', 'key' => 'f9'],
                    ['icon' => 'bi-trophy', 'key' => 'f10'],
                    ['icon' => 'bi-collection', 'key' => 'f11'],
                    ['icon' => 'bi-bar-chart-line', 'key' => 'f12'],
                    ['icon' => 'bi-people', 'key' => 'f13'],
                    ['icon' => 'bi-chat-dots', 'key' => 'f14'],
                ] as $i => $feat)
                    <article class="lp-feature-card lp-reveal" data-delay="{{ ($i % 4) + 1 }}">
                        <span class="lp-feature-card__icon"><i class="bi {{ $feat['icon'] }}"></i></span>
                        <h3 class="lp-feature-card__title">{{ __('landing.features.'.$feat['key'].'.title') }}</h3>
                        <p class="lp-feature-card__desc">{{ __('landing.features.'.$feat['key'].'.desc') }}</p>
                        <span class="lp-feature-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                    </article>
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
                        @include('pages.partials.faculdade-card', [
                            'faculdade' => $faculdade,
                            'demoCounts' => $demoCounts ?? [],
                            'colorIndex' => $i,
                        ])
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
        <i class="bi bi-arrow-up"></i>
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
        (function() {
            var topbar = document.getElementById('lpTopbar');
            var backToTop = document.getElementById('lpBackToTop');
            var brandLink = document.getElementById('lpBrandLink');

            function scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            var onScroll = function() {
                if (topbar) {
                    if (window.scrollY > 8) topbar.classList.add('is-scrolled');
                    else topbar.classList.remove('is-scrolled');
                }
                if (backToTop) {
                    if (window.scrollY > 320) backToTop.classList.add('is-visible');
                    else backToTop.classList.remove('is-visible');
                }
            };
            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            onScroll();

            if (backToTop) backToTop.addEventListener('click', scrollToTop);

            // Logo: smooth scroll to top when already on home
            if (brandLink) {
                brandLink.addEventListener('click', function(e) {
                    var path = window.location.pathname.replace(/\/$/, '');
                    var target = (new URL(brandLink.href)).pathname.replace(/\/$/, '');
                    if (path === target) {
                        e.preventDefault();
                        scrollToTop();
                    }
                });
            }

            // smooth scroll para âncoras (ignora href="#" puro)
            document.querySelectorAll('a[href^="#"]').forEach(function(a) {
                a.addEventListener('click', function(e) {
                    var id = a.getAttribute('href');
                    if (!id || id === '#') return;
                    var el = document.querySelector(id);
                    if (!el) return;
                    e.preventDefault();
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            // Fecha offcanvas ao clicar em qualquer link interno
            var oc = document.getElementById('lpOffcanvas');
            if (oc && window.bootstrap && bootstrap.Offcanvas) {
                oc.querySelectorAll('a').forEach(function(a) {
                    a.addEventListener('click', function() {
                        var inst = bootstrap.Offcanvas.getInstance(oc);
                        if (inst) inst.hide();
                    });
                });
            }

            // Reveal-on-scroll
            var reveals = document.querySelectorAll('.lp-reveal');
            if (!('IntersectionObserver' in window)) {
                reveals.forEach(function(el) {
                    el.classList.add('is-visible');
                });
                return;
            }
            var io = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            });
            reveals.forEach(function(el) {
                io.observe(el);
            });
        })();
    </script>
@endpush
