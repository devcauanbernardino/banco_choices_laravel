<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\DeckCarta;
use App\Models\DeckProgresso;
use App\Services\Decks\AnkiApkgImporter;
use App\Services\Decks\DeckQueueBuilder;
use App\Support\DeckSession;
use App\Support\Sm2Scheduler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeckController extends Controller
{
    private DeckSession $sessao;

    public function __construct()
    {
        $this->sessao = new DeckSession;
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $decks = Deck::query()
            ->where('usuario_id', $user->id)
            ->withCount('cartas')
            ->orderByDesc('id')
            ->get();

        $resumoPorDeck = $decks->mapWithKeys(function (Deck $d) use ($user) {
            return [$d->id => DeckQueueBuilder::counts((int) $user->id, (int) $d->id)];
        });

        $materiasUsuario = $user->materiasUnicas();

        return view('decks.index', compact('decks', 'resumoPorDeck', 'materiasUsuario'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaIds = $user->materias()->pluck('materias.id')->all();

        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'descricao' => 'nullable|string|max:255',
            'materia_id' => ['nullable', 'integer', Rule::in($materiaIds)],
        ]);

        $deck = Deck::create([
            'usuario_id' => $user->id,
            'materia_id' => $data['materia_id'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
        ]);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.created'));
    }

    public function storeAnki(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaIds = $user->materias()->pluck('materias.id')->all();

        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'materia_id' => ['nullable', 'integer', Rule::in($materiaIds)],
            'arquivo' => 'required|file|max:51200|mimes:apkg,zip',
        ]);

        try {
            $cartas = AnkiApkgImporter::extrairCartas($request->file('arquivo')->getRealPath());
        } catch (\RuntimeException $e) {
            return redirect()->route('decks.index')->with('error', __('decks.err.import_'.$e->getMessage()) !== 'decks.err.import_'.$e->getMessage()
                ? __('decks.err.import_'.$e->getMessage())
                : __('decks.err.import_generic'));
        }

        if ($cartas === []) {
            return redirect()->route('decks.index')->with('error', __('decks.err.import_empty'));
        }

        $deck = Deck::create([
            'usuario_id' => $user->id,
            'materia_id' => $data['materia_id'] ?? null,
            'nome' => $data['nome'],
            'descricao' => __('decks.form.imported_from_anki'),
        ]);

        $ordem = 1;
        foreach ($cartas as $c) {
            DeckCarta::create([
                'deck_id' => $deck->id,
                'frente' => $c['frente'],
                'verso' => $c['verso'],
                'ordem' => $ordem++,
            ]);
        }

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.imported', ['n' => count($cartas)]));
    }

    public function show(Deck $deck)
    {
        $this->ensureOwner($deck);
        $deck->load('cartas');

        return view('decks.show', compact('deck'));
    }

    public function update(Request $request, Deck $deck): RedirectResponse
    {
        $this->ensureOwner($deck);

        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'descricao' => 'nullable|string|max:255',
        ]);

        $deck->update($data);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.updated'));
    }

    public function destroy(Deck $deck): RedirectResponse
    {
        $this->ensureOwner($deck);
        $deck->delete();

        return redirect()->route('decks.index')->with('success', __('decks.flash.deleted'));
    }

    public function share(Request $request, Deck $deck): RedirectResponse
    {
        $this->ensureOwner($deck);

        $request->validate([
            'materia_id' => 'required|integer|exists:materias,id',
            'confirma_direitos' => 'accepted',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->possuiMateria((int) $request->input('materia_id'))) {
            abort(403);
        }

        $deck->update([
            'materia_id' => $request->input('materia_id'),
            'compartilhado' => true,
            'compartilhado_em' => now(),
        ]);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.shared'));
    }

    public function unshare(Deck $deck): RedirectResponse
    {
        $this->ensureOwner($deck);
        $deck->update(['compartilhado' => false]);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.unshared'));
    }

    public function descobrir(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaIds = $user->materias()->pluck('materias.id')->all();

        $decks = Deck::query()
            ->where('compartilhado', true)
            ->where('usuario_id', '!=', $user->id)
            ->whereIn('materia_id', $materiaIds)
            ->withCount('cartas')
            ->with(['usuario:id,nome', 'materia:id,nome'])
            ->orderByDesc('compartilhado_em')
            ->get();

        $jaClonados = Deck::query()
            ->where('usuario_id', $user->id)
            ->whereNotNull('deck_origem_id')
            ->pluck('deck_origem_id')
            ->all();

        return view('decks.descobrir', compact('decks', 'jaClonados'));
    }

    public function clonar(Deck $deck): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $deck->compartilhado || (int) $deck->usuario_id === (int) $user->id) {
            abort(403);
        }
        if (! $deck->materia_id || ! $user->possuiMateria((int) $deck->materia_id)) {
            abort(403);
        }

        $novoDeck = Deck::create([
            'usuario_id' => $user->id,
            'materia_id' => $deck->materia_id,
            'nome' => $deck->nome,
            'descricao' => $deck->descricao,
            'deck_origem_id' => $deck->id,
        ]);

        foreach ($deck->cartas as $c) {
            DeckCarta::create([
                'deck_id' => $novoDeck->id,
                'frente' => $c->frente,
                'verso' => $c->verso,
                'ordem' => $c->ordem,
            ]);
        }

        return redirect()->route('decks.show', $novoDeck)->with('success', __('decks.flash.cloned'));
    }

    public function storeCarta(Request $request, Deck $deck): RedirectResponse
    {
        $this->ensureOwner($deck);

        $data = $request->validate([
            'frente' => 'required|string|max:2000',
            'verso' => 'required|string|max:2000',
        ]);

        $ordem = (int) $deck->cartas()->max('ordem') + 1;

        DeckCarta::create([
            'deck_id' => $deck->id,
            'frente' => $data['frente'],
            'verso' => $data['verso'],
            'ordem' => $ordem,
        ]);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.card_added'));
    }

    public function updateCarta(Request $request, Deck $deck, DeckCarta $carta): RedirectResponse
    {
        $this->ensureOwner($deck);
        $this->ensureCartaBelongsToDeck($deck, $carta);

        $data = $request->validate([
            'frente' => 'required|string|max:2000',
            'verso' => 'required|string|max:2000',
        ]);

        $carta->update($data);

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.card_updated'));
    }

    public function destroyCarta(Deck $deck, DeckCarta $carta): RedirectResponse
    {
        $this->ensureOwner($deck);
        $this->ensureCartaBelongsToDeck($deck, $carta);
        $carta->delete();

        return redirect()->route('decks.show', $deck)->with('success', __('decks.flash.card_deleted'));
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'deck' => 'required|integer|exists:decks,id',
            'novos_por_dia' => 'nullable|integer|min:0|max:200',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $deckId = (int) $request->input('deck');

        /** @var Deck|null $deck */
        $deck = Deck::query()->find($deckId);
        if (! $deck || (int) $deck->usuario_id !== (int) $user->id) {
            return response()->json(['error' => __('decks.err.unauthorized')], 403);
        }

        $novosPorDia = (int) $request->input('novos_por_dia', DeckQueueBuilder::DEFAULT_NEW_CARDS_PER_DAY);
        $fila = DeckQueueBuilder::buildQueue((int) $user->id, $deckId, $novosPorDia);
        $refs = array_merge($fila['due'], $fila['new']);

        if ($refs === []) {
            return response()->json(['error' => __('decks.err.nothing_to_review')], 422);
        }

        $this->sessao->init([
            'deck' => $deckId,
            'deck_nome' => $deck->nome,
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
            return response()->json(['error' => __('decks.err.session_expired')], 409);
        }

        $this->ensureSessionAuthorized();

        if ($request->has('revelar')) {
            $this->sessao->set('revelado', true);

            return response()->json($this->cardPayload());
        }

        if ($request->has('avaliar')) {
            if (! $this->sessao->get('revelado')) {
                return response()->json(['error' => __('decks.err.reveal_first')], 409);
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

        return response()->json(['error' => __('decks.err.invalid_action')], 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function cardPayload(): array
    {
        $fila = (array) ($this->sessao->get('fila') ?? []);
        $atual = (int) ($this->sessao->get('atual') ?? 0);
        $cartaId = (int) $fila[$atual];

        /** @var DeckCarta $carta */
        $carta = DeckCarta::query()->findOrFail($cartaId);
        $revelado = (bool) $this->sessao->get('revelado');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $progresso = DeckProgresso::query()
            ->where('usuario_id', $user->id)
            ->where('deck_carta_id', $cartaId)
            ->first();

        return [
            'finished' => false,
            'deck_nome' => $this->sessao->get('deck_nome') ?? '',
            'numero' => $atual + 1,
            'streak_dias' => 0,
            'intervalo_atual' => $progresso ? (int) $progresso->intervalo_dias : null,
            'frente' => $carta->frente,
            'verso' => $revelado ? $carta->verso : null,
            'revelado' => $revelado,
            'atual' => $atual,
            'total' => count($fila),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryPayload(): array
    {
        $resultados = (array) ($this->sessao->get('resultados') ?? []);
        $deckNome = (string) ($this->sessao->get('deck_nome') ?? '');

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
            'deck_nome' => $deckNome,
            'total' => count($resultados),
            'contagem' => $contagem,
        ];
    }

    private function registrarAvaliacao(string $avaliacao): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $fila = (array) ($this->sessao->get('fila') ?? []);
        $atual = (int) $this->sessao->get('atual');
        $cartaId = (int) $fila[$atual];

        $progresso = DeckProgresso::firstOrNew(
            ['usuario_id' => $user->id, 'deck_carta_id' => $cartaId],
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
        $resultados[] = ['deck_carta_id' => $cartaId, 'avaliacao' => $avaliacao];
        $this->sessao->set('resultados', $resultados);
    }

    private function ensureOwner(Deck $deck): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ((int) $deck->usuario_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function ensureCartaBelongsToDeck(Deck $deck, DeckCarta $carta): void
    {
        if ((int) $carta->deck_id !== (int) $deck->id) {
            abort(404);
        }
    }

    private function ensureSessionAuthorized(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $deckId = $this->sessao->get('deck');

        if ($deckId && is_numeric($deckId)) {
            /** @var Deck|null $deck */
            $deck = Deck::query()->find((int) $deckId);
            if (! $deck || (int) $deck->usuario_id !== (int) $user->id) {
                $this->sessao->clear();
                abort(403);
            }
        }
    }
}
