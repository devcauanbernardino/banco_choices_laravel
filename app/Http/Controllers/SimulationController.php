<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Services\Questions\QuestionExamBuilder;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use App\Support\QuestionLocale;
use App\Support\SimulationSession;
use App\Support\SimulationTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    private SimulationSession $sim;

    public function __construct()
    {
        $this->sim = new SimulationSession;
    }

    public function create(Request $request)
    {
        $request->validate([
            'materia' => 'required|integer|exists:materias,id',
            'catedra_id' => 'nullable|integer|exists:catedras,id',
            'quantidade' => 'required|integer|min:1|max:200',
            'modo' => 'required|in:estudo,exame',
            'parcial' => 'nullable|array',
            'parcial.*' => 'nullable|string|max:16',
            'tema' => 'nullable|array',
            'tema.*' => 'nullable|string|max:190',
        ]);

        $user = Auth::user();
        $materiaId = (int) $request->input('materia');

        if (! $user->possuiMateria($materiaId)) {
            return redirect()->route('questionbank')->with('error', __('bank.err.unauthorized_subject'));
        }

        /** @var Materia $materia */
        $materia = Materia::query()->withCount('catedras')->findOrFail($materiaId);

        $catIdRaw = $request->input('catedra_id');
        $catId = ($catIdRaw !== null && $catIdRaw !== '') ? (int) $catIdRaw : null;
        if ((int) $materia->catedras_count > 0) {
            if ($catId === null || $catId <= 0) {
                return redirect()->route('questionbank')->with('error', __('bank.err.catedra_required'));
            }
            $ok = $materia->catedras()->whereKey($catId)->exists();
            if (! $ok) {
                return redirect()->route('questionbank')->with('error', __('bank.err.catedra_invalid'));
            }
        }

        $bancoQuestoes = QuestionBankLocator::filenameFor($materiaId);
        $parciais = QuestionExamBuilder::normalizedFilterTokens((array) $request->input('parcial', []));
        $temas = QuestionExamBuilder::normalizedFilterTokens((array) $request->input('tema', []));

        $filterErrKey = $this->questionFilterViolationKey($materiaId, $catId, $parciais, $temas);
        if ($filterErrKey !== null) {
            return redirect()->route('questionbank')->with('error', __($filterErrKey));
        }

        $parciais = array_values(array_unique(array_map(static function ($p): string {
            $t = trim((string) $p);

            return strcasecmp($t, 'final') === 0 ? 'final' : $t;
        }, $parciais)));

        $questoes = QuestionExamBuilder::buildPack(
            $materiaId,
            $catId,
            $parciais,
            $temas,
            (int) $request->input('quantidade'),
            false,
        );

        if (empty($questoes)) {
            return redirect()->route('questionbank')->with('error', __('bank.err.no_questions_filters'));
        }

        $data = [
            'materia' => $materiaId,
            'materia_nome' => $materia->nome,
            'catedra_id' => $catId,
            'banco_questoes' => $bancoQuestoes,
            'modo' => $request->input('modo'),
            'questoes' => $questoes,
            'atual' => 0,
            'respostas' => [],
            'feedback' => [],
        ];

        if ($request->input('modo') === 'exame') {
            $data['inicio'] = time();
            $data['tempo_total'] = SimulationTimer::DEFAULT_SECONDS;
        }

        $this->sim->init($data);

        return redirect()->route('simulation.show');
    }

    /**
     * @param  list<string>  $parciaisSubmitted
     * @param  list<string>  $temasSubmitted
     */
    private function questionFilterViolationKey(int $materiaId, ?int $catedraId, array $parciaisSubmitted, array $temasSubmitted): ?string
    {
        $allowedParc = QuestionExamBuilder::parciaisDisponiveis($materiaId, $catedraId);
        $hayFinal = QuestionExamBuilder::hayFinalPool($materiaId, $catedraId);

        foreach ($parciaisSubmitted as $p) {
            $pTrim = trim((string) $p);
            if ($pTrim === '') {
                continue;
            }

            $isFinalToken = strtolower($pTrim) === 'final';

            if ($isFinalToken) {
                if (! $hayFinal['hay']) {
                    return 'bank.err.invalid_filters_parciais';
                }

                continue;
            }

            $ok = false;
            foreach ($allowedParc as $a) {
                if (strcasecmp(trim((string) $a), $pTrim) === 0) {
                    $ok = true;
                    break;
                }
            }
            if (! $ok) {
                return 'bank.err.invalid_filters_parciais';
            }
        }

        if ($temasSubmitted !== []) {
            $allowedTemas = QuestionExamBuilder::temasDisponiveis($materiaId, $catedraId);
            foreach ($temasSubmitted as $tRaw) {
                $tStr = trim((string) $tRaw);
                if ($tStr === '') {
                    continue;
                }

                $okTem = false;
                foreach ($allowedTemas as $a) {
                    if (trim((string) $a) === $tStr) {
                        $okTem = true;
                        break;
                    }
                }
                if (! $okTem) {
                    return 'bank.err.invalid_filters_temas';
                }
            }
        }

        return null;
    }

    public function show()
    {
        if (! $this->sim->isActive()) {
            return redirect()->route('dashboard');
        }

        $this->ensureAuthorized();

        $questoes = (array) ($this->sim->get('questoes') ?? []);
        $indiceAtual = (int) ($this->sim->get('atual') ?? 0);

        if (! isset($questoes[$indiceAtual])) {
            $indiceAtual = 0;
            $this->sim->set('atual', 0);
        }

        $banco = (string) ($this->sim->get('banco_questoes') ?? '');
        $qRaw = QuestionLocale::apply($questoes[$indiceAtual], (string) app()->getLocale(), $banco);
        $questao = new Question($qRaw);

        $tempoRestante = null;
        if ($this->sim->get('modo') === 'exame' && $this->sim->get('inicio') !== null) {
            $total = (int) ($this->sim->get('tempo_total') ?? SimulationTimer::DEFAULT_SECONDS);
            $tempoRestante = SimulationTimer::remainingSeconds((int) $this->sim->get('inicio'), $total);
            if (SimulationTimer::isExpired((int) $this->sim->get('inicio'), $total)) {
                return redirect()->route('result.show');
            }
        }

        $respostas = (array) ($this->sim->get('respostas') ?? []);
        $feedbacksAll = (array) ($this->sim->get('feedback') ?? []);
        $modo = (string) ($this->sim->get('modo') ?? 'estudo');

        $mapaStatus = [];
        for ($i = 0, $n = count($questoes); $i < $n; $i++) {
            $isAnswered = array_key_exists($i, $respostas) && $respostas[$i] !== null && $respostas[$i] !== '';
            $status = 'pendente';
            if ($isAnswered) {
                if ($modo === 'estudo' && isset($feedbacksAll[$i]['acertou'])) {
                    $status = ! empty($feedbacksAll[$i]['acertou']) ? 'correta' : 'incorreta';
                } else {
                    $status = 'respondida';
                }
            }
            $mapaStatus[$i] = $status;
        }

        $viewData = [
            'questao' => $questao,
            'indiceAtual' => $indiceAtual,
            'totalQuestoes' => count($questoes),
            'respostas' => $respostas,
            'modo' => $modo,
            'feedback' => $feedbacksAll[$indiceAtual] ?? null,
            'tempoRestante' => $tempoRestante,
            'materiaNome' => $this->sim->get('materia_nome') ?? '',
            'mapaStatus' => $mapaStatus,
            'quiz_translation_overlay_missing' => ! QuestionLocale::hasTranslationOverlay((string) app()->getLocale(), $banco),
        ];

        return view('simulation.show', $viewData);
    }

    public function process(Request $request)
    {
        if (! $this->sim->isActive()) {
            return redirect()->route('home');
        }

        $this->ensureAuthorized();

        if ($request->has('timeout')) {
            return redirect()->route('result.show');
        }

        if ($request->has('resposta')) {
            $this->saveAnswer($request->input('resposta'));
        }

        if ($request->has('ir')) {
            $this->sim->set('atual', (int) $request->input('ir'));

            return redirect()->route('simulation.show');
        }

        if ($request->has('avancar')) {
            $currentIndex = (int) $this->sim->get('atual');
            $totalQuestoes = count((array) ($this->sim->get('questoes') ?? []));
            $next = $currentIndex + 1;

            if ($next >= $totalQuestoes) {
                return redirect()->route('result.show');
            }

            $this->sim->set('atual', $next);

            return redirect()->route('simulation.show');
        }

        if ($request->has('voltar')) {
            $current = (int) $this->sim->get('atual');
            $this->sim->set('atual', max(0, $current - 1));

            return redirect()->route('simulation.show');
        }

        return redirect()->route('simulation.show');
    }

    private function saveAnswer(string|int $userAnswer): void
    {
        $currentIndex = (int) $this->sim->get('atual');
        $questoes = (array) ($this->sim->get('questoes') ?? []);
        $modo = (string) ($this->sim->get('modo') ?? 'estudo');
        $answerStr = is_int($userAnswer) ? (string) $userAnswer : $userAnswer;

        $respostas = (array) ($this->sim->get('respostas') ?? []);
        $respostas[$currentIndex] = $answerStr;
        $this->sim->set('respostas', $respostas);

        if ($modo === 'estudo' && isset($questoes[$currentIndex])) {
            $banco = (string) ($this->sim->get('banco_questoes') ?? '');
            $qRaw = QuestionLocale::apply($questoes[$currentIndex], (string) app()->getLocale(), $banco);
            $questao = new Question($qRaw);

            $feedbacks = (array) ($this->sim->get('feedback') ?? []);
            $feedbacks[$currentIndex] = [
                'acertou' => $questao->isCorrect($answerStr),
                'resposta_usuario' => $answerStr,
                'resposta_correta' => $questao->getCorrectAnswer(),
                'feedback' => $questao->getFeedback(),
                'parcial' => $questoes[$currentIndex]['_parcial'] ?? null,
            ];
            $this->sim->set('feedback', $feedbacks);
        }
    }

    private function ensureAuthorized(): void
    {
        $user = Auth::user();
        $materiaId = $this->sim->get('materia');

        if ($materiaId && is_numeric($materiaId) && ! $user->possuiMateria((int) $materiaId)) {
            $this->sim->clear();
            abort(403);
        }
    }
}
