<?php

namespace App\Http\Controllers;

use App\Models\DemoAttempt;
use App\Models\Faculdade;
use App\Models\Materia;
use App\Models\Questao;
use App\Services\Questions\QuestionExamBuilder;
use App\Support\DemoSession;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use App\Support\QuestionLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class DemoController extends Controller
{
    public const SESSION_COOKIE = 'bc_demo_session';

    private DemoSession $demo;

    public function __construct()
    {
        $this->demo = new DemoSession;
    }

    public function show()
    {
        $faculdades = Faculdade::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->with(['agrupamentos' => fn ($q) => $q->orderBy('ordem')])
            ->get();

        return view('demo.show', compact('faculdades'));
    }

    public function configurar(Request $request)
    {
        $faculdades = Faculdade::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->get();

        $facSlug = trim((string) $request->query('faculdade', ''));
        $facultadAtiva = $facSlug !== ''
            ? $faculdades->firstWhere('slug', $facSlug)
            : $faculdades->first();

        $combinacoes = collect();

        $comboMeta = [];

        if ($facultadAtiva) {
            $combinacoes = DB::table('questoes as q')
                ->leftJoin('materias as m', 'm.id', '=', 'q.materia_id')
                ->leftJoin('agrupamentos as a', 'a.id', '=', 'm.agrupamento_id')
                ->leftJoin('catedras as c', 'c.id', '=', 'q.catedra_id')
                ->where('a.faculdade_id', $facultadAtiva->id)
                ->where('q.is_demo', true)
                ->select(
                    'q.materia_id', 'q.catedra_id', 'q.parcial', 'q.plano',
                    'm.nome as materia_nome',
                    'a.nome as agrupamento_nome',
                    'c.nome as catedra_nome'
                )
                ->groupBy('q.materia_id', 'q.catedra_id', 'q.parcial', 'q.plano', 'm.nome', 'a.nome', 'c.nome')
                ->orderBy('m.nome')
                ->orderBy('c.nome')
                ->orderBy('q.parcial')
                ->get();

            foreach ($combinacoes as $idx => $row) {
                $stats = $this->demoComboStats(
                    (int) $row->materia_id,
                    $row->catedra_id !== null ? (int) $row->catedra_id : null,
                    trim((string) ($row->parcial ?? ''))
                );
                $metaParts = [];
                if (! empty($row->catedra_nome)) {
                    $metaParts[] = (string) $row->catedra_nome;
                }
                if (! empty($row->plano)) {
                    $metaParts[] = (string) $row->plano;
                }
                $p = trim((string) ($row->parcial ?? ''));
                if ($p !== '') {
                    $metaParts[] = __('demo.configurar.parcial_label', ['n' => $p]);
                }
                $comboMeta[] = [
                    'index' => $idx,
                    'materia_id' => (int) $row->materia_id,
                    'catedra_id' => $row->catedra_id !== null ? (int) $row->catedra_id : null,
                    'parcial' => $p,
                    'label' => (string) $row->materia_nome,
                    'agrupamento' => trim((string) ($row->agrupamento_nome ?? '')),
                    'subtitle' => implode(' · ', $metaParts),
                    'total' => $stats['total'],
                    'temas' => $stats['temas'],
                ];
            }
        }

        return view('demo.configurar', [
            'faculdades' => $faculdades,
            'facultadAtiva' => $facultadAtiva,
            'combinacoes' => $combinacoes,
            'comboMeta' => $comboMeta,
            'demoMax' => 5,
        ]);
    }

    public function iniciar(Request $request): RedirectResponse|Response
    {
        $request->merge([
            'catedra_id' => (($v = $request->input('catedra_id')) === '' || $v === null) ? null : $v,
        ]);

        $validated = $request->validate([
            'materia_id' => 'required|integer|exists:materias,id',
            'catedra_id' => 'nullable|integer|exists:catedras,id',
            'parcial' => 'nullable|string|max:50',
            'temas' => 'nullable|array',
            'temas.*' => 'string|max:200',
            'orden' => 'nullable|string|in:random,banco',
            'faculdade_slug' => 'nullable|string|max:80',
        ]);

        $materiaId = (int) $validated['materia_id'];
        /** @var Materia $mat */
        $mat = Materia::query()->withCount('catedras')->findOrFail($materiaId);
        $catId = isset($validated['catedra_id']) ? (int) $validated['catedra_id'] : null;
        $facSlugRedirect = trim((string) $request->input('faculdade_slug', ''));

        if ($mat->catedras_count > 0 && ($catId === null || $catId <= 0)) {
            return redirect()->route('demo.configurar', array_filter([
                'faculdade' => $facSlugRedirect !== '' ? $facSlugRedirect : null,
            ]))->with('error', __('demo.err.catedra'));
        }
        if ($catId !== null && ! $mat->catedras()->whereKey($catId)->exists()) {
            return redirect()->route('demo.configurar', array_filter([
                'faculdade' => $facSlugRedirect !== '' ? $facSlugRedirect : null,
            ]))->with('error', __('demo.err.catedra_invalid'));
        }

        $uuid = trim((string) $request->cookie(self::SESSION_COOKIE, ''));
        $newSession = ($uuid === '' || strlen($uuid) < 16);
        if ($newSession) {
            $uuid = Str::uuid()->toString();
        }

        $ipDayLimit = (int) config('landing.demo.limite_sessoes_por_ip_dia', 3);
        if ($newSession && RateLimiter::tooManyAttempts('demo_ip_day:'.today()->format('Y-m-d').':'.sha1((string) $request->ip()), $ipDayLimit)) {
            return redirect()->route('demo.resultado')->with('error', __('demo.err.ip_limit'));
        }

        // Limite IP+UA: máx N sessões em 7 dias (default 5)
        $uaHash = sha1((string) $request->userAgent());
        $ipUa7dLimit = (int) config('landing.demo.limite_sessoes_ip_ua_7d', 5);
        if ($newSession) {
            $sessions7d = (int) DemoAttempt::query()
                ->where('ip', (string) $request->ip())
                ->where('user_agent_hash', $uaHash)
                ->where('created_at', '>=', now()->subDays(7))
                ->distinct('session_uuid')
                ->count('session_uuid');
            if ($sessions7d >= $ipUa7dLimit) {
                return redirect()->route('demo.resultado')->with('error', __('demo.err.ip_limit'));
            }
        }

        if ($this->overLimit24hPerMateria($uuid, (string) $request->ip(), $materiaId)) {
            return redirect()->route('demo.paywall', [
                'motivo' => 'limite_materia',
                'materia_id' => $materiaId,
            ])->withCookie($this->wrapCookie($uuid));
        }

        $parciais = isset($validated['parcial']) && $validated['parcial'] !== '' ? [(string) $validated['parcial']] : [];
        $temas = array_values(array_filter(array_map('strval', (array) ($validated['temas'] ?? []))));

        $shufflePack = (($validated['orden'] ?? 'random') !== 'banco');
        $facSlug = trim((string) ($validated['faculdade_slug'] ?? ''));

        $pack = QuestionExamBuilder::buildPack($materiaId, $catId, $parciais, $temas, 5, true, $shufflePack);
        if (count($pack) < 1) {
            return redirect()->route('demo.configurar', array_filter([
                'faculdade' => $facSlug !== '' ? $facSlug : null,
            ]))->with('error', __('demo.err.no_demo_questions'));
        }

        if ($newSession) {
            RateLimiter::hit('demo_ip_day:'.today()->format('Y-m-d').':'.sha1((string) $request->ip()), 86400);
        }

        $this->demo->clear();
        $this->demo->init([
            'uuid' => $uuid,
            'materia_id' => $materiaId,
            'materia_nome' => $mat->nome,
            'catedra_id' => $catId,
            'banco_questoes' => QuestionBankLocator::filenameFor($materiaId),
            'questoes' => $pack,
            'atual' => 0,
            'respostas' => [],
            'feedback' => [],
            'completed' => false,
        ]);

        $resp = redirect()->route('demo.questao');

        return $resp->withCookie($this->wrapCookie($uuid));
    }

    public function questao(Request $request)
    {
        if (! $this->demo->isActive()) {
            return redirect()->route('demo.show');
        }

        $uuid = (string) $this->demo->get('uuid');
        $materiaId = (int) $this->demo->get('materia_id');
        if ($this->overLimit24hPerMateria($uuid, (string) $request->ip(), $materiaId)) {
            $this->demo->clear();

            return redirect()->route('demo.paywall', [
                'motivo' => 'limite_materia',
                'materia_id' => $materiaId,
            ]);
        }

        $questoes = (array) $this->demo->get('questoes');
        $idx = (int) $this->demo->get('atual');

        if (! isset($questoes[$idx])) {
            return redirect()->route('demo.paywall');
        }

        $banco = (string) $this->demo->get('banco_questoes');
        $qRaw = QuestionLocale::apply($questoes[$idx], (string) app()->getLocale(), $banco);

        return view('demo.questao', [
            'questao' => new Question($qRaw),
            'indice' => $idx + 1,
            'total' => count($questoes),
            'materiaNome' => (string) $this->demo->get('materia_nome'),
            'quiz_translation_overlay_missing' => ! QuestionLocale::hasTranslationOverlay((string) app()->getLocale(), $banco),
            'materiaDemoId' => $materiaId,
        ]);
    }

    public function responder(Request $request): JsonResponse
    {
        if (! $this->demo->isActive()) {
            return response()->json(['ok' => false, 'msg' => 'inactive'], 400);
        }

        $validated = $request->validate([
            'resposta' => 'nullable|string',
        ]);

        $uuid = (string) $this->demo->get('uuid');
        $ip = (string) $request->ip();
        $materiaId = (int) $this->demo->get('materia_id');

        if ($this->overLimit24hPerMateria($uuid, $ip, $materiaId)) {
            $this->demo->clear();

            return response()->json(['ok' => false, 'paywall' => true, 'paywall_url' => route('demo.paywall', [
                'motivo' => 'limite_materia',
                'materia_id' => $materiaId,
            ])], 403);
        }

        $questoes = (array) $this->demo->get('questoes');
        $idx = (int) $this->demo->get('atual');
        if (! isset($questoes[$idx])) {
            return response()->json(['ok' => false, 'paywall' => true, 'paywall_url' => route('demo.paywall')], 400);
        }

        $overlay = (int) ($questoes[$idx]['_overlay_key'] ?? -1);

        /** @var Questao|null $qmeta */
        $qmeta = Questao::query()->where('materia_id', $materiaId)->where('overlay_key', $overlay)->first();

        $banco = (string) $this->demo->get('banco_questoes');
        $qRaw = QuestionLocale::apply($questoes[$idx], (string) app()->getLocale(), $banco);
        $question = new Question($qRaw);

        $ans = trim((string) ($validated['resposta'] ?? ''));
        $acertou = $question->isCorrect($ans);

        DemoAttempt::query()->create([
            'session_uuid' => $uuid,
            'ip' => $ip !== '' ? $ip : null,
            'user_agent_hash' => sha1((string) $request->userAgent()),
            'materia_id' => $materiaId,
            'questao_id' => $qmeta?->id,
            'acertou' => $acertou,
        ]);

        if ($idx + 1 >= count($questoes)) {
            $totalQuestoes = count($questoes);
            $acertosCount = (int) DemoAttempt::query()
                ->where('session_uuid', $uuid)
                ->where('materia_id', $materiaId)
                ->where('created_at', '>=', now()->subMinutes(30))
                ->where('acertou', true)
                ->count();
            $acertosCount = min($acertosCount, $totalQuestoes);

            $this->demo->clear();

            return response()->json([
                'ok' => true,
                'done' => true,
                'paywall_url' => route('demo.resultado', [
                    'materia_id' => $materiaId,
                    'acertos' => $acertosCount,
                    'total' => $totalQuestoes,
                ]),
                'acertou' => $acertou,
                'feedback' => $question->getFeedback(),
                'resposta_correta' => $question->getCorrectAnswer(),
                'resposta_usuario' => $ans,
            ]);
        }

        $this->demo->set('atual', $idx + 1);

        return response()->json([
            'ok' => true,
            'done' => false,
            'next_url' => route('demo.questao'),
            'acertou' => $acertou,
            'feedback' => $question->getFeedback(),
            'resposta_correta' => $question->getCorrectAnswer(),
            'resposta_usuario' => $ans,
        ]);
    }

    public function paywall(Request $request)
    {
        return $this->resultado($request);
    }

    public function resultado(Request $request)
    {
        $materiaId = $request->query('materia_id');
        $motivo = trim((string) $request->query('motivo', ''));

        $acertos = (int) $request->query('acertos', 0);
        $total = max(1, (int) $request->query('total', 5));
        $pct = (int) round(($acertos / $total) * 100);

        return view('demo.resultado', [
            'materiaPreId' => $materiaId ? (int) $materiaId : null,
            'acertos' => $acertos,
            'total' => $total,
            'pct' => $pct,
            'motivoPaywall' => $motivo,
        ]);
    }

    private function overLimit24hPerMateria(string $uuid, string $ip, int $materiaId): bool
    {
        $since = now()->subDay();

        $q = DemoAttempt::query()->where('materia_id', $materiaId)->where('created_at', '>=', $since);

        $q->where(function ($w) use ($uuid, $ip) {
            $w->where('session_uuid', $uuid);
            if ($ip !== '') {
                $w->orWhere('ip', $ip);
            }
        });

        return (int) $q->count() >= 5;
    }

    /**
     * Contagens de questões demo por combinação (matéria + opcional cátedra + parcial), alinhadas ao QuestionExamBuilder.
     *
     * @return array{total: int, temas: list<array{tema: string, count: int}>}
     */
    private function demoComboStats(int $materiaId, ?int $catedraId, string $parcial): array
    {
        $q = Questao::query()->where('materia_id', $materiaId)->where('is_demo', true);

        if ($parcial !== '') {
            $q->where('parcial', $parcial);
        }

        if ($catedraId !== null && $catedraId > 0) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }

        $total = (int) (clone $q)->count();

        $temasRows = (clone $q)->whereNotNull('tema')->where('tema', '!=', '')
            ->selectRaw('tema, count(*) as c')
            ->groupBy('tema')
            ->orderBy('tema')
            ->get();

        $temas = $temasRows->map(fn ($r) => [
            'tema' => (string) $r->tema,
            'count' => (int) $r->c,
        ])->all();

        return ['total' => $total, 'temas' => $temas];
    }

    private function wrapCookie(string $uuid): Cookie
    {
        return cookie(
            self::SESSION_COOKIE,
            $uuid,
            60 * 24 * 30,
            '/',
            null,
            (bool) config('session.secure', false),
            true,
            false,
            config('session.same_site') ?? 'lax'
        );
    }
}
