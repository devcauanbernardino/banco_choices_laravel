<?php

namespace App\Http\Controllers;

use App\Models\FlashcardProgresso;
use App\Models\Materia;
use App\Models\Questao;
use App\Services\Flashcards\FlashcardContentGenerator;
use App\Services\Flashcards\FlashcardQueueBuilder;
use App\Support\FlashcardSession;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use App\Support\QuestionLocale;
use App\Support\Sm2Scheduler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardController extends Controller
{
    private FlashcardSession $sessao;

    public function __construct()
    {
        $this->sessao = new FlashcardSession;
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materias = $user->materiasUnicas();

        $resumoPorMateria = $materias->mapWithKeys(function (Materia $m) use ($user) {
            return [$m->id => FlashcardQueueBuilder::counts((int) $user->id, (int) $m->id)];
        });

        return view('flashcards.index', compact('materias', 'resumoPorMateria'));
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'materia' => 'required|integer|exists:materias,id',
            'novos_por_dia' => 'nullable|integer|min:0|max:200',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = (int) $request->input('materia');

        if (! $user->possuiMateria($materiaId)) {
            return response()->json(['error' => __('flashcards.err.unauthorized_subject')], 403);
        }

        $materia = Materia::query()->findOrFail($materiaId);
        $novosPorDia = (int) $request->input('novos_por_dia', FlashcardQueueBuilder::DEFAULT_NEW_CARDS_PER_DAY);

        $fila = FlashcardQueueBuilder::buildQueue((int) $user->id, $materiaId, $novosPorDia);
        $refs = array_merge($fila['due'], $fila['new']);

        if ($refs === []) {
            return response()->json(['error' => __('flashcards.err.nothing_to_review')], 422);
        }

        $this->sessao->init([
            'materia' => $materiaId,
            'materia_nome' => $materia->nome,
            'banco_questoes' => QuestionBankLocator::filenameFor($materiaId),
            'fila' => $refs,
            'atual' => 0,
            'revelado' => false,
            'resultados' => [],
        ]);

        return response()->json($this->cardPayload());
    }

    public function process(Request $request): JsonResponse
    {
        if (! $this->sessao->isActive()) {
            return response()->json(['error' => __('flashcards.err.session_expired')], 409);
        }

        $this->ensureAuthorized();

        if ($request->has('revelar')) {
            $this->sessao->set('revelado', true);

            return response()->json($this->cardPayload());
        }

        if ($request->has('avaliar')) {
            if (! $this->sessao->get('revelado')) {
                return response()->json(['error' => __('flashcards.err.reveal_first')], 409);
            }

            $request->validate([
                'avaliar' => 'required|in:dificil,medio,facil',
            ]);

            $this->registrarAvaliacao((string) $request->input('avaliar'));

            $atual = (int) $this->sessao->get('atual') + 1;
            $this->sessao->set('atual', $atual);
            $this->sessao->set('revelado', false);

            $fila = (array) ($this->sessao->get('fila') ?? []);
            if (! isset($fila[$atual])) {
                return response()->json($this->summaryPayload());
            }

            return response()->json($this->cardPayload());
        }

        return response()->json(['error' => __('flashcards.err.invalid_action')], 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function cardPayload(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $fila = (array) ($this->sessao->get('fila') ?? []);
        $atual = (int) ($this->sessao->get('atual') ?? 0);

        $banco = (string) ($this->sessao->get('banco_questoes') ?? '');
        $materiaId = (int) $this->sessao->get('materia');
        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        $overlayKey = (int) $fila[$atual]['overlay_key'];

        $qRaw = QuestionLocale::apply($lista[$overlayKey] ?? [], (string) app()->getLocale(), $banco);
        $questao = new Question($qRaw);
        $questaoId = (int) $fila[$atual]['questao_id'];
        $revelado = (bool) $this->sessao->get('revelado');

        try {
            $conteudo = FlashcardContentGenerator::getOrGenerate($questaoId, $questao, (string) app()->getLocale());
            $frente = $conteudo->frente;
            $verso = $conteudo->verso;
            $erroGeracao = null;
        } catch (\Throwable $e) {
            report($e);
            $frente = $questao->getPergunta();
            $verso = $questao->getFeedback();
            $erroGeracao = __('flashcards.err.ai_generation_failed');
        }

        $tema = Questao::query()->whereKey($questaoId)->value('tema');
        $progresso = FlashcardProgresso::query()
            ->where('usuario_id', $user->id)
            ->where('questao_id', $questaoId)
            ->first();

        return [
            'finished' => false,
            'materia_nome' => $this->sessao->get('materia_nome') ?? '',
            'tema' => $tema ?: null,
            'numero' => $atual + 1,
            'streak_dias' => $this->calcularStreakDias((int) $user->id),
            'intervalo_atual' => $progresso ? (int) $progresso->intervalo_dias : null,
            'frente' => $frente,
            'verso' => $revelado ? $verso : null,
            'erro_geracao' => $revelado ? $erroGeracao : null,
            'revelado' => $revelado,
            'atual' => $atual,
            'total' => count($fila),
        ];
    }

    private function calcularStreakDias(int $uid): int
    {
        $datas = DB::table('flashcard_progresso')
            ->where('usuario_id', $uid)
            ->whereNotNull('ultima_revisao_em')
            ->selectRaw('DISTINCT DATE(ultima_revisao_em) as data')
            ->orderByDesc('data')
            ->pluck('data')
            ->toArray();

        if (empty($datas)) {
            return 0;
        }

        $hoje = new \DateTime('today');
        $ultimaData = new \DateTime($datas[0]);

        if ($hoje->diff($ultimaData)->days > 1) {
            return 0;
        }

        $sequencia = 1;
        $dataComparacao = $ultimaData;
        foreach (array_slice($datas, 1) as $dataStr) {
            $dataAtual = new \DateTime($dataStr);
            if ($dataComparacao->diff($dataAtual)->days === 1) {
                $sequencia++;
                $dataComparacao = $dataAtual;
            } else {
                break;
            }
        }

        return $sequencia;
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryPayload(): array
    {
        $resultados = (array) ($this->sessao->get('resultados') ?? []);
        $materiaNome = (string) ($this->sessao->get('materia_nome') ?? '');

        $contagem = ['dificil' => 0, 'medio' => 0, 'facil' => 0];
        foreach ($resultados as $r) {
            $b = (string) ($r['avaliacao'] ?? '');
            if (isset($contagem[$b])) {
                $contagem[$b]++;
            }
        }

        $this->sessao->clear();

        return [
            'finished' => true,
            'materia_nome' => $materiaNome,
            'total' => count($resultados),
            'contagem' => $contagem,
        ];
    }

    private function registrarAvaliacao(string $avaliacao): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = (int) $this->sessao->get('materia');
        $fila = (array) ($this->sessao->get('fila') ?? []);
        $atual = (int) $this->sessao->get('atual');
        $questaoId = (int) $fila[$atual]['questao_id'];

        $progresso = FlashcardProgresso::firstOrNew(
            ['usuario_id' => $user->id, 'questao_id' => $questaoId],
            ['materia_id' => $materiaId, 'fator_facilidade' => Sm2Scheduler::DEFAULT_EASE_FACTOR]
        );

        $qualidade = Sm2Scheduler::buttonToQuality($avaliacao);
        $novo = Sm2Scheduler::next(
            (float) ($progresso->fator_facilidade ?? Sm2Scheduler::DEFAULT_EASE_FACTOR),
            (int) ($progresso->intervalo_dias ?? 0),
            (int) ($progresso->repeticoes ?? 0),
            $qualidade
        );

        $progresso->materia_id = $materiaId;
        $progresso->fator_facilidade = $novo['ease_factor'];
        $progresso->intervalo_dias = $novo['interval_days'];
        $progresso->repeticoes = $novo['repetitions'];
        $progresso->ultima_revisao_em = now();
        $progresso->proxima_revisao_em = now()->addDays($novo['interval_days']);
        $progresso->total_revisoes = (int) ($progresso->total_revisoes ?? 0) + 1;
        $progresso->save();

        $resultados = (array) ($this->sessao->get('resultados') ?? []);
        $resultados[] = ['questao_id' => $questaoId, 'avaliacao' => $avaliacao];
        $this->sessao->set('resultados', $resultados);
    }

    private function ensureAuthorized(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = $this->sessao->get('materia');

        if ($materiaId && is_numeric($materiaId) && ! $user->possuiMateria((int) $materiaId)) {
            $this->sessao->clear();
            abort(403);
        }
    }
}
