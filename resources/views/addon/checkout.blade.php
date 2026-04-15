@extends('layouts.app')

@section('title', __('addon.page_title_checkout'))
@section('mobile_title', trim(explode('|', __('addon.page_title_checkout'))[0]))
@section('topbar_title', __('nav.buy_subjects'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .addon-checkout-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 991.98px) {
        .addon-checkout-grid { grid-template-columns: 1fr; }
    }
    .addon-sum-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--app-border, #e5e7eb);
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
    <p class="mb-3">
        <a href="{{ route('addon.materias') }}" class="link-primary text-decoration-none d-inline-flex align-items-center gap-1">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>{{ __('addon.back_materias') }}
        </a>
    </p>

    @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    <div class="bc-page-header mb-4">
        <div>
            <h5 class="mb-0 fw-bold">{{ __('addon.page_title_checkout') }}</h5>
            <small class="text-muted d-block mt-2">{{ __('addon.checkout_intro') }}</small>
        </div>
    </div>

    <div class="addon-checkout-grid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-credit-card me-2"></i>{{ __('signup.checkout.contact_title') }}</h3>
                <form method="post" action="{{ route('checkout.process') }}" id="addon-pay-form">
                    @csrf
                    <input type="hidden" name="checkout_kind" value="addon">
                    <input type="hidden" name="order_id" value="{{ $orderId }}">
                    <input type="hidden" name="total_price" value="{{ number_format($totalPrice, 2, '.', '') }}">
                    <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                    <input type="hidden" name="plan_duration_days" value="{{ (int) ($plan['durationDays'] ?? 0) }}">
                    <input type="hidden" name="materias" value="{{ implode(',', $materiasIds) }}">

                    <div class="mb-3">
                        <label class="form-label small text-muted">{{ __('signup.checkout.email') }}</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                        <div class="form-text">{{ __('addon.email_note') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">{{ __('signup.checkout.name') }}</label>
                        <input type="text" class="form-control" value="{{ $user->nome }}" readonly disabled>
                    </div>
                    @include('partials.checkout-country-postal-fields', [
                        'countries' => $countries,
                        'countryId' => 'addon-country',
                        'postalId' => 'addon-postal',
                        'countryDefault' => 'AR',
                        'requiredPostal' => true,
                        'labelClass' => 'form-label',
                    ])
                    @php
                        $mpPrefBase = rtrim((string) (config('mercadopago.checkout_base_url') ?: config('mercadopago.site_url')), '/');
                        $mpHttpsReturn = str_starts_with(strtolower($mpPrefBase), 'https://');
                    @endphp
                    @if (! $mpHttpsReturn)
                        <p class="small text-body-secondary border-start border-3 border-secondary ps-2 mb-3">{{ __('signup.checkout.mp_redirect_hint') }}</p>
                    @endif

                    <div class="form-check mb-3">
                        <input type="checkbox" id="addon-terms" name="terms" class="form-check-input" value="1" required>
                        <label class="form-check-label small" for="addon-terms">
                            {{ __('signup.checkout.terms_before') }}
                            <a href="{{ route('home') }}#terminos" target="_blank" rel="noopener noreferrer">{{ __('signup.checkout.terms_link') }}</a>{{ __('signup.checkout.terms_after') }}
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="addon-submit">
                        <i class="bi bi-lock-fill me-2"></i>
                        {{ sprintf(__('signup.checkout.submit_mp'), $totalPriceFormatted) }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-receipt me-2"></i>{{ __('addon.summary') }}</h3>
                @foreach ($materiasInfo as $mat)
                    <div class="addon-sum-row">
                        <span><i class="bi bi-book me-1"></i>{{ $mat->nome }}</span>
                        <span class="text-muted">$ {{ $unitPriceFormatted }} ARS</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between align-items-center pt-3 mt-2">
                    <span class="fw-bold">{{ __('signup.checkout.total') }}</span>
                    <span class="fs-5 fw-bold text-primary">$ {{ $totalPriceFormatted }} ARS</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.checkout-country-postal-script', [
        'countryId' => 'addon-country',
        'postalId' => 'addon-postal',
    ])
    <script>
        document.getElementById('addon-pay-form').addEventListener('submit', function () {
            var btn = document.getElementById('addon-submit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> …';
        });
    </script>
@endpush
