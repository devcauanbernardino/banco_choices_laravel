<?php

namespace App\Console\Commands;

use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupDemoQuestionsCommand extends Command
{
    protected $signature = 'bancodechoices:setup-demo-questions';

    protected $description = 'Sincroniza JSON → tabela questoes e marca is_demo (funil /probar-gratis)';

    public function handle(): int
    {
        $dataDir = storage_path('app/data');
        if (! is_dir($dataDir)) {
            $this->error('Pasta inexistente: storage/app/data — envia data.zip e extrai no servidor.');

            return self::FAILURE;
        }

        $missing = [];
        foreach (QuestionBankLocator::allKnownFilenames() as $file) {
            $path = $dataDir.DIRECTORY_SEPARATOR.$file;
            if (! is_readable($path)) {
                $missing[] = $file;
                $this->warn("JSON em falta: {$file}");
            } else {
                $this->line('OK JSON: '.$file);
            }
        }

        if ($missing !== [] && $missing === QuestionBankLocator::allKnownFilenames()) {
            $this->error('Nenhum banco de questões encontrado em storage/app/data.');

            return self::FAILURE;
        }

        $this->info('A marcar questões demo (banco:marcar-demo)…');
        Artisan::call('banco:marcar-demo', ['--por-materia' => 12]);
        $this->line(trim(Artisan::output()));

        $this->newLine();
        $this->info('Demo por faculdade (slug):');

        $rows = DB::table('questoes as q')
            ->join('materias as m', 'm.id', '=', 'q.materia_id')
            ->join('agrupamentos as a', 'a.id', '=', 'm.agrupamento_id')
            ->join('faculdades as f', 'f.id', '=', 'a.faculdade_id')
            ->where('q.is_demo', true)
            ->groupBy('f.slug', 'f.nome')
            ->selectRaw('f.slug, f.nome, COUNT(*) as total')
            ->orderBy('f.slug')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Nenhuma questão is_demo na BD. Confere JSON em storage/app/data e CatalogoSeeder.');

            return self::FAILURE;
        }

        foreach ($rows as $row) {
            $this->line("  {$row->slug}: {$row->total} demo — {$row->nome}");
        }

        $uba = $rows->firstWhere('slug', 'uba');
        if ($uba === null || (int) $uba->total < 1) {
            $this->warn('Medicina UBA (slug uba) sem demo — microbiologia (matéria 1) precisa de questoes_microbiologia_refinado.json.');
        }

        $cbc = $rows->firstWhere('slug', 'cbc');
        if ($cbc === null || (int) $cbc->total < 1) {
            $this->warn('CBC / UBA XXI (slug cbc) sem demo — matéria 4 precisa de biologia_cbc_1parcial_2022.json em storage/app/data.');
        }

        return self::SUCCESS;
    }
}
