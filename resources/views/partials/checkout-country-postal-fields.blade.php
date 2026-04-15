@php
    $countryId = $countryId ?? 'checkout-country';
    $postalId = $postalId ?? 'checkout-postal';
    $countryName = $countryName ?? 'country';
    $postalName = $postalName ?? 'postal_code';
    $countryDefault = $countryDefault ?? 'AR';
    $requiredPostal = $requiredPostal ?? false;
    $labelClass = $labelClass ?? 'form-label fw-semibold small';
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="{{ $labelClass }}" for="{{ $countryId }}">{{ __('signup.checkout.country') }}</label>
        <select class="form-select" name="{{ $countryName }}" id="{{ $countryId }}" required autocomplete="country">
            @foreach ($countries as $code => $label)
                <option value="{{ $code }}" @selected(old($countryName, $countryDefault) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="{{ $labelClass }}" for="{{ $postalId }}">{{ __('signup.checkout.postal') }}</label>
        <input type="text" class="form-control" name="{{ $postalName }}" id="{{ $postalId }}"
               value="{{ old($postalName) }}" autocomplete="postal-code"
               placeholder="{{ __('signup.checkout.postal_ph') }}" @if($requiredPostal) required @endif>
        <div class="small mt-1" id="{{ $postalId }}-feedback" role="status" aria-live="polite" hidden></div>
    </div>
</div>
