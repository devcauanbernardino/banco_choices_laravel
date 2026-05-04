<?php

namespace App\Console\Commands;

use App\Models\Materia;
use App\Models\Questao;
use App\Services\Questions\QuestoesMetadataSync;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;

class MarcarDemoCommand extends Command
{
    protected $signature = 'banco:marcar-demo {--por-materia=5}';

    protected $description = 'Marca N questões aleatórias como demo por matéria';

    public function handle(): int
    {
        $n = max(1, (int) $this->option('por-materia'));

        foreach (Materia::query()->orderBy('id')->cursor() as $materia) {
            QuestoesMetadataSync::syncMateria((int) $materia->id);
            if ($materia->catedras()->count() >= 2) {
                QuestoesMetadataSync::assignCatedrasEvenSplit((int) $materia->id);
            }

            $totalQuestoes = Questao::query()->where('materia_id', $materia->id)->count();
            if ($totalQuestoes === 0) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: sem questões — pulada.");

                continue;
            }

            $atual = Questao::query()->where('materia_id', $materia->id)->where('is_demo', true)->count();
            if ($atual >= $n) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: já há {$atual} demo.");

                continue;
            }

            $lista = QuestionBankLocator::loadCanonicalList((int) $materia->id);

            $candidates = Questao::query()
                ->where('materia_id', $materia->id)
                ->where('is_demo', false)
                ->inRandomOrder()
                ->get(['id', 'overlay_key']);

            $ids = [];
            $needed = $n - $atual;
            foreach ($candidates as $cand) {
                if (count($ids) >= $needed) {
                    break;
                }
                $blob = $lista[(int) $cand->overlay_key] ?? null;
                if (! is_array($blob) || ! self::looksUsable($blob)) {
                    continue;
                }
                $ids[] = $cand->id;
            }

            if ($ids === []) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: sem questões válidas para demo.");

                continue;
            }

            Questao::query()->whereIn('id', $ids)->update(['is_demo' => true]);

            $this->info('Matéria '.(string) $materia->id.' — marcadas '.count($ids).' questões.');
        }

        return self::SUCCESS;
    }

    /**
     * Aceita apenas questões com enunciado e alternativas com texto real
     * (descarta placeholders tipo "A","B","C","D" ou strings vazias/curtas demais).
     */
    private static function looksUsable(array $blob): bool
    {
        $q = new Question($blob);
        $perg = trim($q->getPergunta());
        if ($perg === '' || $perg === 'Questão sem título') {
            return false;
        }

        $opts = $q->getOpcoes();
        if (count($opts) < 2) {
            return false;
        }

        foreach ($opts as $i => $txt) {
            $t = trim((string) $txt);
            $letra = chr(ord('A') + $i);
            if (mb_strlen($t) < 4) {
                return false;
            }
            if (strcasecmp($t, $letra) === 0) {
                return false;
            }
        }

        return true;
    }
}
