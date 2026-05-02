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
        $faculdades = Faculdade::query()->where('ativo', true)->orderBy('ordem')->get();

        return view('demo.show', compact('faculdades'));
    }

    public function iniciar(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'materia_id' => 'required|integer|exists:materias,id',
            'catedra_id' => 'nullable|integer|exists:catedras,id',
        ]);

        $materiaId = (int) $validated['materia_id'];
        /** @var Materia $mat */
        $mat = Materia::query()->withCount('catedras')->findOrFail($materiaId);
        $catId = isset($validated['catedra_id']) ? (int) $validated['catedra_id'] : null;
        if ($mat->catedras_count > 0 && ($catId === null || $catId <= 0)) {
            return redirect()->route('demo.show')->with('error', __('demo.err.catedra'));
        }
        if ($catId !== null && ! $mat->catedras()->whereKey($catId)->exists()) {
            return redirect()->route('demo.show')->with('error', __('demo.err.catedra_invalid'));
        }

        $uuid = trim((string) $request->cookie(self::SESSION_COOKIE, ''));
        $newSession = ($uuid === '' || strlen($uuid) < 16);
        if ($newSession) {
            $uuid = Str::uuid()->toString();
        }

        if ($newSession && RateLimiter::tooManyAttempts('demo_ip_day:'.today()->format('Y-m-d').':'.sha1((string) $request->ip()), 3)) {
            return redirect()->route('demo.paywall')->with('error', __('demo.err.ip_limit'));
        }

        if ($this->overLimit24hPerMateria($uuid, (string) $request->ip(), $materiaId)) {
            return redirect()->route('demo.paywall')->withCookie($this->wrapCookie($uuid));
        }

        $pack = QuestionExamBuilder::buildPack($materiaId, $catId, [], [], 5, true);
        if (count($pack) < 1) {
            return redirect()->route('demo.show')->with('error', __('demo.err.no_demo_questions'));
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

            return redirect()->route('demo.paywall');
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

            return response()->json(['ok' => false, 'paywall' => true, 'paywall_url' => route('demo.paywall')], 403);
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
            'materia_id' => $materiaId,
            'questao_id' => $qmeta?->id,
            'acertou' => $acertou,
        ]);

        if ($idx + 1 >= count($questoes)) {
            $this->demo->clear();

            return response()->json([
                'ok' => true,
                'done' => true,
                'paywall_url' => route('demo.paywall', ['materia_id' => $materiaId]),
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
        $materiaId = $request->query('materia_id');

        return view('demo.paywall', ['materiaPreId' => $materiaId ? (int) $materiaId : null]);
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
