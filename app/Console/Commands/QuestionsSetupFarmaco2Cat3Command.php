<?php

namespace App\Console\Commands;

use App\Services\Questions\Farmaco2Cat3CatalogInstaller;
use App\Services\Questions\Farmaco2Cat3MetadataSync;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;

class QuestionsSetupFarmaco2Cat3Command extends Command
{
    protected $signature = 'questions:setup-farmaco2cat3
                            {--import : Baixa e importa o JSON antes de configurar o catálogo}
                            {--reimport : Força nova importação (com cache remoto)}';

    protected $description = 'Configura matéria/cátedra UBA, sincroniza questões e metadados (parcial/tema) do banco Farmaco2 Cat3';

    public function handle(): int
    {
        $jsonPath = QuestionBankLocator::resolvePath(5);
        if ($this->option('import') || $this->option('reimport') || ! is_file($jsonPath)) {
            $this->info('Importando questões do site…');
            $code = $this->call('questions:import-farmaco2cat3', $this->option('reimport') ? [] : [
                '--cache' => storage_path('app/data/cache/farmaco2cat3_preguntas.of.js'),
            ]);
            if ($code !== self::SUCCESS) {
                return $code;
            }
        } elseif (! is_file($jsonPath)) {
            $this->error('JSON ausente: '.$jsonPath);
            $this->line('Execute: php artisan questions:import-farmaco2cat3');

            return self::FAILURE;
        }

        try {
            Farmaco2Cat3CatalogInstaller::ensureCatalog();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Rode: php artisan db:seed --class=CatalogoSeeder');

            return self::FAILURE;
        }

        $this->info('Catálogo: matéria id 5 · Farmacología II — Cátedra III · Cátedra III');

        try {
            $stats = Farmaco2Cat3MetadataSync::sync();
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
        $demoCode = $this->call('banco:marcar-demo', ['--por-materia' => 5]);
        if ($demoCode !== self::SUCCESS) {
            return $demoCode;
        }

        $this->newLine();
        $this->comment('Traduções (pt/en): php artisan questions:build-i18n questoes_farmaco2_cat3.json pt');
        $this->comment('              depois: php artisan questions:build-i18n questoes_farmaco2_cat3.json en');

        $this->newLine();
        $this->comment('Banco pronto. Filtros por 1/2/3/final/libre e tema no simulador, demo e banco de questões.');

        return self::SUCCESS;
    }
}
