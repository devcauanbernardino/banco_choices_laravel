@extends('layouts.public')

@section('title', __('demo.resultado.page_title'))
@section('body_attr')
 class="lp-body demo-body"
@endsection

@push('styles')
    @include('pages.partials.landing-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}?v={{ filemtime(public_path('assets/css/demo.css')) }}">
@endpush

@section('public_topbar')
    @include('pages.partials.topbar')
@endsection

@section('public_offcanvas')
    @include('pages.partials.offcanvas-public')
@endsection

@section('public_footer')
    @include('pages.partials.footer')
@endsection

@section('content')
@php
    $signupUrl = route('signup.materias').'?demo=1';
    if (!empty($materiaPreId)) {
        $signupUrl .= '&materia_id='.(int) $materiaPreId;
    }
    $limiteMateria = (($motivoPaywall ?? '') === 'limite_materia');
@endphp
<section class="demo-section">
    <div class="lp-container">
        <div class="demo-result">
            <div class="demo-result__hero">
                @if($limiteMateria)
                    <div class="demo-result__icon-wrap demo-result__icon-wrap--soft" aria-hidden="true">
                        <i class="bi bi-clock-history demo-result__icon-soft"></i>
                    </div>
                    <h1 class="demo-result__title">{{ __('demo.resultado.heading_limite_materia') }}</h1>
                    <p class="demo-result__lead text-muted">{{ __('demo.resultado.lead_limite_materia') }}</p>
                    <p class="demo-result__aux mb-0">
                        <a href="{{ route('demo.show') }}" class="demo-result__aux-link">{{ __('demo.resultado.link_novo_demo') }}</a>
                    </p>
                @else
                    <div class="demo-result__trophy" aria-hidden="true">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <h1 class="demo-result__title">{{ __('demo.resultado.heading') }}</h1>
                    <p class="demo-result__score">
                        {{ __('demo.resultado.score', ['acertos' => $acertos, 'total' => $total]) }}
                        <span class="demo-result__pct">· {{ __('demo.resultado.percent', ['pct' => $pct]) }}</span>
                    </p>
                @endif
            </div>

            <article class="demo-result__paywall">
                <h2 class="demo-result__paywall-title">{{ __('demo.resultado.title') }}</h2>
                <ul class="demo-result__features" role="list">
                    @foreach(['feat1','feat2','feat3','feat4'] as $f)
                        <li><i class="bi bi-check-circle-fill"></i> {{ __('demo.resultado.'.$f) }}</li>
                    @endforeach
                </ul>
                <div class="demo-result__ctas">
                    <a href="{{ $signupUrl }}" class="btn lp-btn-primary lp-btn-lg">
                        {{ __('demo.resultado.cta_planes') }}
                    </a>
                    <a href="{{ route('login') }}" class="btn lp-btn-outline">
                        {{ __('demo.resultado.cta_login') }}
                    </a>
                </div>
            </article>

            <article class="demo-result__share">
                <div class="demo-result__share-text">
                    <h3 class="demo-result__share-title">{{ __('demo.resultado.share_title') }}</h3>
                    <p class="demo-result__share-desc">{{ __('demo.resultado.share_desc') }}</p>
                </div>
                <a href="{{ route('signup.materias') }}?demo=1&ref=1" class="btn lp-btn-primary">
                    {{ __('demo.resultado.share_cta') }}
                </a>
            </article>
        </div>
    </div>
</section>
@endsection
