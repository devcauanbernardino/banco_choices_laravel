<?php

namespace App\Console\Commands;

use App\Models\Agrupamento;
use App\Models\Faculdade;
use App\Models\Materia;
use Illuminate\Console\Command;

class MigrateMateriasCommand extends Command
{
    protected $signature = 'banco:migrate-materias';

    protected $description = 'Associa matérias legadas sem agrupamento ao Ciclo Biomédico (UBA)';

    public function handle(): int
    {
        $fac = Faculdade::query()->firstOrCreate(
            ['slug' => 'uba'],
            [
                'nome' => 'UBA',
                'ordem' => 1,
                'ativo' => true,
            ]
        );

        $agr = Agrupamento::query()->firstOrCreate(
            ['faculdade_id' => $fac->id, 'slug' => 'uba-ciclo-biomedico'],
            [
                'nome' => 'Ciclo Biomédico',
                'tipo' => 'ciclo',
                'ordem' => 1,
            ]
        );

        $n = Materia::query()->whereNull('agrupamento_id')->update([
            'agrupamento_id' => $agr->id,
        ]);

        $this->info("Matérias atualizadas: {$n}");

        return self::SUCCESS;
    }
}
