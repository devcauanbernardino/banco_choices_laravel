@extends('layouts.public')

@section('title', __('signup.page_title.checkout'))

@section('body_attr')
 class="checkout-mp-page signup-plano-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-materias.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-plano.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/checkout-mercadopago.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/public-language-selector.css') }}">
@endpush

@section('content')
<div class="checkout-mp-wrap">
    <div class="signup-plano-blobs" aria-hidden="true">
        <div class="signup-plano-blobs__one"></div>
        <div class="signup-plano-blobs__two"></div>
    </div>

    <header class="signup-materias-topbar">
        <a href="{{ route('signup.plano') }}" class="signup-materias-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;" aria-hidden="true">arrow_back</span>
            <span>{{ __('signup.checkout.back_plano') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="signup-materias-main">
        <div class="checkout-mp-inner">
            <div class="signup-materias-steps" aria-label="{{ __('signup.steps.aria') }}">
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">1</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.materias') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">2</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.plan') }}</div>
                </div>
                <div class="signup-materias-steps__item is-active" aria-current="step">
                    <div class="signup-materias-steps__num">3</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.pago') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">4</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.confirmacion') }}</div>
                </div>
            </div>

            <div class="checkout-mp-lead">
                <h1>{{ __('signup.checkout.h1') }}</h1>
                <p>{{ __('signup.checkout.mp_info') }}</p>
            </div>

            @if (session('error'))
                <div class="alert alert-warning mb-3">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="checkout-mp-grid">
                <div class="checkout-mp-primary">
                    <div class="checkout-mp-card">
                        <h2 class="checkout-mp-section-title">{{ __('signup.checkout.method_title') }}</h2>
                        <div class="checkout-mp-mp-card" aria-current="true">
                            <div class="checkout-mp-mp-card__icon">
                                <span class="material-symbols-outlined is-fill" aria-hidden="true">payments</span>
                            </div>
                            <div class="checkout-mp-mp-card__body">
                                <p class="checkout-mp-mp-card__title">Mercado Pago</p>
                                <p class="checkout-mp-mp-card__sub">{{ __('signup.checkout.mp_hint') }}</p>
                            </div>
                            <div class="checkout-mp-mp-card__tick" aria-hidden="true"></div>
                        </div>

                        <div class="checkout-mp-trust">
                            <h3 class="checkout-mp-trust__title">
                                <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                                {{ __('signup.checkout.trust_title') }}
                            </h3>
                            <div class="checkout-mp-trust__grid">
                                <div class="checkout-mp-trust__item">
                                    <div class="checkout-mp-trust__ico">
                                        <span class="material-symbols-outlined" aria-hidden="true">shield_lock</span>
                                    </div>
                                    <div>
                                        <h4>{{ __('signup.checkout.trust_a_title') }}</h4>
                                        <p>{{ __('signup.checkout.trust_a_text') }}</p>
                                    </div>
                                </div>
                                <div class="checkout-mp-trust__item">
                                    <div class="checkout-mp-trust__ico checkout-mp-trust__ico--b">
                                        <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
                                    </div>
                                    <div>
                                        <h4>{{ __('signup.checkout.trust_b_title') }}</h4>
                                        <p>{{ __('signup.checkout.trust_b_text') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h2 class="checkout-mp-section-title mt-1">{{ __('signup.checkout.contact_title') }}</h2>

                        <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                            @csrf
                            <input type="hidden" name="checkout_kind" value="signup">
                            <input type="hidden" name="order_id" value="{{ $orderId }}">
                            <input type="hidden" name="plan_id" value="{{ $planId }}">
                            <input type="hidden" name="plan_duration_days" value="{{ (int) ($plan['durationDays'] ?? 30) }}">
                            <input type="hidden" name="materias" value="{{ implode(',', $materias) }}">
                            <input type="hidden" name="total_price" value="{{ number_format($totalPrice, 2, '.', '') }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ __('signup.checkout.email') }}</label>
                                <input type="email" class="form-control form-control-lg" name="email" required
                                       value="{{ old('email') }}" placeholder="{{ __('signup.checkout.email_hint') }}" autocomplete="email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ __('signup.checkout.name') }}</label>
                                <input type="text" class="form-control form-control-lg" name="name" required
                                       value="{{ old('name') }}" autocomplete="name">
                            </div>

                            @include('partials.checkout-country-postal-fields', [
                                'countries' => $countries,
                                'countryId' => 'checkout-country',
                                'postalId' => 'checkout-postal',
                                'countryDefault' => 'AR',
                                'requiredPostal' => false,
                            ])

                            <p class="small text-muted">{{ __('signup.checkout.after_pay_note') }}</p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small" for="codigoCupomSignup">{{ __('referral.codigo_opcional_label') }}</label>
                                <input type="text" class="form-control form-control-lg" name="codigo_cupom_usado" id="codigoCupomSignup"
                                       value="{{ old('codigo_cupom_usado') }}" maxlength="40" autocomplete="off"
                                       placeholder="{{ __('referral.codigo_placeholder') }}">
                                <div class="form-text">{{ __('referral.codigo_hint_signup') }}</div>
                            </div>

                            @php
                                $mpPrefBase = rtrim((string) (config('mercadopago.checkout_base_url') ?: config('mercadopago.site_url')), '/');
                                $mpHttpsReturn = str_starts_with(strtolower($mpPrefBase), 'https://');
                            @endphp
                            @if (! $mpHttpsReturn)
                                <p class="small text-body-secondary border-start border-3 border-secondary ps-2 mb-3">{{ __('signup.checkout.mp_redirect_hint') }}</p>
                            @endif

                            <div class="checkout-terms-wrap mb-3">
                                <div class="checkout-terms-row">
                                    <input type="checkbox" class="form-check-input" name="terms" id="signup-terms" value="1" required
                                           aria-required="true">
                                    <label class="form-check-label small mb-0" for="signup-terms">
                                        {!! __('signup.checkout.terms_label', ['url' => route('home').'#terminos']) !!}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="checkout-mp-submit">
                                <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                                {{ sprintf(__('signup.checkout.submit_mp'), number_format($totalPrice, 2, ',', '.')) }}
                            </button>
                            <p class="checkout-mp-submit-note">{{ __('signup.checkout.submit_note') }}</p>
                        </form>
                    </div>
                </div>

                <aside class="checkout-mp-aside">
                    <div class="checkout-mp-aside-panel">
                        <div class="checkout-mp-aside-head">
                            <div class="min-w-0">
                                <span class="checkout-mp-badge">{{ __('signup.checkout.plan_badge') }}</span>
                                <h2>{{ $plan['name'] ?? '' }}</h2>
                            </div>
                            <span class="material-symbols-outlined checkout-mp-aside-premium is-fill" aria-hidden="true">workspace_premium</span>
                        </div>

                        <div class="checkout-mp-rows">
                            <div class="checkout-mp-row">
                                <span>{{ $plan['name'] ?? '' }}</span>
                                <span class="fw-medium">{{ $plan['duration'] ?? '' }}</span>
                            </div>

                            @foreach ($materiasInfo as $m)
                                <div class="checkout-mp-materia-row">
                                    <span>
                                        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                                        {{ $m->nome }}
                                    </span>
                                    <span>$ {{ number_format($plan['price'] ?? 0, 2, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="checkout-mp-row checkout-mp-row--total">
                                <span class="checkout-mp-total-label">{{ __('signup.checkout.total_due') }}</span>
                                <div class="text-end">
                                    <div class="checkout-mp-total-value">$ {{ number_format($totalPrice, 2, ',', '.') }}</div>
                                    <div class="small text-muted">ARS</div>
                                </div>
                            </div>
                        </div>

                        <p class="checkout-mp-aside-note">{{ sprintf(__('signup.checkout.access_note'), $plan['duration'] ?? '') }}</p>
                        <p class="checkout-mp-aside-secure">
                            <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                            <span>{{ __('signup.checkout.secure') }}</span>
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
    @include('partials.checkout-country-postal-script', [
        'countryId' => 'checkout-country',
        'postalId' => 'checkout-postal',
    ])
@endpush
