@extends('layouts.public')

@section('title', __('signup.page_title.checkout'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --accent-purple: #6a0392;
        --accent-purple-lighter: rgba(106,3,146,0.12);
        --navy-primary: #002147;
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%);
        background-size: 160% 160%;
        animation: floatBg 14s ease-in-out infinite;
        min-height: 100vh;
    }
    @keyframes floatBg {
        0% { background-position: 0% 0%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 0%; }
    }
    .container-custom { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
    .checkout-card { background: rgba(255,255,255,0.98); border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .header-section { text-align: center; margin-bottom: 2rem; color: white; }
    .header-section h1 { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 2rem; }
    .step-indicator { display: flex; justify-content: space-between; margin-bottom: 2.5rem; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: rgba(255,255,255,0.2); }
    .step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex: 1; }
    .step-number { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 2px solid white; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; margin-bottom: 0.5rem; }
    .step.active .step-number { background: var(--accent-purple); box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter); }
    .step-label { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; }
    .step.active .step-label { color: white; }
    .order-summary { background: var(--accent-purple-lighter); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; }
    .order-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0; }
    .order-summary-total { border-top: 2px solid var(--accent-purple); padding-top: 0.75rem; margin-top: 0.5rem; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; font-weight: 600; }
    .back-link:hover { color: rgba(255,255,255,0.8); }
    /* Foco: uma única indicação (evita borda + anel azul duplo do Bootstrap) */
    .checkout-card .form-control:focus,
    .checkout-card .form-control:focus-visible,
    .checkout-card .form-select:focus,
    .checkout-card .form-select:focus-visible {
        outline: none;
        border-color: var(--accent-purple);
        box-shadow: none;
    }
    .checkout-card .form-check-input:focus,
    .checkout-card .form-check-input:focus-visible {
        outline: none;
        border-color: var(--accent-purple);
        box-shadow: 0 0 0 0.2rem var(--accent-purple-lighter);
    }
    /* Termos: à esquerda; checkbox e texto na mesma linha (eixo vertical centralizado) */
    #checkoutForm .checkout-terms-wrap {
        text-align: left;
        line-height: normal;
    }
    #checkoutForm .checkout-terms-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
    }
    #checkoutForm .checkout-terms-row .form-check-input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0;
        cursor: pointer;
        flex-shrink: 0;
    }
    #checkoutForm .checkout-terms-row .form-check-label {
        cursor: pointer;
        line-height: 1.45;
    }
    #checkoutForm .checkout-terms-row .form-check-label a {
        word-break: break-word;
    }
    #checkoutForm .checkout-terms-row .form-check-input:checked {
        background-color: var(--accent-purple);
        border-color: var(--accent-purple);
    }
</style>
@endpush

@section('content')
<main class="container-custom">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <a href="{{ route('signup.plano') }}" class="back-link">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
            {{ __('signup.back_materias') }}
        </a>
        <div class="signup-flow-topbar flex-shrink-0 ms-auto">
            <div class="navbar-actions navbar-actions--landing">
                <div class="navbar-actions__inner">
                    @include('components.language-selector')
                </div>
            </div>
        </div>
    </div>

    <div class="header-section">
        <h1>{{ __('signup.page_title.checkout') }}</h1>
        <p class="opacity-90 mb-0">{{ __('signup.checkout.mp_info') }}</p>
    </div>

    <div class="step-indicator" aria-label="{{ __('signup.steps.aria') }}">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-label">{{ __('signup.step.materias') }}</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-label">{{ __('signup.step.plan') }}</div>
        </div>
        <div class="step active" aria-current="step">
            <div class="step-number">3</div>
            <div class="step-label">{{ __('signup.step.pago') }}</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-label">{{ __('signup.step.confirmacion') }}</div>
        </div>
    </div>

    <div class="checkout-card">
        <div class="row g-4">
            <div class="col-lg-7">
                <h5 class="fw-bold mb-3">{{ __('signup.checkout.contact_title') }}</h5>

                @if (session('error'))
                    <div class="alert alert-warning">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

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

                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 mt-2">
                        <i class="bi bi-lock-fill" aria-hidden="true"></i>
                        {{ sprintf(__('signup.checkout.submit_mp'), number_format($totalPrice, 2, ',', '.')) }}
                    </button>
                </form>
            </div>

            <div class="col-lg-5">
                <h5 class="fw-bold mb-3">{{ __('signup.checkout.summary_title') }}</h5>
                <div class="order-summary">
                    <div class="order-summary-row">
                        <span class="fw-bold">{{ $plan['name'] ?? '' }}</span>
                        <span class="small text-muted">{{ $plan['duration'] ?? '' }}</span>
                    </div>

                    @foreach ($materiasInfo as $m)
                        <div class="order-summary-row">
                            <span class="small">
                                <i class="bi bi-book me-1" aria-hidden="true"></i>
                                {{ $m->nome }}
                            </span>
                            <span class="small">$ {{ number_format($plan['price'] ?? 0, 2, ',', '.') }}</span>
                        </div>
                    @endforeach

                    <div class="order-summary-row order-summary-total">
                        <span class="fw-bold fs-5">{{ __('signup.checkout.total') }}</span>
                        <span class="fw-bold fs-5" style="color: var(--accent-purple);">$ {{ number_format($totalPrice, 2, ',', '.') }} ARS</span>
                    </div>
                </div>

                <p class="small text-muted mt-2 mb-2">{{ sprintf(__('signup.checkout.access_note'), $plan['duration'] ?? '') }}</p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                    {{ __('signup.checkout.secure') }}
                </p>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    @include('partials.checkout-country-postal-script', [
        'countryId' => 'checkout-country',
        'postalId' => 'checkout-postal',
    ])
@endpush
