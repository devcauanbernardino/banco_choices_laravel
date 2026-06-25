<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuestoesResumoCommand extends Command
{
    protected $signature = 'questoes:resumo';

    protected $description = 'Lista faculdades, agrupamentos e matérias com total de questões e demo (somente leitura)';

    public function handle(): int
    {
        $faculdades = DB::table('faculdades')->orderBy('ordem')->get(['id', 'nome', 'slug']);

        $totalGeral = 0;
        foreach ($faculdades as $fac) {
            $this->newLine();
            $this->info("=== Faculdade: {$fac->nome} (slug {$fac->slug}, id {$fac->id}) ===");

            $agrupamentos = DB::table('agrupamentos')->where('faculdade_id', $fac->id)->orderBy('ordem')->get(['id', 'nome', 'slug']);

            foreach ($agrupamentos as $agr) {
                $materias = DB::table('materias as m')
                    ->leftJoin('questoes as q', 'q.materia_id', '=', 'm.id')
                    ->where('m.agrupamento_id', $agr->id)
                    ->select('m.id', 'm.nome', 'm.slug', DB::raw('COUNT(q.id) as total'), DB::raw('SUM(CASE WHEN q.is_demo = 1 THEN 1 ELSE 0 END) as demo'))
                    ->groupBy('m.id', 'm.nome', 'm.slug')
                    ->orderBy('m.id')
                    ->get();

                if ($materias->isEmpty()) {
                    continue;
                }

                $this->line("-- Agrupamento: {$agr->nome} (slug {$agr->slug}) --");
                $table = [];
                foreach ($materias as $m) {
                    $table[] = [$m->id, $m->nome, $m->slug, (string) $m->total, (string) ($m->demo ?? 0)];
                    $totalGeral += (int) $m->total;
                }
                $this->table(['id', 'nome', 'slug', 'total', 'demo'], $table);
            }
        }

        $this->newLine();
        $this->info('Total geral de questões: '.$totalGeral);

        return self::SUCCESS;
    }
}
