<?php

namespace App\Http\Controllers;

use App\Models\FlashcardProgresso;
use App\Models\Materia;
use App\Services\Flashcards\FlashcardQueueBuilder;
use App\Support\FlashcardBankLocator;
use App\Support\FlashcardLocale;
use App\Support\FlashcardSession;
use App\Support\MateriaLocale;
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
            $materiaId = (int) $m->id;

            return [$materiaId => array_merge(
                FlashcardQueueBuilder::counts((int) $user->id, $materiaId),
                ['mastery' => $this->masteryBreakdown((int) $user->id, $materiaId)],
                ['temas' => FlashcardBankLocator::temasDisponiveis($materiaId)],
            )];
        });

        $streak = $this->calcularStreakDias((int) $user->id);
        $revisadosHoje = $this->revisadosHoje((int) $user->id);
        $ultimos7dias = $this->ultimos7Dias((int) $user->id);

        return view('flashcards.index', compact('materias', 'resumoPorMateria', 'streak', 'revisadosHoje', 'ultimos7dias'));
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'materia' => 'required|integer|exists:materias,id',
            'novos_por_dia' => 'nullable|integer|min:0|max:200',
            'modo' => 'nullable|in:revisao,livre',
            'temas' => 'nullable|array',
            'temas.*' => 'string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = (int) $request->input('materia');

        if (! $user->possuiMateria($materiaId)) {
            return response()->json(['error' => __('flashcards.err.unauthorized_subject')], 403);
        }

        $materia = Materia::query()->findOrFail($materiaId);
        $modo = $request->input('modo') === 'livre' ? 'livre' : 'revisao';
        $temas = array_values(array_filter(
            (array) $request->input('temas', []),
            fn ($t) => is_string($t) && $t !== ''
        ));

        if ($modo === 'livre') {
            $refs = FlashcardQueueBuilder::buildFreeBrowseQueue($materiaId, $temas);
            if ($refs === []) {
                return response()->json(['error' => __('flashcards.err.nothing_to_review')], 422);
            }
        } else {
            $novosPorDia = (int) $request->input('novos_por_dia', FlashcardQueueBuilder::DEFAULT_NEW_CARDS_PER_DAY);
            $fila = FlashcardQueueBuilder::buildQueue((int) $user->id, $materiaId, $novosPorDia, $temas);
            $refs = array_merge($fila['due'], $fila['new']);

            if ($refs === []) {
                // Nada devido/novo agora: em vez de bloquear, libera uma revisão livre de todo o baralho (respeitando o filtro de tema).
                $refs = FlashcardQueueBuilder::buildFreeBrowseQueue($materiaId, $temas);
                if ($refs === []) {
                    return response()->json(['error' => __('flashcards.err.nothing_to_review')], 422);
                }
            }
        }

        $this->sessao->init([
            'materia' => $materiaId,
            'materia_nome' => MateriaLocale::nome($materia),
            'fila' => $refs,
            'atual' => 0,
            'revelado' => false,
            'resultados' => [],
            'modo' => $modo,
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

        if ($request->has('navegar')) {
            $request->validate([
                'navegar' => 'required|in:proximo,anterior',
            ]);

            $fila = (array) ($this->sessao->get('fila') ?? []);
            $atual = (int) $this->sessao->get('atual');
            $novoAtual = $request->input('navegar') === 'proximo' ? $atual + 1 : $atual - 1;
            $novoAtual = max(0, min($novoAtual, count($fila) - 1));

            $this->sessao->set('atual', $novoAtual);
            $this->sessao->set('revelado', false);

            return response()->json($this->cardPayload());
        }

        if ($request->has('avaliar')) {
            if ($this->sessao->get('modo') !== 'revisao') {
                return response()->json(['error' => __('flashcards.err.invalid_action')], 422);
            }

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

        $materiaId = (int) $this->sessao->get('materia');
        $lista = FlashcardBankLocator::loadList($materiaId);
        $overlayKey = (int) $fila[$atual]['overlay_key'];
        $carta = $lista[$overlayKey] ?? [];
        $carta = FlashcardLocale::apply($carta, $overlayKey, app()->getLocale(), FlashcardBankLocator::filenameFor($materiaId));
        $revelado = (bool) $this->sessao->get('revelado');

        $frente = (string) ($carta['frente'] ?? '');
        $verso = (string) ($carta['verso'] ?? '');

        $progresso = FlashcardProgresso::query()
            ->where('usuario_id', $user->id)
            ->where('materia_id', $materiaId)
            ->where('overlay_key', $overlayKey)
            ->first();

        return [
            'finished' => false,
            'materia_nome' => $this->sessao->get('materia_nome') ?? '',
            'tema' => is_string($carta['tema'] ?? null) && $carta['tema'] !== '' ? $carta['tema'] : null,
            'numero' => $atual + 1,
            'streak_dias' => $this->calcularStreakDias((int) $user->id),
            'intervalo_atual' => $progresso ? (int) $progresso->intervalo_dias : null,
            'frente' => $frente,
            'verso' => $revelado ? $verso : null,
            'revelado' => $revelado,
            'atual' => $atual,
            'total' => count($fila),
            'modo' => (string) ($this->sessao->get('modo') ?? 'revisao'),
        ];
    }

    /**
     * @return array{dominado: int, aprendendo: int, novo: int, total: int}
     */
    private function masteryBreakdown(int $userId, int $materiaId): array
    {
        $total = count(FlashcardBankLocator::loadList($materiaId));

        $rows = FlashcardProgresso::query()
            ->where('usuario_id', $userId)
            ->where('materia_id', $materiaId)
            ->get(['intervalo_dias']);

        $dominado = $rows->where('intervalo_dias', '>=', Sm2Scheduler::MATURE_THRESHOLD_DAYS)->count();
        $comProgresso = $rows->count();

        return [
            'dominado' => $dominado,
            'aprendendo' => $comProgresso - $dominado,
            'novo' => $total - $comProgresso,
            'total' => $total,
        ];
    }

    private function revisadosHoje(int $uid): int
    {
        return (int) DB::table('flashcard_progresso')
            ->where('usuario_id', $uid)
            ->whereDate('ultima_revisao_em', now()->toDateString())
            ->count();
    }

    /**
     * @return list<array{data: string, n: int}>
     */
    private function ultimos7Dias(int $uid): array
    {
        $rows = DB::table('flashcard_progresso')
            ->where('usuario_id', $uid)
            ->where('ultima_revisao_em', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(ultima_revisao_em) as dia, COUNT(*) as n')
            ->groupBy('dia')
            ->pluck('n', 'dia');

        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $chave = $data->toDateString();
            $out[] = ['data' => $data->format('d/m'), 'n' => (int) ($rows[$chave] ?? 0)];
        }

        return $out;
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
        $overlayKey = (int) $fila[$atual]['overlay_key'];

        $progresso = FlashcardProgresso::firstOrNew(
            ['usuario_id' => $user->id, 'materia_id' => $materiaId, 'overlay_key' => $overlayKey],
            ['fator_facilidade' => Sm2Scheduler::DEFAULT_EASE_FACTOR]
        );

        $qualidade = Sm2Scheduler::buttonToQuality($avaliacao);
        $novo = Sm2Scheduler::next(
            (float) ($progresso->fator_facilidade ?? Sm2Scheduler::DEFAULT_EASE_FACTOR),
            (int) ($progresso->intervalo_dias ?? 0),
            (int) ($progresso->repeticoes ?? 0),
            $qualidade
        );

        $progresso->fator_facilidade = $novo['ease_factor'];
        $progresso->intervalo_dias = $novo['interval_days'];
        $progresso->repeticoes = $novo['repetitions'];
        $progresso->ultima_revisao_em = now();
        $progresso->proxima_revisao_em = now()->addDays($novo['interval_days']);
        $progresso->total_revisoes = (int) ($progresso->total_revisoes ?? 0) + 1;
        $progresso->save();

        $resultados = (array) ($this->sessao->get('resultados') ?? []);
        $resultados[] = ['overlay_key' => $overlayKey, 'avaliacao' => $avaliacao];
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
