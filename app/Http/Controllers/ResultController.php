<?php

namespace App\Http\Controllers;

use App\Models\HistoricoSimulado;
use App\Support\Question;
use App\Support\QuestionLocale;
use App\Support\SimulationSession;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    /**
     * Revisão de um simulado já guardado em histórico (ícone “ver” em /simulados).
     */
    public function showHistory(HistoricoSimulado $historico)
    {
        if ((int) $historico->usuario_id !== (int) Auth::id()) {
            abort(403);
        }

        $historico->load('materia');

        $payload = $historico->detalhes_json;
        if (! is_array($payload)) {
            $payload = [];
        }

        $detalhes = $payload['detalhes'] ?? [];

        $acertos = (int) $historico->acertos;
        $total = (int) $historico->total_questoes;
        $porcentagem = $total > 0 ? round(($acertos / $total) * 100, 1) : 0;
        $modo = (string) ($payload['modo'] ?? 'estudo');
        $materiaNome = $historico->materia?->nome ?? '';
        $tempoSegundos = $payload['tempo_segundos'] ?? null;
        if ($tempoSegundos !== null) {
            $tempoSegundos = (int) $tempoSegundos;
        }

        return view('simulation.result', compact(
            'acertos', 'total', 'porcentagem', 'detalhes', 'modo',
            'materiaNome', 'tempoSegundos'
        ));
    }

    public function show()
    {
        $sim = new SimulationSession;

        if (! $sim->isActive()) {
            return redirect()->route('dashboard');
        }

        $questoes = (array) ($sim->get('questoes') ?? []);
        $respostas = (array) ($sim->get('respostas') ?? []);
        $modo = $sim->get('modo') ?? 'estudo';
        $materiaId = $sim->get('materia');
        $materiaNome = $sim->get('materia_nome') ?? '';

        $acertos = 0;
        $total = count($questoes);
        $detalhes = [];
        $banco = (string) ($sim->get('banco_questoes') ?? '');
        $locale = (string) app()->getLocale();

        foreach ($questoes as $i => $qData) {
            $qData = QuestionLocale::apply($qData, $locale, $banco);
            $q = new Question($qData);
            $userAnswer = $respostas[$i] ?? null;
            $correct = $q->isCorrect($userAnswer);
            if ($correct) {
                $acertos++;
            }

            $detalhes[] = [
                'pergunta' => $q->getPergunta(),
                'resposta_usuario' => $userAnswer,
                'resposta_correta' => $q->getCorrectAnswer(),
                'acertou' => $correct,
                'feedback' => $q->getFeedback(),
                'parcial' => isset($questoes[$i]['_parcial']) ? $questoes[$i]['_parcial'] : null,
            ];
        }

        $tempoSegundos = null;
        if ($modo === 'exame' && $sim->get('inicio')) {
            $tempoSegundos = time() - (int) $sim->get('inicio');
        }

        // Save to history
        HistoricoSimulado::create([
            'usuario_id' => Auth::id(),
            'materia_id' => $materiaId,
            'acertos' => $acertos,
            'total_questoes' => $total,
            'detalhes_json' => [
                'v' => 1,
                'modo' => $modo,
                'detalhes' => $detalhes,
                'tempo_segundos' => $tempoSegundos,
            ],
        ]);

        $sim->clear();

        $porcentagem = $total > 0 ? round(($acertos / $total) * 100, 1) : 0;

        return view('simulation.result', compact(
            'acertos', 'total', 'porcentagem', 'detalhes', 'modo',
            'materiaNome', 'tempoSegundos'
        ));
    }
}
