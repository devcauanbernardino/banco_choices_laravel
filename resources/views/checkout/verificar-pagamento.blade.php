@extends('layouts.public')

@section('title', __('payment_status.page_title'))

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
    .container-custom { max-width: 640px; margin: 0 auto; padding: 2.5rem 1rem; }
    .status-card { background: rgba(255,255,255,0.98); border-radius: 20px; padding: 2.25rem 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .pedido-row { border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.1rem; margin-bottom: 0.75rem; }
    .pedido-status { font-weight: 700; font-size: 0.8rem; padding: 0.25rem 0.65rem; border-radius: 999px; display: inline-block; }
    .pedido-status--completed { background: rgba(16,185,129,0.12); color: #10b981; }
    .pedido-status--awaiting_payment { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; font-weight: 600; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<main class="container-custom">
    <a href="{{ route('home') }}" class="back-link">
        <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
        {{ __('signup.back_home') }}
    </a>

    <div class="status-card">
        <h1 class="fw-bold mb-2" style="font-size: 1.5rem;">{{ __('payment_status.title') }}</h1>
        <p class="text-muted mb-4">{{ __('payment_status.subtitle') }}</p>

        <form method="GET" action="{{ route('payment.status') }}" class="d-flex gap-2 flex-wrap mb-4">
            <input type="email" name="email" required value="{{ $email }}"
                   class="form-control flex-grow-1" style="min-width: 200px;"
                   placeholder="{{ __('payment_status.email_placeholder') }}">
            <button type="submit" class="btn btn-primary fw-semibold px-4">{{ __('payment_status.search_btn') }}</button>
        </form>

        @if ($email !== '')
            @if ($pedidos->isEmpty())
                <div class="alert alert-warning small mb-0">{{ __('payment_status.not_found') }}</div>
            @else
                @foreach ($pedidos as $pedido)
                    <div class="pedido-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $pedido->stripe_payment_id }}</div>
                            <div class="small text-muted">{{ optional($pedido->data_criacao)->format('d/m/Y H:i') }} — $ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</div>
                        </div>
                        <span class="pedido-status pedido-status--{{ $pedido->status }}">
                            {{ $pedido->status === 'completed' ? __('payment_status.status_completed') : __('payment_status.status_pending') }}
                        </span>
                    </div>
                @endforeach

                <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 mt-3 fw-semibold">
                    {{ __('payment_status.go_login') }}
                </a>
            @endif
        @endif
    </div>
</main>
@endsection
