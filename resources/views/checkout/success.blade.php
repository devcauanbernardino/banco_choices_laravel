@extends('layouts.public')

@section('title', __('signup.page_title.payment_ok'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --accent-purple: #6a0392;
        --accent-purple-lighter: rgba(106,3,146,0.12);
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
    .container-custom { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }
    .success-card { background: rgba(255,255,255,0.98); border-radius: 20px; padding: 2.5rem 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
    .success-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin-bottom: 1.5rem;
    }
    .success-icon--approved { background: rgba(16,185,129,0.1); color: var(--success-green); }
    .success-icon--pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
    .success-icon--failed { background: rgba(239,68,68,0.1); color: #ef4444; }
    .step-indicator { display: flex; justify-content: space-between; margin-bottom: 2.5rem; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: rgba(255,255,255,0.2); }
    .step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex: 1; }
    .step-number { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 2px solid white; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; margin-bottom: 0.5rem; }
    .step.active .step-number { background: var(--accent-purple); box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter); }
    .step-label { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; }
    .step.active .step-label { color: white; }
    .next-steps { text-align: left; background: #f8f9fa; border-radius: 12px; padding: 1.25rem; margin: 1.5rem 0; }
    .next-steps li { padding: 0.3rem 0; font-size: 0.9rem; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; font-weight: 600; }
    .back-link:hover { color: rgba(255,255,255,0.85); }
</style>
@endpush

@php
    $checkoutKind = $pendingOrder['checkout_kind'] ?? 'signup';
    $isAddon = $checkoutKind === 'addon' && auth()->check();
@endphp

@section('content')
<main class="container-custom">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <a href="{{ route('home') }}" class="back-link">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
            {{ __('signup.back_home') }}
        </a>
        <div class="signup-flow-topbar flex-shrink-0 ms-auto">
            <div class="navbar-actions navbar-actions--landing">
                <div class="navbar-actions__inner">
                    @include('components.language-selector')
                </div>
            </div>
        </div>
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
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">{{ __('signup.step.pago') }}</div>
        </div>
        <div class="step active" aria-current="step">
            <div class="step-number">4</div>
            <div class="step-label">{{ __('signup.step.confirmacion') }}</div>
        </div>
    </div>

    <div class="success-card">
        @if ($status === 'approved')
            <div class="success-icon success-icon--approved">
                <i class="bi bi-check-circle-fill" style="font-size: 2.5rem;"></i>
            </div>
            <h2 class="fw-bold mb-2" style="color: var(--success-green);">{{ __('signup.payment.confirmed_h1') }}</h2>
            <p class="text-muted mb-0">{{ __('signup.payment.success_p') }}</p>
        @elseif ($status === 'pending')
            <div class="success-icon success-icon--pending">
                <i class="bi bi-hourglass-split" style="font-size: 2.5rem;"></i>
            </div>
            <h2 class="fw-bold mb-2" style="color: #f59e0b;">{{ __('signup.payment.received_h1') }}</h2>
            <p class="text-muted mb-0">{{ __('signup.payment.processing_p') }}</p>
        @else
            <div class="success-icon success-icon--failed">
                <i class="bi bi-x-circle-fill" style="font-size: 2.5rem;"></i>
            </div>
            <h2 class="fw-bold mb-2" style="color: #ef4444;">{{ __('signup.payment.error_h1') }}</h2>
            <p class="text-muted mb-0">{{ __('signup.payment.error_sub') }}</p>
        @endif

        @if (!empty($pendingOrder['email']))
            <div class="mt-3 p-3 rounded-3 text-start" style="background: var(--accent-purple-lighter);">
                <small class="text-muted">{{ __('signup.payment.order_email') }}</small>
                <div class="fw-bold">{{ $pendingOrder['email'] }}</div>
            </div>
        @endif

        @if (!empty($orderId))
            <div class="mt-2 text-start">
                <small class="text-muted">{{ __('signup.payment.order_id') }} <strong>{{ $orderId }}</strong></small>
            </div>
        @endif

        @if ($isAddon)
            <div class="next-steps text-start mt-3">
                <p class="fw-bold mb-1"><i class="bi bi-info-circle me-2"></i>{{ __('addon.payment.next_title') }}</p>
                <p class="mb-3 small">{{ __('addon.payment.next_p') }}</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-speedometer2" aria-hidden="true"></i>
                    {{ __('addon.payment.btn_panel') }}
                </a>
            </div>
        @else
            <div class="next-steps">
                <h6 class="fw-bold mb-2">{{ __('signup.payment.next_title') }}</h6>
                <ol class="mb-0 ps-3">
                    <li>{{ __('signup.payment.next1') }}</li>
                    <li>{{ __('signup.payment.next2') }}</li>
                    <li>{{ __('signup.payment.next3') }}</li>
                </ol>
            </div>

            <a href="{{ route('login') }}" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 mt-2">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                {{ __('signup.payment.btn_login') }}
            </a>
        @endif
    </div>
</main>
@endsection
