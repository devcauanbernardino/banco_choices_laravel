<?php

namespace App\Console\Commands;

use App\Services\Questions\Farmaco1Cat3CatalogInstaller;
use App\Services\Questions\Farmaco1Cat3MetadataSync;
use App\Services\Questions\Farmaco1Cat3SectionCatalog;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuestionsSetupFarmaco1Cat3Command extends Command
{
    protected $signature = 'questions:setup-farmaco1cat3';

    protected $description = 'Configura cátedra III em Farmacología I e sincroniza questões/metadados (parcial/tema) do compilado Medimisión';

    public function handle(): int
    {
        try {
            Farmaco1Cat3CatalogInstaller::ensureCatalog();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Rode: php artisan db:seed --class=CatalogoSeeder');

            return self::FAILURE;
        }

        $materiaId = (int) DB::table('materias')->where('slug', Farmaco1Cat3SectionCatalog::MATERIA_SLUG)->value('id');

        $jsonPath = QuestionBankLocator::resolvePath($materiaId);
        if (! is_file($jsonPath)) {
            $this->error('JSON ausente: '.$jsonPath);

            return self::FAILURE;
        }

        $this->info('Catálogo: matéria farmacologia-i · Cátedra III');

        try {
            $stats = Farmaco1Cat3MetadataSync::sync($materiaId);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['JSON', $jsonPath],
                ['Linhas novas em questoes', (string) $stats['inserted']],
                ['Linhas atualizadas', (string) $stats['updated']],
                ['Sem origem_seccion', (string) $stats['sem_seccion']],
            ]
        );

        $rows = [];
        ksort($stats['por_parcial']);
        foreach ($stats['por_parcial'] as $p => $n) {
            $rows[] = [$p, (string) $n];
        }
        $this->info('Questões por parcial:');
        $this->table(['parcial', 'quantidade'], $rows);

        $this->newLine();
        $this->info('A marcar questões para o funil demo (is_demo)…');
        $demoCode = $this->call('banco:marcar-demo', ['--por-materia' => 12]);
        if ($demoCode !== self::SUCCESS) {
            return $demoCode;
        }

        $this->newLine();
        $this->comment('Banco pronto. Filtros por parcial e tema disponíveis no simulador, demo e banco de questões.');

        return self::SUCCESS;
    }
}
