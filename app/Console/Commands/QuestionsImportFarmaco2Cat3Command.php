<?php

namespace App\Console\Commands;

use App\Services\Questions\Farmaco2Cat3Importer;
use Illuminate\Console\Command;

class QuestionsImportFarmaco2Cat3Command extends Command
{
    protected $signature = 'questions:import-farmaco2cat3
                            {--url=https://raw.githubusercontent.com/farmaco2cat3choice/farmaco2cat3choice/main/preguntas.of.js : URL do preguntas.of.js}
                            {--cache= : Caminho local ao preguntas.of.js (evita download)}
                            {--section= : Importar só uma seção (ex.: inotropicos)}
                            {--output=questoes_farmaco2_cat3.json : Nome do ficheiro em storage/app/data}
                            {--dry-run : Não grava o JSON}';

    protected $description = 'Importa questões do site farmaco2cat3choice (GitHub) para JSON do Banco de Choices';

    public function handle(Farmaco2Cat3Importer $importer): int
    {
        $cacheOpt = $this->option('cache');
        $jsPath = is_string($cacheOpt) && $cacheOpt !== ''
            ? $cacheOpt
            : storage_path('app/data/cache/farmaco2cat3_preguntas.of.js');

        if (! is_file($jsPath)) {
            $url = (string) $this->option('url');
            $this->info("A baixar preguntas.of.js de {$url} …");
            try {
                $importer->downloadSourceJs($url, $jsPath);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
            $this->info('Download concluído: '.$jsPath);
        } else {
            $this->line('A usar cache: '.$jsPath);
        }

        try {
            $porSeccion = $importer->fetchSectionsFromJsFile($jsPath);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $section = $this->option('section');
        $onlySection = is_string($section) && $section !== '' ? $section : null;

        if ($onlySection !== null && ! isset($porSeccion[$onlySection])) {
            $this->error("Seção não encontrada: {$onlySection}");
            $this->line('Seções disponíveis: '.implode(', ', array_keys($porSeccion)));

            return self::FAILURE;
        }

        $bank = $importer->buildBank($porSeccion, $onlySection);
        $skipped = $bank['skipped'];
        unset($bank['skipped']);

        $this->info('Seções no origem: '.count($porSeccion));
        $this->info('Questões importadas: '.$bank['total_questoes']);
        $this->warn('Ignoradas: '.count($skipped));

        if ($skipped !== [] && $this->output->isVerbose()) {
            foreach (array_slice($skipped, 0, 30) as $line) {
                $this->line('  - '.$line);
            }
            if (count($skipped) > 30) {
                $this->line('  … e mais '.(count($skipped) - 30));
            }
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry-run: ficheiro não gravado.');

            return self::SUCCESS;
        }

        $outputName = (string) $this->option('output');
        $dest = storage_path('app/data/'.$outputName);
        $dir = dirname($dest);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error('Não foi possível criar: '.$dir);

            return self::FAILURE;
        }

        $json = json_encode($bank, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false || file_put_contents($dest, $json) === false) {
            $this->error('Falha ao gravar JSON.');

            return self::FAILURE;
        }

        $this->info('Gravado: '.$dest);
        $this->newLine();
        $this->comment('Próximo passo (catálogo + parcial/tema na base):');
        $this->line('  php artisan questions:setup-farmaco2cat3');

        return self::SUCCESS;
    }
}
