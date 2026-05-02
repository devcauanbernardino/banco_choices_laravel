<?php

namespace App\Console\Commands;

use App\Support\Question;
use Illuminate\Console\Command;

class AnalyseQuestionBanksCommand extends Command
{
    protected $signature = 'questions:analyse {file? : Um ficheiro em storage/app/data (omite para os dois bancos por defeito)}';

    protected $description = 'Estatísticas dos JSONs de questões (feedback editorial, opções curtas, gabarito/opções inválidos). Ver também: questions:repair';

    public function handle(): int
    {
        $files = $this->argument('file')
            ? [$this->argument('file')]
            : ['questoes_microbiologia_refinado.json', 'questoes_biologia_final_v2.json'];

        foreach ($files as $name) {
            $path = storage_path('app/data/'.$name);
            if (! is_file($path)) {
                $this->error("Ficheiro inexistente: {$path}");

                continue;
            }

            $data = json_decode((string) file_get_contents($path), true);
            if (! is_array($data) || ! isset($data['questoes'])) {
                $this->warn("Formato inesperado: {$name}");

                continue;
            }

            $questoes = $data['questoes'];
            $n = count($questoes);

            $noKeyFeedback = 0;
            $noEditorialFeedback = 0;
            $shortOpt = [];
            $structuralNums = [];
            $titleBroken = [];

            foreach ($questoes as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $num = (int) ($item['numero'] ?? 0);
                $numStr = (string) ($item['numero'] ?? '?');

                if (! isset($item['feedback'])) {
                    $noKeyFeedback++;
                }

                if (! Question::editorialFeedbackPresent($item['feedback'] ?? null)) {
                    $noEditorialFeedback++;
                }

                $qn = new Question($item);
                if ($qn->getPergunta() === 'Questão sem título') {
                    $titleBroken[] = $numStr;
                }

                $opts = $qn->getOpcoes();
                $gab = $qn->getCorrectAnswer();

                if (count($opts) < 2) {
                    $structuralNums[] = "{$numStr}: menos de 2 opções";
                }

                if ($gab !== '' && is_numeric($gab) && count($opts) > 0 && (int) $gab >= count($opts)) {
                    $structuralNums[] = "{$numStr}: gabarito índice {$gab} com apenas ".count($opts).' opções';
                }

                if ($gab === '' && count($opts) >= 2) {
                    $structuralNums[] = "{$numStr}: sem resposta correta (gabarito/resposta_correta)";
                }

                foreach (($item['opcoes'] ?? []) as $j => $op) {
                    $t = is_array($op) ? (string) ($op['texto'] ?? '') : (string) $op;
                    if ($t !== '' && strlen($t) < 18) {
                        $shortOpt[] = 'n'.$num.'@'.(is_array($op) ? ($op['letra'] ?? $j) : $j);
                    }
                }
            }

            $this->info($name." — {$n} questões");
            $this->line('  Sem chave feedback: '.$noKeyFeedback);
            $this->line('  Sem feedback editorial (vazio/placeholder): '.$noEditorialFeedback);
            $this->line('  Opções muito curtas (menos de 18 caracteres): '.count($shortOpt).(count($shortOpt) ? ' — ex.: '.implode(', ', array_slice($shortOpt, 0, 8)) : ''));

            $structCount = count($structuralNums);
            $this->line('  Problemas estruturais (gabarito/opções): '.$structCount);

            $maxShow = 35;

            if ($structCount > 0) {
                foreach (array_slice($structuralNums, 0, $maxShow) as $line) {
                    $this->line('    · '.$line);
                }
                if (count($structuralNums) > $maxShow) {
                    $this->line('    … +'.(count($structuralNums) - $maxShow).' mais');
                }
            }

            if ($titleBroken !== []) {
                $this->warn('  Enunciado em falta ou inválido (#): '.count($titleBroken).' — '.implode(', ', array_slice($titleBroken, 0, 18)).(count($titleBroken) > 18 ? '…' : ''));
            }

            $this->comment('  Corrigir no JSON: php artisan questions:repair (pré-visualização) ou questions:repair --force');
        }

        return self::SUCCESS;
    }
}
