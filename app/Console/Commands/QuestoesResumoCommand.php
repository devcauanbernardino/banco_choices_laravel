<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuestoesResumoCommand extends Command
{
    protected $signature = 'questoes:resumo';

    protected $description = 'Lista cada matéria com total de questões e quantas estão marcadas como demo (somente leitura)';

    public function handle(): int
    {
        $rows = DB::table('materias as m')
            ->leftJoin('questoes as q', 'q.materia_id', '=', 'm.id')
            ->select('m.id', 'm.nome', 'm.slug', DB::raw('COUNT(q.id) as total'), DB::raw('SUM(CASE WHEN q.is_demo = 1 THEN 1 ELSE 0 END) as demo'))
            ->groupBy('m.id', 'm.nome', 'm.slug')
            ->orderBy('m.id')
            ->get();

        $table = [];
        $totalGeral = 0;
        foreach ($rows as $r) {
            $table[] = [$r->id, $r->nome, $r->slug, (string) $r->total, (string) ($r->demo ?? 0)];
            $totalGeral += (int) $r->total;
        }

        $this->table(['id', 'nome', 'slug', 'total', 'demo'], $table);
        $this->newLine();
        $this->info('Total geral de questões: '.$totalGeral);

        return self::SUCCESS;
    }
}
