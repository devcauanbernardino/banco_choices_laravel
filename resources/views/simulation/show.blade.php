@extends('layouts.public')

@section('title', __('quiz.page_title'))

@section('body_attr')
 class="quiz-page"
@endsection

@push('styles')
<script>
    (function () {
        try {
            var stored = localStorage.getItem('bancochoices-theme');
            if (stored === 'dark' || stored === 'light') {
                document.documentElement.setAttribute('data-theme', stored);
                document.documentElement.setAttribute('data-bs-theme', stored);
                document.documentElement.style.colorScheme = stored;
            }
        } catch (e) {}
    })();
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
.quiz-page { font-family: 'Inter', system-ui, sans-serif; background: var(--app-bg); }
.quiz-page h1, .quiz-page h2, .quiz-page h3 { font-family: 'Poppins', system-ui, sans-serif; }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
@keyframes slideIn { from { opacity:0; transform:translateX(12px); } to { opacity:1; transform:none; } }

.qz-opt { display:flex; align-items:center; gap:12px; padding:13px 16px; border-radius:13px; border:1.5px solid var(--app-border); background:var(--app-surface); cursor:pointer; transition:border-color .18s ease, background .18s ease, box-shadow .18s ease; }
.qz-opt:hover { border-color:rgba(106,3,146,.45); }
.qz-opt.is-selected { border-color:rgba(106,3,146,.6); background:rgba(106,3,146,.08); }
.qz-opt.is-correct  { border-color:rgba(22,163,74,.55); background:rgba(22,163,74,.1); }
.qz-opt.is-wrong     { border-color:rgba(239,68,68,.5); background:rgba(239,68,68,.08); }
.qz-opt-letter { flex-shrink:0; width:28px; height:28px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.72rem; background:rgba(106,3,146,.08); color:#a855f7; border:1px solid rgba(106,3,146,.2); }
.qz-opt.is-correct .qz-opt-letter { background:rgba(22,163,74,.18); color:#22c55e; border-color:rgba(22,163,74,.35); }
.qz-opt.is-wrong .qz-opt-letter    { background:rgba(239,68,68,.15); color:#f87171; border-color:rgba(239,68,68,.3); }
.qz-opt.is-selected .qz-opt-letter { background:rgba(106,3,146,.18); color:#a855f7; border-color:rgba(106,3,146,.35); }
.qz-opt-text { font-size:.92rem; color:var(--app-text); line-height:1.5; }
.qz-opt input { position:absolute; opacity:0; pointer-events:none; }

.qz-map-cell { width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; border:1.5px solid var(--app-border); background:var(--app-surface); color:var(--app-muted); cursor:pointer; transition:all .18s ease; }
.qz-map-cell:hover { border-color:rgba(106,3,146,.5); color:#a855f7; }
.qz-map-cell.is-current { border-color:#6a0392; background:rgba(106,3,146,.14); color:#a855f7; box-shadow:0 0 0 3px rgba(106,3,146,.18); }
.qz-map-cell.qz-map-cell--respondida { border-color:rgba(106,3,146,.4); background:rgba(106,3,146,.12); color:#a855f7; }
.qz-map-cell.qz-map-cell--correta { border-color:rgba(22,163,74,.5); background:rgba(22,163,74,.15); color:#22c55e; }
.qz-map-cell.qz-map-cell--incorreta { border-color:rgba(239,68,68,.45); background:rgba(239,68,68,.13); color:#f87171; }

.qz-card { background:var(--app-surface); border:1px solid var(--app-border); }
.qz-btn-ghost { background:var(--app-surface); border:1.5px solid var(--app-border); color:var(--app-text); }
.qz-btn-ghost:hover { border-color:rgba(106,3,146,.4); color:#a855f7; }
</style>
@endpush

@section('content')
@php
    $opcoes = $questao->getOpcoes();
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    $respostaAtual = $respostas[$indiceAtual] ?? null;
    $textoPergunta = $questao->getPergunta();
    $isStudy = $modo === 'estudo';
    $hasFeedback = $isStudy && !empty($feedback);
    $modoLabel = $isStudy ? __('quiz.mode_study') : __('quiz.mode_exam');
    $isMulti = $questao->isMultiResposta();
    $selecionadasAtuais = $isMulti ? array_filter(explode(',', (string) $respostaAtual), fn ($v) => $v !== '') : [];
    $corretasMulti = $isMulti && $hasFeedback ? array_filter(explode(',', (string) ($feedback['resposta_correta'] ?? '')), fn ($v) => $v !== '') : [];
    $totalRespondidas = count(array_filter($mapaStatus, fn ($s) => $s !== 'pendente'));
    $totalCorretas = count(array_filter($mapaStatus, fn ($s) => $s === 'correta'));
    $pctAcertos = $totalRespondidas > 0 ? round(($totalCorretas / $totalRespondidas) * 100) : 0;
@endphp

<div style="display:flex; min-height:100svh; background:var(--app-bg);">

    {{-- HEADER --}}
    <div style="position:fixed; top:0; left:0; right:0; z-index:20; background:var(--app-surface); border-bottom:1px solid var(--app-border); height:58px; display:flex; align-items:center; padding:0 clamp(16px,3vw,28px); gap:16px;">
        <a href="{{ route('home') }}" style="display:inline-flex; align-items:center; gap:9px; text-decoration:none;">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="32" height="32" style="display:block; object-fit:contain;">
        </a>

        <div style="flex:1; display:flex; align-items:center; justify-content:center; gap:20px; flex-wrap:wrap;">
            @if ($tempoRestante !== null)
                @php
                    $__seg = max(0, (int) $tempoRestante);
                    $__timerFmt = sprintf('%02d:%02d:%02d', intdiv($__seg, 3600), intdiv($__seg % 3600, 60), $__seg % 60);
                @endphp
                <div style="display:flex; align-items:center; gap:8px; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); padding:5px 14px; border-radius:999px;">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem; color:#dc2626;">schedule</span>
                    <span style="font-size:.68rem; font-weight:600; color:#dc2626;">{{ __('quiz.timer_label') }}</span>
                    <span id="timerDisplay" style="font-family:'Poppins',sans-serif; font-weight:700; font-size:.9rem; color:#dc2626; letter-spacing:.04em;">{{ $__timerFmt }}</span>
                </div>
            @endif
            <span style="padding:5px 14px; border-radius:999px; background:rgba(106,3,146,.1); border:1px solid rgba(106,3,146,.22); font-size:.75rem; font-weight:700; color:#a855f7; letter-spacing:.06em; text-transform:uppercase;">{{ $modoLabel }}</span>
            <span style="font-size:.84rem; color:var(--app-muted); font-weight:500;">{{ $materiaNome }}</span>
        </div>

        <button type="button" class="js-theme-toggle-btn" aria-pressed="false" aria-label="{{ __('sidebar.theme_dark_aria') }}" title="{{ __('sidebar.appearance') }}"
                style="width:34px; height:34px; border-radius:9px; border:1px solid var(--app-border); background:var(--app-surface); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; color:var(--app-muted);">
            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">light_mode</span>
        </button>
    </div>

    {{-- MAIN CANVAS --}}
    <div style="flex:1; display:flex; gap:0; padding-top:58px; max-width:1280px; margin:0 auto; width:100%;">

        {{-- QUESTION COLUMN --}}
        <main style="flex:1; min-width:0; padding:clamp(20px,3vw,36px); display:flex; flex-direction:column; gap:20px; animation:fadeUp .5s ease both;">

            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <nav aria-label="{{ __('quiz.breadcrumb_aria') }}" style="display:flex; align-items:center; gap:6px; font-size:.8rem; color:var(--app-muted);">
                    <span>{{ $modoLabel }}</span>
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.9rem;">chevron_right</span>
                    <span style="color:#a855f7; font-weight:600;">{{ __('quiz.question_of', ['current' => $indiceAtual + 1, 'total' => $totalQuestoes]) }}</span>
                </nav>
                <span style="padding:4px 12px; border-radius:999px; background:rgba(106,3,146,.1); border:1px solid rgba(106,3,146,.22); color:#a855f7; font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">{{ $materiaNome }}</span>
            </div>

            <div style="height:5px; background:var(--app-border); border-radius:99px; overflow:hidden;">
                <div style="height:100%; width:{{ (($indiceAtual + 1) / max(1, $totalQuestoes)) * 100 }}%; background:linear-gradient(90deg,#8b1fb8,#c084fc); border-radius:99px; transition:width .6s cubic-bezier(.2,.9,.2,1);"></div>
            </div>

            @if (! empty($quiz_translation_overlay_missing))
                <div style="display:flex; align-items:flex-start; gap:10px; background:rgba(106,3,146,.06); border:1px solid rgba(106,3,146,.18); border-radius:12px; padding:12px 14px;">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem; color:#a855f7;">translate</span>
                    <p style="font-size:.8rem; color:var(--app-muted); line-height:1.55; margin:0;">{{ __('quiz.bank_original_language_notice') }}</p>
                </div>
            @endif

            <div class="qz-card" style="border-radius:20px; padding:clamp(20px,3vw,32px);">
                <h2 style="font-size:clamp(.95rem,1.5vw,1.08rem); font-weight:600; color:var(--app-text); line-height:1.65; margin-bottom:24px;">
                    {{ $textoPergunta }}
                </h2>

                @if (count($opcoes) === 0)
                    <div class="alert alert-warning mb-0" role="alert">{{ __('quiz.no_options') }}</div>
                @endif

                <form method="POST" action="{{ route('simulation.process') }}" id="quizForm">
                    @csrf
                    <input type="hidden" name="indice" value="{{ $indiceAtual }}">

                    @if ($isMulti)
                        <input type="hidden" name="resposta_multi_submit" value="1">
                        <p style="font-size:.8rem; color:var(--app-muted); margin:0 0 10px;">{{ __('quiz.multi_hint') }}</p>
                    @endif

                    <div role="{{ $isMulti ? 'group' : 'radiogroup' }}" aria-label="{{ __('quiz.options_aria') }}" style="display:flex; flex-direction:column; gap:10px;" id="quiz-options">
                        @foreach ($opcoes as $i => $opcao)
                            @php
                                $isSelected = $isMulti
                                    ? in_array((string) $i, $selecionadasAtuais, true)
                                    : (string) $respostaAtual === (string) $i;
                                $isCorrect = $hasFeedback && ($isMulti
                                    ? in_array((string) $i, $corretasMulti, true)
                                    : (string) ($feedback['resposta_correta'] ?? '') === (string) $i);
                                $isWrongPick = $hasFeedback && $isSelected && ! $isCorrect;
                            @endphp
                            <label class="qz-opt {{ $isSelected ? 'is-selected' : '' }} {{ $isCorrect ? 'is-correct' : '' }} {{ $isWrongPick ? 'is-wrong' : '' }}">
                                <input type="{{ $isMulti ? 'checkbox' : 'radio' }}"
                                       name="{{ $isMulti ? 'resposta[]' : 'resposta' }}" value="{{ $i }}"
                                       {{ $isSelected ? 'checked' : '' }}
                                       @if ($hasFeedback) disabled @endif>
                                <span class="qz-opt-letter" aria-hidden="true">{{ $letras[$i] ?? ($i + 1) }}</span>
                                <span class="qz-opt-text">{{ $opcao }}</span>
                                @if ($hasFeedback && $isCorrect)
                                    <span class="material-symbols-outlined" aria-hidden="true" style="margin-left:auto; color:#22c55e;">check_circle</span>
                                @elseif ($isWrongPick)
                                    <span class="material-symbols-outlined" aria-hidden="true" style="margin-left:auto; color:#f87171;">cancel</span>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    @if ($isMulti && ! $hasFeedback)
                        <div style="margin-top:14px;">
                            <button type="submit" name="resposta_multi_submit" value="1"
                                    style="padding:9px 18px; border-radius:10px; border:none; background:linear-gradient(135deg,#8b1fb8,#6a0392); color:#fff; font-weight:700; font-size:.84rem; cursor:pointer;">
                                {{ __('quiz.multi_confirm') }}
                            </button>
                        </div>
                    @endif

                    @if ($hasFeedback)
                        <div style="margin-top:20px; padding:16px 18px; border-radius:14px; background:{{ !empty($feedback['acertou']) ? 'rgba(22,163,74,.1)' : 'rgba(239,68,68,.08)' }}; border:1px solid {{ !empty($feedback['acertou']) ? 'rgba(22,163,74,.3)' : 'rgba(239,68,68,.3)' }};">
                            <div style="display:flex; align-items:center; gap:9px; margin-bottom:8px;">
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.3rem; color:{{ !empty($feedback['acertou']) ? '#22c55e' : '#f87171' }};">{{ !empty($feedback['acertou']) ? 'task_alt' : 'error' }}</span>
                                <strong style="font-size:.9rem; color:{{ !empty($feedback['acertou']) ? '#16a34a' : '#dc2626' }};">{{ !empty($feedback['acertou']) ? __('quiz.correct') : __('quiz.incorrect') }}</strong>
                            </div>
                            @if (!empty($feedback['feedback']))
                                <p style="font-size:.86rem; color:var(--app-text); line-height:1.65; margin:0;">{{ $feedback['feedback'] }}</p>
                            @endif

                            <div style="margin-top:14px;">
                                <button type="button" id="qzAiExplainBtn" style="display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:10px; border:1.5px solid rgba(139,31,184,.35); background:rgba(139,31,184,.06); color:#8b1fb8; font-size:.8rem; font-weight:700; cursor:pointer;">
                                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.05rem;">auto_awesome</span>
                                    {{ __('quiz.ai.explain_btn') }}
                                </button>
                                <div id="qzAiExplainBox" class="d-none" style="margin-top:10px; padding:13px 16px; border-radius:12px; background:rgba(139,31,184,.06); border:1px solid rgba(139,31,184,.2);">
                                    <p id="qzAiExplainText" style="font-size:.85rem; color:var(--app-text); line-height:1.65; margin:0;"></p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:20px;">
                        @if ($indiceAtual > 0)
                            <button type="submit" name="voltar" value="1" class="qz-btn-ghost" style="display:inline-flex; align-items:center; gap:8px; padding:11px 20px; border-radius:12px; font-size:.88rem; font-weight:600; cursor:pointer;">
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_back</span>
                                {{ __('quiz.prev') }}
                            </button>
                        @else
                            <span class="qz-btn-ghost" aria-hidden="true" style="display:inline-flex; align-items:center; gap:8px; padding:11px 20px; border-radius:12px; font-size:.88rem; font-weight:600; opacity:.45;">
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_back</span>
                                {{ __('quiz.prev') }}
                            </span>
                        @endif

                        <div style="flex:1; max-width:280px; display:flex; flex-direction:column; align-items:center; gap:5px;">
                            <div style="height:4px; width:100%; background:var(--app-border); border-radius:99px; overflow:hidden;">
                                <div style="height:100%; width:{{ (($indiceAtual + 1) / max(1, $totalQuestoes)) * 100 }}%; background:linear-gradient(90deg,#8b1fb8,#c084fc); border-radius:99px;"></div>
                            </div>
                            <span style="font-size:.72rem; color:var(--app-muted);">{{ __('quiz.exam_progress') }}</span>
                        </div>

                        @if ($indiceAtual < $totalQuestoes - 1)
                            <button type="submit" name="avancar" value="1"
                                    style="display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:12px; border:none; background:linear-gradient(135deg,#8b1fb8,#6a0392); color:#fff; font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; cursor:pointer; box-shadow:0 6px 18px rgba(106,3,146,.28);">
                                {{ __('quiz.next') }}
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">arrow_forward</span>
                            </button>
                        @else
                            <button type="submit" name="avancar" value="1"
                                    @if (count($opcoes) === 0) disabled aria-disabled="true" @endif
                                    style="display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:12px; border:none; background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; cursor:pointer; box-shadow:0 6px 18px rgba(22,163,74,.28);">
                                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">check_circle</span>
                                {{ __('quiz.finish') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </main>

        {{-- SIDEBAR --}}
        <aside style="width:260px; flex-shrink:0; padding:clamp(16px,2.5vw,28px) clamp(16px,2vw,20px); padding-top:clamp(20px,3vw,36px); display:flex; flex-direction:column; gap:16px; animation:slideIn .5s .1s ease both;">

            <div class="qz-card" style="border-radius:18px; padding:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <h3 style="font-size:.9rem; font-weight:700; color:var(--app-text); margin:0;">{{ __('quiz.map_title') }}</h3>
                    <span style="font-size:.78rem; font-weight:600; color:#a855f7;">{{ $indiceAtual + 1 }} / {{ $totalQuestoes }}</span>
                </div>

                <form method="POST" action="{{ route('simulation.process') }}" id="quizMapForm" style="display:grid; grid-template-columns:repeat(5,1fr); gap:6px; margin-bottom:16px;">
                    @csrf
                    @for ($i = 0; $i < $totalQuestoes; $i++)
                        @php
                            $st = $mapaStatus[$i] ?? 'pendente';
                            $isCurrent = $i === $indiceAtual;
                        @endphp
                        <button type="submit" name="ir" value="{{ $i }}"
                                class="qz-map-cell qz-map-cell--{{ $st }} {{ $isCurrent ? 'is-current' : '' }}"
                                aria-label="{{ __('quiz.map_goto', ['n' => $i + 1]) }}"
                                @if ($isCurrent) aria-current="true" @endif>
                            {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}
                        </button>
                    @endfor
                </form>

                <div style="display:flex; flex-direction:column; gap:6px;">
                    @if ($isStudy)
                        <div style="display:flex; align-items:center; gap:7px; font-size:.74rem; color:var(--app-muted);">
                            <span style="width:10px; height:10px; border-radius:3px; background:rgba(22,163,74,.18); border:1px solid rgba(22,163,74,.4); flex-shrink:0;"></span>
                            {{ __('quiz.legend_correct') }}
                        </div>
                        <div style="display:flex; align-items:center; gap:7px; font-size:.74rem; color:var(--app-muted);">
                            <span style="width:10px; height:10px; border-radius:3px; background:rgba(239,68,68,.13); border:1px solid rgba(239,68,68,.4); flex-shrink:0;"></span>
                            {{ __('quiz.legend_wrong') }}
                        </div>
                    @else
                        <div style="display:flex; align-items:center; gap:7px; font-size:.74rem; color:var(--app-muted);">
                            <span style="width:10px; height:10px; border-radius:3px; background:rgba(106,3,146,.15); border:1px solid rgba(106,3,146,.4); flex-shrink:0;"></span>
                            {{ __('quiz.legend_answered') }}
                        </div>
                    @endif
                    <div style="display:flex; align-items:center; gap:7px; font-size:.74rem; color:var(--app-muted);">
                        <span style="width:10px; height:10px; border-radius:3px; background:var(--app-surface); border:1.5px solid var(--app-border); flex-shrink:0;"></span>
                        {{ __('quiz.legend_pending') }}
                    </div>
                    <div style="display:flex; align-items:center; gap:7px; font-size:.74rem; color:var(--app-muted);">
                        <span style="width:10px; height:10px; border-radius:3px; background:rgba(106,3,146,.12); border:1px solid #6a0392; flex-shrink:0;"></span>
                        {{ __('quiz.legend_current') }}
                    </div>
                </div>
            </div>

            @if ($isStudy && $totalRespondidas > 0)
                <div class="qz-card" style="border-radius:18px; padding:18px;">
                    <h3 style="font-size:.85rem; font-weight:700; color:var(--app-text); margin:0 0 12px;">{{ __('quiz.current_progress_title') }}</h3>
                    <div style="display:flex; justify-content:space-between; font-size:.8rem; color:var(--app-muted); margin-bottom:8px;">
                        <span>{{ __('quiz.stat_hits') }}</span>
                        <span style="font-weight:700; color:#22c55e;">{{ $totalCorretas }} / {{ $totalRespondidas }}</span>
                    </div>
                    <div style="height:6px; background:var(--app-border); border-radius:99px; overflow:hidden; margin-bottom:10px;">
                        <div style="height:100%; width:{{ $pctAcertos }}%; background:linear-gradient(90deg,#22c55e,#16a34a); border-radius:99px;"></div>
                    </div>
                    <p style="font-size:.75rem; color:var(--app-muted); line-height:1.5; margin:0;">{{ sprintf(__('quiz.progress_done'), $pctAcertos) }} {{ __('quiz.current_progress_copy') }}</p>
                </div>
            @endif

            <a href="{{ route('result.show') }}"
               style="width:100%; padding:12px; border-radius:12px; border:1.5px solid rgba(239,68,68,.3); background:transparent; color:#f87171; font-size:.85rem; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:7px; text-decoration:none;">
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">logout</span>
                {{ __('quiz.end_early') }}
            </a>
        </aside>
    </div>
</div>

@auth
    @include('partials.ai-chat-widget')
@endauth
@endsection

@push('scripts')
<script src="{{ asset('assets/js/theme.js') }}" defer></script>
<script>
    (function () {
        var isStudy = {{ $isStudy ? 'true' : 'false' }};
        var alreadyFeedback = {{ $hasFeedback ? 'true' : 'false' }};
        var isMulti = {{ $isMulti ? 'true' : 'false' }};
        var form = document.getElementById('quizForm');

        document.querySelectorAll('.qz-opt').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                var input = opt.querySelector('input[type="radio"], input[type="checkbox"]');
                if (!input || input.disabled) return;

                if (isMulti) {
                    input.checked = !input.checked;
                    opt.classList.toggle('is-selected', input.checked);
                    return;
                }

                document.querySelectorAll('.qz-opt').forEach(function (o) { o.classList.remove('is-selected'); });
                opt.classList.add('is-selected');
                input.checked = true;

                if (isStudy && !alreadyFeedback && form) {
                    e.preventDefault();
                    setTimeout(function () { form.submit(); }, 120);
                }
            });
        });
    })();

    @if ($tempoRestante !== null)
    (function () {
        function pad2(n) { n = Math.floor(n); return (n < 10 ? '0' : '') + n; }
        function formatRemaining(sec) {
            sec = Math.max(0, sec);
            var h = Math.floor(sec / 3600);
            var m = Math.floor((sec % 3600) / 60);
            var s = sec % 60;
            return pad2(h) + ':' + pad2(m) + ':' + pad2(s);
        }
        var remaining = {{ (int) $tempoRestante }};
        var display = document.getElementById('timerDisplay');
        var quizForm = document.getElementById('quizForm');
        if (!display || !quizForm) return;

        display.textContent = formatRemaining(remaining);

        var interval = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                var hid = document.createElement('input');
                hid.type = 'hidden';
                hid.name = 'timeout';
                hid.value = '1';
                quizForm.appendChild(hid);
                quizForm.submit();
                return;
            }
            display.textContent = formatRemaining(remaining);
        }, 1000);
    })();
    @endif

    (function () {
        var btn = document.getElementById('qzAiExplainBtn');
        if (!btn) return;
        var box = document.getElementById('qzAiExplainBox');
        var text = document.getElementById('qzAiExplainText');
        var originalHtml = btn.innerHTML;
        var csrf = document.querySelector('meta[name="csrf-token"]');

        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.style.opacity = '.6';
            btn.style.cursor = 'default';
            btn.innerHTML = '{{ __('quiz.ai.loading') }}';

            fetch('{{ route('simulation.explainAi') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                },
            }).then(function (r) {
                return r.json().then(function (body) { return { ok: r.ok, body: body }; });
            }).then(function (res) {
                if (!res.ok) {
                    throw new Error((res.body && res.body.error) || '{{ __('quiz.ai.error') }}');
                }
                text.textContent = res.body.explicacao;
                box.classList.remove('d-none');
                btn.remove();
            }).catch(function (err) {
                text.textContent = err.message;
                box.classList.remove('d-none');
                btn.disabled = false;
                btn.style.opacity = '';
                btn.style.cursor = 'pointer';
                btn.innerHTML = originalHtml;
            });
        });
    })();
</script>
@endpush
