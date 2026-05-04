@extends('layouts.public')

@section('title', __('demo.page_title'))
@section('body_attr', ' class="lp-body demo-body"')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/landing-v2.css') }}?v={{ filemtime(public_path('assets/css/landing-v2.css')) }}">
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
    <section class="demo-section">
        <div class="lp-container">
            <header class="lp-section__header text-start">
                <span class="lp-badge">{{ __('demo.configurar.badge_demo') }}</span>
                <h1 class="lp-h-section">{{ __('demo.show.heading') }}</h1>
                <p class="demo-section__lead">{{ __('demo.show.subtitle') }}</p>
            </header>

            @if(session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif

            <div class="lp-fac-grid">
                @forelse($faculdades as $fac)
                    @include('pages.partials.faculdade-card', ['faculdade' => $fac])
                @empty
                    <div class="lp-fac-card lp-fac-card--soon">
                        <div class="lp-fac-card__head">
                            <h3 class="lp-fac-card__title">{{ __('demo.questao.no_questions') }}</h3>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
