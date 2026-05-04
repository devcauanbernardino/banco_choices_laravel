@php
    $plans = config('signup.plans', []);
    $order = ['monthly', 'semester', 'annual'];
    $durationKeys = [
        'monthly' => 'landing.planes.duration_monthly',
        'semester' => 'landing.planes.duration_semester',
        'annual' => 'landing.planes.duration_annual',
    ];
    $currency = config('branding.currency_symbol', 'AR$ ');
    $fmt = fn ($v) => \App\Support\PricingDisplay::formatArsForCheckout((float) $v);

    $monthlyPrice = (float) ($plans['monthly']['price'] ?? 0);

    $rendered = [];
    foreach ($order as $key) {
        if (! isset($plans[$key])) {
            continue;
        }
        $p = $plans[$key];
        $months = max(1, (int) round(((int) ($p['days'] ?? 30)) / 30));
        $price = (float) ($p['price'] ?? 0);
        $perMonth = $months > 0 ? $price / $months : $price;
        $savePct = 0;
        if ($monthlyPrice > 0 && $key !== 'monthly') {
            $base = $monthlyPrice * $months;
            $savePct = (int) round((1 - ($price / $base)) * 100);
        }
        $rendered[] = [
            'key' => $key,
            'plan' => $p,
            'months' => $months,
            'price' => $price,
            'perMonth' => $perMonth,
            'savePct' => $savePct,
            'precioOriginal' => isset($p['precio_original']) ? (float) $p['precio_original'] : null,
            'isHighlight' => $key === 'annual',
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
                @if($r['savePct'] > 0)
                    <span class="lp-plan-card__save">{{ __('landing.planes.ahorras', ['percent' => $r['savePct']]) }}</span>
                @endif
            </header>
            <div class="lp-plan-card__pricing">
                @if($r['precioOriginal'])
                    <span class="lp-plan-card__price-old">{{ $currency }}{{ $fmt($r['precioOriginal']) }}</span>
                @endif
                <div class="lp-plan-card__price">
                    <span class="lp-plan-card__currency">{{ $currency }}</span>
                    <span class="lp-plan-card__amount">{{ $fmt($r['price']) }}</span>
                </div>
                @if($r['months'] > 1)
                    <span class="lp-plan-card__permonth">
                        {{ $currency }}{{ $fmt($r['perMonth']) }}{{ __('landing.planes.por_mes') }}
                    </span>
                @endif
            </div>
            <ul class="lp-plan-card__features" role="list">
                @foreach(['feat1','feat2','feat3','feat4','feat5'] as $f)
                    <li><i class="bi bi-check-circle-fill"></i> {{ __('landing.planes.'.$f) }}</li>
                @endforeach
            </ul>
            <a href="{{ route('signup.materias') }}?plano={{ $r['key'] }}" class="btn lp-btn-primary w-100">
                {{ __('landing.planes.cta') }}
            </a>
        </article>
    @endforeach
</div>
