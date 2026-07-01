@php
    $plans = config('signup.plans', []);
    $order = ['daily', 'monthly', 'weekly'];
    $durationKeys = [
        'daily' => 'landing.planes.duration_daily',
        'weekly' => 'landing.planes.duration_weekly',
        'monthly' => 'landing.planes.duration_monthly',
    ];
    $isBr = app()->getLocale() === 'pt_BR';
    $currency = $isBr ? 'R$ ' : 'AR$ ';
    $fmt = fn ($v) => \App\Support\PricingDisplay::formatArsForCheckout((float) $v);

    $rendered = [];
    foreach ($order as $key) {
        if (! isset($plans[$key])) {
            continue;
        }
        $p = $plans[$key];
        $rendered[] = [
            'key' => $key,
            'plan' => $p,
            'price' => $isBr ? (float) ($p['price_brl'] ?? 0) : (float) ($p['price'] ?? 0),
            'isHighlight' => $key === 'monthly',
            'isPopular' => (bool) ($p['popular'] ?? false),
        ];
    }
@endphp

<div class="lp-planes-grid">
    @foreach($rendered as $r)
        <article class="lp-plan-card @if($r['isHighlight']) lp-plan-card--highlight @endif">
            @if($r['isHighlight'])
                <span class="lp-plan-card__ribbon">{{ __('landing.planes.recomendado') }}</span>
            @endif
            <header class="lp-plan-card__header">
                <h3 class="lp-plan-card__duration">{{ __($durationKeys[$r['key']]) }}</h3>
            </header>
            <div class="lp-plan-card__pricing">
                <div class="lp-plan-card__price">
                    <span class="lp-plan-card__currency">{{ $currency }}</span>
                    <span class="lp-plan-card__amount">{{ $fmt($r['price']) }}</span>
                </div>
            </div>
            <ul class="lp-plan-card__features" role="list">
                @foreach(['feat1','feat2','feat3','feat4','feat5'] as $f)
                    <li><i class="bi bi-check-lg"></i> {{ __('landing.planes.'.$f) }}</li>
                @endforeach
            </ul>
            <a href="{{ route('signup.materias') }}?plano={{ $r['key'] }}"
               class="btn w-100 @if($r['isHighlight']) lp-btn-primary @else lp-btn-outline @endif">
                {{ __('landing.planes.cta') }}
            </a>
        </article>
    @endforeach
</div>
