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
        --success-green: #10b981;
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
</style>
@endpush

@section('content')
<main class="container-custom">
    {{-- Top bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <a href="{{ route('home') }}" class="brand-mark" aria-label="Banco de Choices">
            <img src="{{ asset('img/logo-bd-transparente.png') }}" alt="Banco de Choices" width="180" height="40">
        </a>
        <a href="{{ route('signup.plano') }}" class="back-link">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
            {{ __('signup.back_plan') }}
        </a>
    </div>

    {{-- Header --}}
    <div class="header-section">
        <h1>{{ __('checkout.heading') }}</h1>
        <p class="opacity-90">{{ __('checkout.subheading') }}</p>
    </div>

    {{-- Step indicator --}}
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

    {{-- Checkout card --}}
    <div class="checkout-card">
        <div class="row g-4">
            {{-- Form --}}
            <div class="col-lg-7">
                <h5 class="fw-bold mb-4">{{ __('checkout.your_data') }}</h5>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $orderId }}">
                    <input type="hidden" name="plan_id" value="{{ $planId }}">
                    <input type="hidden" name="plan_duration_days" value="{{ $plan['duration_days'] ?? 30 }}">
                    <input type="hidden" name="materias" value="{{ is_array($materias) ? implode(',', $materias) : $materias }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">{{ __('checkout.label_email') }}</label>
                        <input type="email" class="form-control form-control-lg" name="email" required
                               value="{{ old('email') }}" placeholder="{{ __('checkout.placeholder_email') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">{{ __('checkout.label_name') }}</label>
                        <input type="text" class="form-control form-control-lg" name="nome" required
                               value="{{ old('nome') }}" placeholder="{{ __('checkout.placeholder_name') }}">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('checkout.label_country') }}</label>
                            <input type="text" class="form-control" name="country" value="{{ old('country', 'AR') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">{{ __('checkout.label_postal') }}</label>
                            <input type="text" class="form-control" name="postal_code" value="{{ old('postal_code') }}"
                                   placeholder="{{ __('checkout.placeholder_postal') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 mt-3">
                        <i class="bi bi-lock-fill" aria-hidden="true"></i>
                        {{ __('checkout.pay_btn') }}
                    </button>
                </form>
            </div>

            {{-- Order summary --}}
            <div class="col-lg-5">
                <h5 class="fw-bold mb-4">{{ __('checkout.summary_title') }}</h5>
                <div class="order-summary">
                    <div class="order-summary-row">
                        <span class="fw-bold">{{ $plan['name'] ?? '' }}</span>
                        <span class="small text-muted">{{ $plan['duration'] ?? '' }}</span>
                    </div>

                    @foreach ($materiasInfo as $m)
                        <div class="order-summary-row">
                            <span class="small">
                                <i class="bi bi-book me-1" aria-hidden="true"></i>
                                {{ $m['nome'] }}
                            </span>
                            <span class="small">$ {{ number_format($plan['price'] ?? 0, 2, ',', '.') }}</span>
                        </div>
                    @endforeach

                    <div class="order-summary-row order-summary-total">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-5" style="color: var(--accent-purple);">$ {{ number_format($totalPrice, 2, ',', '.') }} ARS</span>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                        {{ __('checkout.secure_note') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
