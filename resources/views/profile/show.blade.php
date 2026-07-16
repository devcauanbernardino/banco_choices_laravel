@extends('layouts.app')

@section('title', __('perfil.page_title'))
@section('mobile_title', __('perfil.mobile_title'))

@section('topbar_title', __('perfil.mobile_title'))

@section('content')
    <div class="container-fluid bc-private-wrap--profile">

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Hero banner --}}
        <div class="bc-hero mb-4">
            <div class="row align-items-end g-4 position-relative bc-profile-hero-row">
                <div class="col-md-auto text-center text-md-start">
                    <img class="bc-hero-avatar"
                         src="https://ui-avatars.com/api/?name={{ urlencode($usuario->nome) }}&size=224&background=ffffff&color=6a0392"
                         alt="">
                </div>
                <div class="col-md">
                    <h1 class="h3 fw-bold mb-1 text-white">{{ $usuario->nome }}</h1>
                    <p class="mb-2 opacity-90 small text-white">{{ $usuario->email }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($materias as $mat)
                            <span class="bc-materia-chip">{{ $mat->nome ?? $mat['nome'] ?? '' }}</span>
                        @empty
                            <span class="badge bg-light text-dark">{{ __('perfil.no_materias') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left column --}}
            <div class="col-lg-4">
                {{-- Stats summary --}}
                <div class="bc-card bc-card--p-fluid mb-4">
                    <h2 class="bc-profile-section-title mb-3">{{ __('perfil.summary') }}</h2>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_sims') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ (int) $totalSimulados }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_questions') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ number_format((int) $totalQuestoes, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_avg') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ $mediaGeral }}%</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('stats') }}" class="btn btn-outline-primary btn-sm w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 1.1rem;">bar_chart</span>
                        {{ __('perfil.view_detailed_stats') }}
                    </a>
                </div>

                {{-- Contato/suporte --}}
                <div class="bc-card bc-card--p-fluid mb-4">
                    <h2 class="bc-profile-section-title mb-3">{{ __('perfil.contact_title') }}</h2>
                    <a href="mailto:{{ config('mail.from.address') }}" class="bc-stat-card d-flex flex-column align-items-start gap-1 text-decoration-none">
                        <span class="d-inline-flex align-items-center gap-2 text-muted small">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 1.1rem;">mail</span>
                            {{ __('perfil.contact_hint') }}
                        </span>
                        <span class="fw-bold text-primary">{{ config('mail.from.address') }}</span>
                    </a>
                </div>
            </div>

            {{-- Right column --}}
            <div class="col-lg-8">
                <form action="{{ route('profile.update') }}" method="post" autocomplete="off" class="bc-profile-form">
                    @csrf

                    {{-- Dados da conta --}}
                    <div class="bc-card bc-card--p-fluid mb-4">
                        <header class="bc-profile-section-head">
                            <h2 class="bc-profile-section-title">{{ __('perfil.account_data') }}</h2>
                        </header>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="bc-profile-label" for="profileNomeInput">{{ __('perfil.label_name') }}</label>
                                <input type="text" class="form-control bc-profile-input" id="profileNomeInput" name="nome" required
                                       value="{{ old('nome', $usuario->nome) }}">
                            </div>
                        </div>

                        <div class="bc-form-actions bc-form-actions--profile">
                            <div class="d-flex flex-wrap gap-2 justify-content-stretch justify-content-md-end align-items-center">
                                <a href="{{ route('dashboard') }}"
                                   class="btn btn-outline-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-md-grow-0">
                                    <span class="material-symbols-outlined bc-perfil-symbol-btn" aria-hidden="true">arrow_back</span>
                                    {{ __('perfil.back') }}
                                </a>
                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-md-grow-0">
                                    <span class="material-symbols-outlined bc-perfil-symbol-btn" aria-hidden="true">save</span>
                                    {{ __('perfil.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Logout section --}}
                <div class="bc-card bc-card--p-fluid border-danger border-opacity-25">
                    <h2 class="h6 fw-bold mb-2 text-danger">{{ __('perfil.logout_section') }}</h2>
                    <p class="small text-muted mb-3">{{ __('perfil.logout_hint') }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            <span class="material-icons fs-6" aria-hidden="true">logout</span> {{ __('perfil.logout_btn') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
