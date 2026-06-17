@extends('layouts.public')

@section('title', __('terms.page_title') . ' — Banco de Choices')

@push('styles')
    <style>
        .terms-page { background: #f8fafc; min-height: 100vh; padding: 3rem 0 4rem; }
        .terms-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            padding: 2.5rem 2.75rem;
            max-width: 780px;
            margin: 0 auto;
        }
        .terms-card h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #181c20;
            margin-bottom: 0.35rem;
        }
        .terms-card .terms-updated {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 2rem;
        }
        .terms-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #6a0392;
            margin-top: 2rem;
            margin-bottom: 0.6rem;
        }
        .terms-card p, .terms-card li {
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.65;
        }
        .terms-card ul { padding-left: 1.25rem; }
        .terms-back {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #6a0392;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 1.5rem;
        }
        .terms-back:hover { text-decoration: underline; }
    </style>
@endpush

@section('content')
    <div class="terms-page">
        <div class="container">
            <div class="terms-card">
                <a href="{{ route('home') }}" class="terms-back">&larr; {{ __('terms.back_home') }}</a>

                <h1>{{ __('terms.title') }}</h1>
                <p class="terms-updated">{{ __('terms.updated_at') }}</p>

                <p>{{ __('terms.intro') }}</p>

                <h2>{{ __('terms.section_service_title') }}</h2>
                <p>{{ __('terms.section_service_body') }}</p>

                <h2>{{ __('terms.section_account_title') }}</h2>
                <p>{{ __('terms.section_account_body') }}</p>

                <h2>{{ __('terms.section_payment_title') }}</h2>
                <p>{{ __('terms.section_payment_body') }}</p>

                <h2>{{ __('terms.section_refund_title') }}</h2>
                <p>{{ __('terms.section_refund_body') }}</p>

                <h2>{{ __('terms.section_content_title') }}</h2>
                <p>{{ __('terms.section_content_body') }}</p>

                <h2>{{ __('terms.section_privacy_title') }}</h2>
                <p>{{ __('terms.section_privacy_body') }}</p>

                <h2>{{ __('terms.section_liability_title') }}</h2>
                <p>{{ __('terms.section_liability_body') }}</p>

                <h2>{{ __('terms.section_changes_title') }}</h2>
                <p>{{ __('terms.section_changes_body') }}</p>

                <h2>{{ __('terms.section_contact_title') }}</h2>
                <p>{!! __('terms.section_contact_body') !!}</p>
            </div>
        </div>
    </div>
@endsection
