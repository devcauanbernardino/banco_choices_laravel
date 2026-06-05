@php
    /** @var \App\Models\Faculdade $faculdade */
    $faculdade = $faculdade ?? null;
    $demoCounts = $demoCounts ?? [];
    $temDemo = $faculdade && (($demoCounts[$faculdade->slug] ?? 0) > 0);
    $href = $href ?? ($faculdade && $temDemo
        ? route('demo.configurar', ['faculdade' => $faculdade->slug])
        : ($faculdade ? route('demo.show') : '#'));
    $proximamente = $proximamente ?? ($faculdade && ! $temDemo);
    $titulo = $titulo ?? ($faculdade->nome ?? '');
    $descricao = $descricao ?? ($faculdade->descricao_curta ?? '');
    $agrupamentos = collect();
    if ($faculdade && method_exists($faculdade, 'agrupamentos')) {
        $agrupamentos = $faculdade->relationLoaded('agrupamentos')
            ? $faculdade->agrupamentos
            : $faculdade->agrupamentos()->orderBy('ordem')->get();
    }
@endphp

@if($proximamente)
    <div class="lp-fac-card lp-fac-card--soon" aria-disabled="true">
        <div class="lp-fac-card__body">
            <div class="lp-fac-card__head">
                <h3 class="lp-fac-card__title">{{ $titulo }}</h3>
                <span class="lp-fac-card__badge">{{ __('landing.modalidades.proximamente') }}</span>
            </div>
            <p class="lp-fac-card__desc">{{ $descricao }}</p>
        </div>
    </div>
@else
    <a href="{{ $href }}" class="lp-fac-card">
        <div class="lp-fac-card__body">
            <div class="lp-fac-card__head">
                <h3 class="lp-fac-card__title">{{ $titulo }}</h3>
                <span class="lp-fac-card__arrow" aria-hidden="true">→</span>
            </div>
            @if($descricao)
                <p class="lp-fac-card__desc">{{ $descricao }}</p>
            @endif
            @if($agrupamentos->isNotEmpty())
                <p class="lp-fac-card__meta">
                    {{ $agrupamentos->pluck('nome')->take(3)->implode(' • ') }}
                </p>
            @endif
        </div>
    </a>
@endif
