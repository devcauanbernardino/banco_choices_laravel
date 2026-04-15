@extends('layouts.app')

@section('title', __('perfil.page_title'))
@section('mobile_title', __('perfil.mobile_title'))

@section('topbar_title', __('perfil.mobile_title'))

@section('content')
    <div class="container-fluid" style="max-width: 1100px;">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Hero banner --}}
        <div class="bc-hero mb-4">
            <div class="row align-items-end g-4 position-relative" style="z-index: 1;">
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
                <div class="bc-card p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3">{{ __('perfil.summary') }}</h2>
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
                </div>

                {{-- Theme toggle --}}
                <div class="bc-card p-4">
                    <h2 class="h6 fw-bold mb-3">{{ __('perfil.appearance') }}</h2>
                    @include('components.theme-toggle')
                    <p class="small text-muted mt-3 mb-0">{{ __('perfil.preference_note') }}</p>
                </div>
            </div>

            {{-- Right column --}}
            <div class="col-lg-8">
                {{-- Account data form --}}
                <div class="bc-card p-4 mb-4">
                    <h2 class="h6 fw-bold mb-4">{{ __('perfil.account_data') }}</h2>
                    <form action="{{ route('profile.update') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_name') }}</label>
                                <input type="text" class="form-control form-control-lg" name="nome" required
                                       value="{{ old('nome', $usuario->nome) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_email') }}</label>
                                <input type="email" class="form-control" value="{{ $usuario->email }}" readonly disabled>
                                <div class="form-text">{{ __('perfil.email_help') }}</div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        {{-- Password change --}}
                        <h3 class="h6 fw-bold mb-3">{{ __('perfil.security') }}</h3>
                        <p class="small text-muted">{{ __('perfil.security_hint') }}</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_cur_pass') }}</label>
                                <input type="password" class="form-control" name="senha_atual" placeholder="--------" autocomplete="current-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_new_pass') }}</label>
                                <input type="password" class="form-control" name="nova_senha" placeholder="{{ __('perfil.placeholder_new') }}" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="bc-form-actions">
                            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-end align-items-center">
                                <a href="{{ route('dashboard') }}"
                                   class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                                    <span class="material-icons" style="font-size: 1.15rem;" aria-hidden="true">arrow_back</span>
                                    {{ __('perfil.back') }}
                                </a>
                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <span class="material-icons" style="font-size: 1.15rem;" aria-hidden="true">save</span>
                                    {{ __('perfil.save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Logout section --}}
                <div class="bc-card p-4 border-danger border-opacity-25">
                    <h2 class="h6 fw-bold mb-2 text-danger">{{ __('perfil.logout_section') }}</h2>
                    <p class="small text-muted mb-3">{{ __('perfil.logout_hint') }}</p>
                    <a href="{{ route('logout') }}" class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <span class="material-icons fs-6">logout</span> {{ __('perfil.logout_btn') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
