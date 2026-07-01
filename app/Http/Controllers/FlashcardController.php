<?php

namespace App\Http\Controllers;

use App\Models\FlashcardProgresso;
use App\Models\Materia;
use App\Services\Flashcards\FlashcardQueueBuilder;
use App\Support\FlashcardSession;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use App\Support\QuestionLocale;
use App\Support\Sm2Scheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function create(Request $request)
    {
        $request->validate([
            'materia' => 'required|integer|exists:materias,id',
            'novos_por_dia' => 'nullable|integer|min:0|max:200',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = (int) $request->input('materia');

        if (! $user->possuiMateria($materiaId)) {
            return redirect()->route('flashcards.index')->with('error', __('flashcards.err.unauthorized_subject'));
        }

        $materia = Materia::query()->findOrFail($materiaId);
        $novosPorDia = (int) $request->input('novos_por_dia', FlashcardQueueBuilder::DEFAULT_NEW_CARDS_PER_DAY);

        $fila = FlashcardQueueBuilder::buildQueue((int) $user->id, $materiaId, $novosPorDia);
        $refs = array_merge($fila['due'], $fila['new']);

        if ($refs === []) {
            return redirect()->route('flashcards.index')->with('info', __('flashcards.err.nothing_to_review'));
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

        return redirect()->route('flashcards.show');
    }

    public function show()
    {
        if (! $this->sessao->isActive()) {
            return redirect()->route('flashcards.index');
        }

        $this->ensureAuthorized();

        $fila = (array) ($this->sessao->get('fila') ?? []);
        $atual = (int) ($this->sessao->get('atual') ?? 0);

        if (! isset($fila[$atual])) {
            return redirect()->route('flashcards.summary');
        }

        $banco = (string) ($this->sessao->get('banco_questoes') ?? '');
        $materiaId = (int) $this->sessao->get('materia');
        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        $overlayKey = (int) $fila[$atual]['overlay_key'];

        $qRaw = QuestionLocale::apply($lista[$overlayKey] ?? [], (string) app()->getLocale(), $banco);
        $questao = new Question($qRaw);

        $viewData = [
            'questao' => $questao,
            'materiaNome' => $this->sessao->get('materia_nome') ?? '',
            'revelado' => (bool) $this->sessao->get('revelado'),
            'atual' => $atual,
            'total' => count($fila),
        ];

        return view('flashcards.show', $viewData);
    }

    public function process(Request $request)
    {
        if (! $this->sessao->isActive()) {
            return redirect()->route('flashcards.index');
        }

        $this->ensureAuthorized();

        if ($request->has('revelar')) {
            $this->sessao->set('revelado', true);

            return redirect()->route('flashcards.show');
        }

        if ($request->has('avaliar')) {
            if (! $this->sessao->get('revelado')) {
                return redirect()->route('flashcards.show');
            }

            $request->validate([
                'avaliar' => 'required|in:again,hard,good,easy',
            ]);

            $this->registrarAvaliacao((string) $request->input('avaliar'));

            $atual = (int) $this->sessao->get('atual') + 1;
            $this->sessao->set('atual', $atual);
            $this->sessao->set('revelado', false);

            $fila = (array) ($this->sessao->get('fila') ?? []);
            if (! isset($fila[$atual])) {
                return redirect()->route('flashcards.summary');
            }

            return redirect()->route('flashcards.show');
        }

        return redirect()->route('flashcards.show');
    }

    public function summary()
    {
        $resultados = (array) ($this->sessao->get('resultados') ?? []);
        $materiaNome = (string) ($this->sessao->get('materia_nome') ?? '');

        $contagem = ['again' => 0, 'hard' => 0, 'good' => 0, 'easy' => 0];
        foreach ($resultados as $r) {
            $b = (string) ($r['avaliacao'] ?? '');
            if (isset($contagem[$b])) {
                $contagem[$b]++;
            }
        }

        $this->sessao->clear();

        return view('flashcards.summary', [
            'materiaNome' => $materiaNome,
            'total' => count($resultados),
            'contagem' => $contagem,
        ]);
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
