@extends('layouts.result-standalone')

@section('title', __('result.page_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<style>
.sim-result2 { font-family: 'Inter', system-ui, sans-serif; }
.sim-result2 h1, .sim-result2 h2 { font-family: 'Poppins', system-ui, sans-serif; }
@keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }
@keyframes scaleIn { from { opacity:0; transform:scale(.7); } to { opacity:1; transform:scale(1); } }

.sim-result2-card { background:var(--app-surface); border:1px solid var(--app-border); }
.sim-result2-chip { background:var(--app-surface-2); color:var(--app-text); }
.sim-result2-stat-row { background:var(--app-surface-2); }
.sim-result2-table thead tr { background:var(--app-surface-2); }
.sim-result2-table tbody tr { border-bottom:1px solid var(--app-border); }
.sim-result2-table tbody tr:hover { background:var(--app-surface-2); }
</style>
@endpush

@section('content')
@php
    $erros = max(0, $total - $acertos);
    $aprovado = $porcentagem >= 70;
    $tempoFmt = $tempoSegundos ? gmdate('H:i:s', $tempoSegundos) : null;
    $xpGanho = (int) max(15, min(999, round($acertos * 12 + ($total > 0 ? ($acertos / $total) : 0) * 80)));
    $circunf = 251;
    $scoreOffset = $circunf - $circunf * min(100, max(0, $porcentagem)) / 100;
    $modoLabel = $modo === 'estudo' ? __('quiz.mode_study') : __('quiz.mode_exam');
@endphp

<div class="sim-result2">
    <div style="max-width:860px; margin:0 auto; width:100%; padding:clamp(8px,2vw,16px) clamp(16px,4vw,28px) clamp(28px,5vw,52px);">

        {{-- Hero --}}
        <div style="text-align:center; animation:fadeUp .6s ease both; margin-bottom:32px;">
            <span style="display:inline-block; padding:5px 14px; border-radius:999px; background:rgba(106,3,146,.1); border:1px solid rgba(106,3,146,.25); color:#a855f7; font-size:.68rem; font-weight:700; letter-spacing:.13em; text-transform:uppercase; margin-bottom:16px;">{{ __('result.eyebrow_done') }}</span>
            <h1 style="font-size:clamp(1.6rem,3vw,2.2rem); font-weight:800; color:var(--app-text); letter-spacing:-.03em; margin-bottom:10px;">{{ __('result.standalone_heading') }}</h1>
            <div style="display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap;">
                @if ($materiaNome)
                    <span class="sim-result2-chip" style="padding:4px 14px; border-radius:999px; font-size:.82rem; font-weight:600;">{{ $materiaNome }}</span>
                    <span style="color:var(--app-muted); font-size:.8rem;">&middot;</span>
                @endif
                @if ($tempoFmt)
                    <span class="sim-result2-chip" style="padding:4px 14px; border-radius:999px; font-size:.82rem; font-weight:600;">{{ $tempoFmt }}</span>
                    <span style="color:var(--app-muted); font-size:.8rem;">&middot;</span>
                @endif
                <span class="sim-result2-chip" style="padding:4px 14px; border-radius:999px; font-size:.82rem; font-weight:600;">{{ $modoLabel }}</span>
            </div>
        </div>

        {{-- Score card --}}
        <div class="sim-result2-card" style="border-radius:22px; padding:clamp(24px,5vw,44px); margin-bottom:24px; display:flex; align-items:center; justify-content:space-around; flex-wrap:wrap; gap:28px; animation:fadeUp .6s .08s ease both;">

            <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                <div style="position:relative; width:140px; height:140px; animation:scaleIn .7s .2s ease both;">
                    <svg width="140" height="140" viewBox="0 0 90 90" style="transform:rotate(-90deg);">
                        <circle cx="45" cy="45" r="40" fill="none" stroke="var(--app-border)" stroke-width="8"/>
                        <circle cx="45" cy="45" r="40" fill="none" stroke="url(#scoreGrad)" stroke-width="8" stroke-linecap="round"
                                stroke-dasharray="251" stroke-dashoffset="{{ $scoreOffset }}"
                                style="transition:stroke-dashoffset 1.2s cubic-bezier(.2,.9,.2,1);"/>
                        <defs>
                            <linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#8b1fb8"/>
                                <stop offset="100%" stop-color="#c084fc"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                        <span style="font-family:'Poppins',sans-serif; font-weight:800; font-size:2rem; line-height:1; background:linear-gradient(135deg,#8b1fb8,#6a0392); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">{{ $porcentagem }}%</span>
                        <span style="font-size:.72rem; color:var(--app-muted); font-weight:500; margin-top:3px;">{{ __('result.correct') }}</span>
                    </div>
                </div>
                <span style="display:inline-block; padding:6px 18px; border-radius:999px; background:{{ $aprovado ? 'rgba(22,163,74,.14)' : 'rgba(239,68,68,.12)' }}; border:1px solid {{ $aprovado ? 'rgba(22,163,74,.35)' : 'rgba(239,68,68,.32)' }}; color:{{ $aprovado ? '#22c55e' : '#f87171' }}; font-family:'Poppins',sans-serif; font-weight:700; font-size:.85rem;">
                    {{ $aprovado ? __('result.approved') : __('result.failed') }}
                </span>
            </div>

            <div style="display:flex; flex-direction:column; gap:14px; min-width:200px;">
                <div class="sim-result2-stat-row" style="display:flex; align-items:center; justify-content:space-between; gap:20px; padding:12px 16px; border-radius:12px;">
                    <span style="font-size:.85rem; color:var(--app-muted);">{{ __('dashboard.stat.questions_answered') }}</span>
                    <span style="font-family:'Poppins',sans-serif; font-weight:700; font-size:.95rem; color:var(--app-text);">{{ $total }}</span>
                </div>
                <div class="sim-result2-stat-row" style="display:flex; align-items:center; justify-content:space-between; gap:20px; padding:12px 16px; border-radius:12px;">
                    <span style="font-size:.85rem; color:var(--app-muted);">{{ __('quiz.stat_hits') }}</span>
                    <span style="font-family:'Poppins',sans-serif; font-weight:700; font-size:.95rem; color:#22c55e;">{{ $acertos }}</span>
                </div>
                <div class="sim-result2-stat-row" style="display:flex; align-items:center; justify-content:space-between; gap:20px; padding:12px 16px; border-radius:12px;">
                    <span style="font-size:.85rem; color:var(--app-muted);">{{ __('result.stat_wrong') }}</span>
                    <span style="font-family:'Poppins',sans-serif; font-weight:700; font-size:.95rem; color:#f87171;">{{ $erros }}</span>
                </div>
                @if ($tempoFmt)
                    <div class="sim-result2-stat-row" style="display:flex; align-items:center; justify-content:space-between; gap:20px; padding:12px 16px; border-radius:12px;">
                        <span style="font-size:.85rem; color:var(--app-muted);">{{ __('result.stat_time') }}</span>
                        <span style="font-family:'Poppins',sans-serif; font-weight:700; font-size:.95rem; color:var(--app-text);">{{ $tempoFmt }}</span>
                    </div>
                @endif
            </div>

            <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:24px 32px; background:linear-gradient(135deg,rgba(139,31,184,.14),rgba(192,132,252,.1)); border:1px solid rgba(139,31,184,.25); border-radius:18px;">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:2.2rem; color:#c084fc; animation:scaleIn .5s .5s ease both;">military_tech</span>
                <div style="font-family:'Poppins',sans-serif; font-weight:800; font-size:1.8rem; background:linear-gradient(135deg,#8b1fb8,#c084fc); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; line-height:1;">+{{ $xpGanho }}</div>
                <span style="font-size:.8rem; font-weight:700; color:#a855f7; letter-spacing:.06em; text-transform:uppercase;">{{ __('result.xp_label') }}</span>
            </div>
        </div>

        {{-- Details table --}}
        <div class="sim-result2-card" style="border-radius:20px; overflow:hidden; margin-bottom:24px; animation:fadeUp .6s .16s ease both;">
            <div style="padding:18px 24px; border-bottom:1px solid var(--app-border); display:flex; align-items:center; gap:10px;">
                <h2 style="font-size:.95rem; font-weight:700; color:var(--app-text); flex:1; margin:0;">{{ __('result.details_title') }}</h2>
                <span style="font-size:.78rem; color:var(--app-muted);">{{ count($detalhes) }}</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="sim-result2-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="padding:11px 16px; text-align:left; font-size:.72rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.08em; white-space:nowrap;">#</th>
                            <th style="padding:11px 16px; text-align:left; font-size:.72rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.08em;">{{ __('result.th_question') }}</th>
                            <th style="padding:11px 16px; text-align:center; font-size:.72rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.08em; white-space:nowrap;">{{ __('result.th_your_answer') }}</th>
                            <th style="padding:11px 16px; text-align:center; font-size:.72rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.08em; white-space:nowrap;">{{ __('result.th_correct') }}</th>
                            <th style="padding:11px 16px; text-align:center; font-size:.72rem; font-weight:700; color:var(--app-muted); text-transform:uppercase; letter-spacing:.08em;">{{ __('result.th_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalhes as $i => $d)
                            <tr>
                                <td style="padding:13px 16px; font-weight:700; color:var(--app-muted); font-size:.82rem; vertical-align:top;">{{ $i + 1 }}</td>
                                <td style="padding:13px 16px; font-size:.84rem; color:var(--app-text); max-width:360px; vertical-align:top;">{!! nl2br(e($d['pergunta'] ?? '')) !!}</td>
                                <td style="padding:13px 16px; text-align:center; vertical-align:top;"><span style="font-weight:800; font-size:.9rem; color:var(--app-text); background:var(--app-surface-2); padding:3px 10px; border-radius:7px;">{{ $d['resposta_usuario'] ?? '—' }}</span></td>
                                <td style="padding:13px 16px; text-align:center; vertical-align:top;"><span style="font-weight:800; font-size:.9rem; color:#22c55e; background:rgba(22,163,74,.14); padding:3px 10px; border-radius:7px;">{{ $d['resposta_correta'] ?? '—' }}</span></td>
                                <td style="padding:13px 16px; text-align:center; vertical-align:top;">
                                    @if (!empty($d['acertou']))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:999px; background:rgba(22,163,74,.14); border:1px solid rgba(22,163,74,.32); color:#22c55e; font-size:.72rem; font-weight:700;">
                                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.95rem;">check_circle</span>
                                            {{ __('quiz.correct') }}
                                        </span>
                                    @else
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:999px; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#f87171; font-size:.72rem; font-weight:700;">
                                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.95rem;">cancel</span>
                                            {{ __('quiz.incorrect') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @if (!empty($d['feedback']))
                                <tr>
                                    <td></td>
                                    <td colspan="4" style="padding:0 16px 14px; font-size:.78rem; color:var(--app-muted); font-style:italic;">{!! nl2br(e($d['feedback'])) !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex; justify-content:center; gap:14px; flex-wrap:wrap; animation:fadeUp .6s .22s ease both;">
            <a href="{{ route('questionbank') }}" style="display:inline-flex; align-items:center; gap:9px; padding:13px 26px; border-radius:12px; background:linear-gradient(135deg,#8b1fb8,#6a0392); color:#fff; font-family:'Poppins',sans-serif; font-weight:700; font-size:.92rem; text-decoration:none; box-shadow:0 6px 20px rgba(106,3,146,.28);">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">replay</span>
                {{ __('result.new_quiz') }}
            </a>
        </div>
    </div>
</div>
@endsection
