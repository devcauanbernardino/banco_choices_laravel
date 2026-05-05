@php
    /** @var \App\Models\Faculdade $faculdade */
    $slug = $faculdade->slug ?? '';
    $nome = (string) ($faculdade->nome ?? '');
    $initial = $nome !== ''
        ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($nome, 0, 1))
        : '?';
@endphp
<a href="{{ route('demo.configurar', ['faculdade' => $slug]) }}" class="demo-pick-card">
    <span class="demo-pick-card__stripe" aria-hidden="true"></span>
    <span class="demo-pick-card__avatar" aria-hidden="true">{{ $initial }}</span>
    <div class="demo-pick-card__main">
        <h3 class="demo-pick-card__title">{{ $faculdade->nome }}</h3>
        @if($faculdade->descricao_curta ?? null)
            <p class="demo-pick-card__desc">{{ $faculdade->descricao_curta }}</p>
        @endif
    </div>
    <span class="demo-pick-card__go" aria-hidden="true"><i class="bi bi-arrow-right-circle"></i></span>
</a>
