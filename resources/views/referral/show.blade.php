@extends('layouts.app')

@section('title', __('referral.page_title'))
@section('mobile_title', __('referral.mobile_title'))

@section('topbar_title', __('referral.mobile_title'))

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .ref-shell { max-width: 1040px; }
        .ref-hero-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(106, 3, 146, 0.15);
            background: linear-gradient(135deg, rgba(106, 3, 146, 0.12) 0%, rgba(255,255,255,0.94) 45%, rgba(245,240,252,1) 100%);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            box-shadow: 0 8px 28px rgba(31, 10, 60, .08);
        }
        [data-theme="dark"] .ref-hero-card {
            background: linear-gradient(135deg, rgba(168,85,247,0.14) 0%, rgba(30,27,40,1) 50%, rgba(24,21,31,1) 100%);
            border-color: rgba(196,181,253,0.2);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .35);
        }
        .ref-code-box {
            font-family: ui-monospace, monospace;
            font-size: clamp(1.25rem, 3vw, 1.85rem);
            letter-spacing: 0.06em;
            color: var(--bc-app-text, inherit);
            background: rgba(106, 3, 146, 0.08);
            border-radius: 0.65rem;
            padding: 0.65rem 1rem;
        }
        [data-theme="dark"] .ref-code-box { background: rgba(167,139,250,0.14); }
        .ref-side-card {
            border-radius: 1rem;
            background: rgba(255, 255, 255, .55) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, .5) !important;
            box-shadow: 0 8px 28px rgba(31, 10, 60, .08) !important;
        }
        [data-theme="dark"] .ref-side-card {
            background: rgba(255, 255, 255, .05) !important;
            border-color: rgba(255, 255, 255, .1) !important;
            box-shadow: 0 8px 28px rgba(0, 0, 0, .35) !important;
        }
        .ref-cond-ul li { margin-bottom: .45rem; }
    </style>
@endpush

@php
    $codigoCupom = trim((string) ($user->codigo_cupom ?? ''));
    $shareMsg = __('referral.share_message', ['code' => $codigoCupom ?: 'BC-______', 'url' => route('signup.materias')]);
    $waLink = 'https://wa.me/?text=' . rawurlencode($shareMsg);
    $saldoFmt = number_format((float) ($user->saldo_credito ?? 0), 2, ',', '.');
    $minSaque = number_format((float) config('referral.minimo_saque', 0), 0, ',', '.');
@endphp

@section('content')
<div class="container py-4 ref-shell">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ref-hero-card p-4 p-md-5 mb-4">
                <p class="text-uppercase small fw-semibold mb-2" style="letter-spacing:.08em;color:#6a0392">{{ __('referral.hero_kicker') }}</p>
                <h1 class="h3 fw-bold mb-3">{{ __('referral.heading') }}</h1>
                <p class="text-secondary mb-4">{{ __('referral.lead') }}</p>

                <p class="small text-muted text-uppercase mb-2">{{ __('referral.your_code') }}</p>
                <div class="ref-code-box mb-4 user-select-all" id="refCode">{{ $codigoCupom }}</div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button type="button" class="btn btn-primary px-4" id="refCopy">{{ __('referral.copy') }}</button>
                    <a class="btn btn-success d-inline-flex align-items-center gap-2 px-4" href="{{ $waLink }}" target="_blank" rel="noopener noreferrer">
                        <span class="material-symbols-outlined" aria-hidden="true">chat</span>
                        {{ __('referral.share_whatsapp') }}
                    </a>
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#refIgModal">
                        <span class="material-symbols-outlined" aria-hidden="true">photo_camera</span>
                        {{ __('referral.share_instagram') }}
                    </button>
                </div>

                <p class="small text-muted mb-0">{{ __('referral.hero_hint') }}</p>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="card ref-side-card border shadow-sm flex-grow-1">
                <div class="card-body">
                    <h2 class="h6 fw-bold d-flex align-items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-primary" aria-hidden="true">insights</span>
                        {{ __('referral.card_how_title') }}
                    </h2>
                    <ol class="ps-3 small mb-0 text-secondary">
                        <li class="mb-2"><strong class="text-body">{{ __('referral.how_step1_title') }}</strong> — {{ __('referral.how_step1_body') }}</li>
                        <li class="mb-2"><strong class="text-body">{{ __('referral.how_step2_title') }}</strong> — {{ __('referral.how_step2_body') }}</li>
                        <li><strong class="text-body">{{ __('referral.how_step3_title') }}</strong> — {{ __('referral.how_step3_body') }}</li>
                    </ol>
                </div>
            </div>

            <div class="card ref-side-card border shadow-sm flex-grow-1">
                <div class="card-body">
                    <h2 class="h6 fw-bold d-flex align-items-center gap-2 mb-3">
                        <span class="material-symbols-outlined" style="color:#6a0392;" aria-hidden="true">redeem</span>
                        {{ __('referral.card_balance_title') }}
                    </h2>
                    <p class="small text-muted mb-1">{{ __('referral.balance_label') }}</p>
                    <p class="display-6 fw-bold text-primary mb-3">{{ $saldoFmt }}</p>
                    <p class="small text-secondary mb-3">{{ __('referral.min_withdraw_hint', ['min' => $minSaque]) }}</p>
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#refHistoryModal">{{ __('referral.btn_open_history') }}</button>
                </div>
            </div>
        </div>
    </div>

    <section class="mt-2 mb-5" aria-labelledby="ref-cond-heading">
        <h2 class="h5 fw-bold mb-3" id="ref-cond-heading">{{ __('referral.condiciones_heading') }}</h2>
        <div class="card border shadow-sm ref-side-card">
            <div class="card-body">
                <ul class="ref-cond-ul small text-secondary mb-0 ps-3">
                    @for ($ci = 1; $ci <= 8; $ci++)
                        <li>{{ __("referral.condition_item_{$ci}") }}</li>
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>
@endsection

@push('modals')
{{-- Fora do <main>: evita overflow/transform do shell cortar centrado do modal (backdrop fixo ao viewport). --}}
{{-- Histórico --}}
<div class="modal fade" id="refHistoryModal" tabindex="-1" aria-labelledby="refHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 mb-0" id="refHistoryModalLabel">{{ __('referral.history_title') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('referral.modal_close') }}"></button>
            </div>
            <div class="modal-body p-0 pt-3 px-3">
                @if ($movimentos->isEmpty())
                    <p class="text-muted py-4 mb-0">{{ __('referral.history_empty') }}</p>
                @else
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">{{ __('referral.col_date') }}</th>
                                    <th class="small">{{ __('referral.col_type') }}</th>
                                    <th class="small text-end">{{ __('referral.col_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movimentos as $m)
                                    <tr>
                                        <td class="small text-nowrap">{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                                        <td class="small">{{ $m->descricao ?: $m->tipo }}</td>
                                        <td class="small text-end">{{ number_format((float) $m->valor, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 pt-3">
                <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">{{ __('referral.modal_close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Instagram / redes: copiar texto --}}
<div class="modal fade" id="refIgModal" tabindex="-1" aria-labelledby="refIgModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header align-items-start border-0 pb-0 gap-3">
                <h2 class="modal-title h5 mb-0 pe-2 lh-base" id="refIgModalLabel">{{ __('referral.insta_modal_title') }}</h2>
                <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="{{ __('referral.modal_close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="small text-muted mb-3 mb-md-4">{{ __('referral.insta_modal_intro') }}</p>
                <pre class="small bg-body-secondary p-3 rounded-3 mb-0 font-monospace user-select-all overflow-auto mw-100" id="refIgCopyBlock"
                     style="max-height: 14rem; white-space: pre-wrap;">{{ $shareMsg }}</pre>
            </div>
            <div class="modal-footer flex-column gap-2 border-0 pt-3">
                <button type="button" class="btn btn-primary w-100" id="refIgCopyBtn">{{ __('referral.copy_full_message') }}</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('refCopy');
    var codeEl = document.getElementById('refCode');
    var errMsg = {{ json_encode(__('referral.copy_failed')) }};

    function copyWithExec(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        ta.style.left = '-9999px';
        ta.style.top = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) { /* ignore */ }
        document.body.removeChild(ta);
        return ok;
    }

    function copyTxt(text, btnEl, doneLbl, origLbl) {
        text = String(text || '').trim();
        if (!text) return;
        var prev = btnEl ? btnEl.textContent : '';
        var restore = function () {
            if (btnEl && origLbl !== undefined) btnEl.textContent = origLbl;
        };
        var onOk = function () {
            if (btnEl && doneLbl) btnEl.textContent = doneLbl;
            if (btnEl && doneLbl && origLbl !== undefined) {
                window.setTimeout(restore, 1800);
            }
        };

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(text).then(onOk).catch(function () {
                if (copyWithExec(text)) onOk();
                else { restore(); alert(errMsg); }
            });
            return;
        }

        if (copyWithExec(text)) onOk();
        else alert(errMsg);
    }

    if (btn && codeEl) btn.addEventListener('click', function () {
        var orig = btn.textContent;
        copyTxt((codeEl.textContent || '').trim(), btn, {{ json_encode(__('referral.copied')) }}, orig);
    });
    var igBtn = document.getElementById('refIgCopyBtn');
    var igBlock = document.getElementById('refIgCopyBlock');
    if (igBtn && igBlock) igBtn.addEventListener('click', function () {
        var orig = igBtn.textContent;
        copyTxt(igBlock.textContent || '', igBtn, {{ json_encode(__('referral.copied')) }}, orig);
    });
})();
</script>
@endpush
