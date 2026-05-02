@extends('layouts.app')

@section('title', __('addon.page_title_checkout'))
@section('mobile_title', trim(explode('|', __('addon.page_title_checkout'))[0]))
@section('topbar_title', __('nav.buy_subjects'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/addon-checkout-painel.css') }}">
@endpush

@section('content')
    <p class="mb-3">
        <a href="{{ route('addon.materias') }}" class="link-primary text-decoration-none d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined addon-checkout-back-ico" aria-hidden="true">chevron_left</span>{{ __('addon.back_materias') }}
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
            <div class="card-body bc-card-body--fluid">
                <h3 class="h6 fw-bold mb-3 d-inline-flex align-items-center gap-2"><span class="material-symbols-outlined addon-checkout-heading-ico" aria-hidden="true">credit_card</span>{{ __('signup.checkout.contact_title') }}</h3>
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

                    <div class="mb-3">
                        <label class="form-label small text-muted" for="codigoCupomAddon">{{ __('referral.codigo_opcional_label') }}</label>
                        <input type="text" class="form-control" name="codigo_cupom_usado" id="codigoCupomAddon"
                               value="{{ old('codigo_cupom_usado') }}" maxlength="40" autocomplete="off"
                               placeholder="{{ __('referral.codigo_placeholder') }}">
                    </div>
                    @if ((float) ($user->saldo_credito ?? 0) > 0)
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="usar_credito_addon" id="addonUsarCredito"
                                       @checked(old('usar_credito_addon'))>
                                <label class="form-check-label" for="addonUsarCredito">
                                    {{ __('referral.usar_saldo_checkbox', ['saldo' => number_format((float) $user->saldo_credito, 2, ',', '.')]) }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('referral.usar_saldo_hint') }}</div>
                        </div>
                    @endif

                    <div class="checkout-terms-wrap mb-3">
                        <div class="checkout-terms-row">
                            <input type="checkbox" id="addon-terms" name="terms" class="form-check-input" value="1" required
                                   aria-required="true">
                            <label class="form-check-label small mb-0" for="addon-terms">
                                {!! __('signup.checkout.terms_label', ['url' => route('home').'#terminos']) !!}
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2" id="addon-submit">
                        <span class="material-symbols-outlined addon-checkout-heading-ico" aria-hidden="true">lock</span>
                        {{ sprintf(__('signup.checkout.submit_mp'), $totalPriceFormatted) }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body bc-card-body--fluid">
                <h3 class="h6 fw-bold mb-3 d-inline-flex align-items-center gap-2"><span class="material-symbols-outlined addon-checkout-heading-ico" aria-hidden="true">receipt_long</span>{{ __('addon.summary') }}</h3>
                @foreach ($materiasInfo as $mat)
                    <div class="addon-sum-row">
                        <span class="d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined addon-checkout-heading-ico" aria-hidden="true">menu_book</span>{{ $mat->nome }}</span>
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
