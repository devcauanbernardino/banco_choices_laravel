@extends('layouts.public')

@section('title', __('perfil.force_change_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root { --accent-purple: #6a0392; }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%);
        min-height: 100vh;
    }
    .force-pw-card {
        max-width: 480px;
        margin: 4rem auto;
        background: rgba(255,255,255,0.98);
        border-radius: 20px;
        padding: 2.25rem 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .force-pw-icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: rgba(106,3,146,0.1); color: var(--accent-purple);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }
</style>
@endpush

@section('content')
<main class="container-fluid">
    <div class="force-pw-card text-center">
        <div class="force-pw-icon">
            <i class="bi bi-shield-lock-fill" style="font-size: 1.75rem;"></i>
        </div>
        <h1 class="h4 fw-bold mb-2">{{ __('perfil.force_change_title') }}</h1>
        <p class="text-muted mb-4">{{ __('perfil.force_change_subtitle') }}</p>

        @if ($errors->any())
            <div class="alert alert-danger text-start small">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.force-change.store') }}" class="text-start">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small" for="novaSenhaForceInput">{{ __('perfil.label_new_pass') }}</label>
                <input type="password" class="form-control form-control-lg" id="novaSenhaForceInput" name="nova_senha" required autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small" for="confirmaSenhaForceInput">{{ __('perfil.force_change_confirm_label') }}</label>
                <input type="password" class="form-control form-control-lg" id="confirmaSenhaForceInput" name="confirma_senha" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                {{ __('perfil.force_change_submit') }}
            </button>
        </form>
    </div>
</main>
@endsection
