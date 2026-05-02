<?php

namespace App\Console\Commands;

use App\Models\Materia;
use App\Models\Questao;
use App\Services\Questions\QuestoesMetadataSync;
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

            $atual = Questao::query()->where('materia_id', $materia->id)->where('is_demo', true)->count();
            if ($atual >= $n) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: já há {$atual} demo.");

                continue;
            }

            $ids = Questao::query()
                ->where('materia_id', $materia->id)
                ->where('is_demo', false)
                ->inRandomOrder()
                ->limit($n - $atual)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                continue;
            }

            Questao::query()->whereIn('id', $ids)->update(['is_demo' => true]);

            $this->info('Matéria '.(string) $materia->id.' — marcadas '.count($ids).' questões.');
        }

        return self::SUCCESS;
    }
}
