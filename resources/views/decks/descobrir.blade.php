@extends('layouts.app')

@section('title', __('decks.descobrir.page_title'))
@section('mobile_title', __('decks.descobrir.mobile_title'))
@section('topbar_title', __('decks.descobrir.mobile_title'))

@push('styles')
<style>
.dkd-header { margin-bottom: 22px; }
.dkd-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.dkd-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }
.dkd-back { display: inline-flex; align-items: center; gap: 4px; color: var(--app-muted); font-size: .82rem; text-decoration: none; margin-bottom: 14px; }
.dkd-back:hover { color: #8b1fb8; }

.dkd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px; }
.dkd-card {
    border-radius: 18px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
}
[data-theme="dark"] .dkd-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }
.dkd-card__icon { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg,#8b1fb8,#6a0392); display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(106,3,146,.28); }
.dkd-card__icon .material-symbols-outlined { color: #fff; font-size: 1.3rem; }
.dkd-card__title { font-size: 1rem; font-weight: 700; color: var(--app-text); margin: 0; }
.dkd-card__meta { font-size: .78rem; color: var(--app-muted); margin: 0; }
.dkd-card__desc { font-size: .82rem; color: var(--app-muted); margin: 0; line-height: 1.4; }
.dkd-badge { font-size: .74rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; background: rgba(139,31,184,.14); color: #8b1fb8; display: inline-block; width: fit-content; }
[data-theme="dark"] .dkd-badge { background: rgba(199,125,253,.16); color: #c77dfd; }
.dkd-card__btn { margin-top: auto; padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.dkd-card__btn:disabled { opacity: .5; cursor: default; box-shadow: none; }
.dkd-empty { text-align: center; padding: 60px 20px; color: var(--app-muted); }
.dkd-empty .material-symbols-outlined { font-size: 3rem; color: #8b1fb8; opacity: .5; margin-bottom: 12px; display: block; }
</style>
@endpush

@section('content')
<a href="{{ route('decks.index') }}" class="dkd-back">
    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_back</span>
    {{ __('decks.form.back_to_list') }}
</a>

<div class="dkd-header">
    <h1>{{ __('decks.descobrir.title') }}</h1>
    <p>{{ __('decks.descobrir.sub') }}</p>
</div>

@if ($decks->isEmpty())
    <div class="dkd-empty">
        <span class="material-symbols-outlined" aria-hidden="true">explore</span>
        <p class="mb-0">{{ __('decks.descobrir.empty') }}</p>
    </div>
@else
    <div class="dkd-grid">
        @foreach ($decks as $d)
            @php $jaClonado = in_array($d->id, $jaClonados, true); @endphp
            <div class="dkd-card">
                <div class="dkd-card__icon"><span class="material-symbols-outlined" aria-hidden="true">stacks</span></div>
                <span class="dkd-badge">{{ $d->materia->nome ?? '' }}</span>
                <h3 class="dkd-card__title">{{ $d->nome }}</h3>
                <p class="dkd-card__meta">{{ __('decks.descobrir.by', ['nome' => $d->usuario->nome ?? '—']) }} · {{ __('decks.descobrir.card_count', ['n' => $d->cartas_count]) }}</p>
                @if ($d->descricao)
                    <p class="dkd-card__desc">{{ $d->descricao }}</p>
                @endif
                <form method="POST" action="{{ route('decks.clonar', $d) }}">
                    @csrf
                    <button type="submit" class="dkd-card__btn w-100" @if ($jaClonado) disabled @endif>
                        {{ $jaClonado ? __('decks.descobrir.already_added') : __('decks.descobrir.add_to_mine') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
