<?php

namespace App\Http\Controllers;

use App\Services\AI\GeminiClient;
use App\Support\Question;
use App\Support\QuestionLocale;
use App\Support\SimulationSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    private const SESSION_KEY = 'ia_chat_historico';

    private const MAX_TURNS = 8;

    public function history(): JsonResponse
    {
        return response()->json(['mensagens' => session(self::SESSION_KEY, [])]);
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mensagem' => 'required|string|max:1000',
        ]);

        $mensagem = trim($data['mensagem']);
        if ($mensagem === '') {
            return response()->json(['error' => __('ia_chat.empty')], 422);
        }

        $historico = (array) session(self::SESSION_KEY, []);
        $historico[] = ['role' => 'user', 'texto' => $mensagem];

        $idioma = match (substr((string) app()->getLocale(), 0, 2)) {
            'es' => 'espanhol',
            'en' => 'inglês',
            default => 'português',
        };

        $contexto = $this->contextoQuestaoAtual();

        $system = 'Você é um tutor de medicina que ajuda estudantes a entender conteúdo e tirar dúvidas de forma clara e direta. '
            ."Responda sempre em {$idioma}. Seja objetivo, no máximo 6 frases por resposta, a menos que o aluno peça mais detalhes."
            .($contexto !== '' ? "\n\nContexto: o aluno está respondendo agora esta questão:\n{$contexto}" : '');

        try {
            $resposta = app(GeminiClient::class)->chat($historico, $system);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => __('ia_chat.error')], 502);
        }

        $historico[] = ['role' => 'model', 'texto' => $resposta];
        $historico = array_slice($historico, -self::MAX_TURNS * 2);
        session([self::SESSION_KEY => $historico]);

        return response()->json(['resposta' => $resposta]);
    }

    public function clear(): JsonResponse
    {
        session()->forget(self::SESSION_KEY);

        return response()->json(['ok' => true]);
    }

    private function contextoQuestaoAtual(): string
    {
        $sim = new SimulationSession;
        if (! $sim->isActive()) {
            return '';
        }

        $questoes = (array) ($sim->get('questoes') ?? []);
        $atual = (int) ($sim->get('atual') ?? 0);
        if (! isset($questoes[$atual]) || ! is_array($questoes[$atual])) {
            return '';
        }

        $banco = (string) ($sim->get('banco_questoes') ?? '');
        $qRaw = QuestionLocale::apply($questoes[$atual], (string) app()->getLocale(), $banco);
        $questao = new Question($qRaw);

        $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $linhas = [$questao->getPergunta()];
        foreach ($questao->getOpcoes() as $i => $texto) {
            $linhas[] = ($letras[$i] ?? $i).') '.$texto;
        }

        return implode("\n", $linhas);
    }
}
