<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;

class QuestionsBuildI18nCommand extends Command
{
    protected $signature = 'questions:build-i18n
        {file : Ficheiro em storage/app/data, ex.: questoes_biologia_final_v2.json}
        {target : Código alvo do Google: pt ou en}
        {--sleep=180 : Milissegundos entre chamadas (rate limit)}
        {--limit= : Traduzir apenas as primeiras N questões (testes / incremental)}';

    protected $description = 'Gera ou sobrescreve storage/app/data/i18n/{locale}/file com traduções (es → pt/en)';

    public function handle(): int
    {
        $filename = $this->argument('file');
        $target = strtolower($this->argument('target'));
        if (! in_array($target, ['pt', 'en'], true)) {
            $this->error('target deve ser pt ou en');

            return self::FAILURE;
        }

        $localeDir = $target === 'pt' ? 'pt_BR' : 'en_US';
        $sourcePath = storage_path('app/data/'.$filename);
        if (! is_file($sourcePath)) {
            $this->error("Ficheiro não encontrado: {$sourcePath}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($data) || ! isset($data['questoes']) || ! is_array($data['questoes'])) {
            $this->error('JSON inválido (esperado { "questoes": [ ... ] })');

            return self::FAILURE;
        }

        $sleepMs = max(0, (int) $this->option('sleep'));
        $questoes = $data['questoes'];
        $limitOpt = $this->option('limit');
        if ($limitOpt !== null && $limitOpt !== '') {
            $limit = max(1, (int) $limitOpt);
            $questoes = array_slice($questoes, 0, $limit);
            $this->warn("Modo --limit={$limit}: só ".count($questoes).' questões.');
        }

        $this->info('A traduzir '.count($questoes)." questões (es → {$target})…");

        $totalQuestoes = count($questoes);
        $processadas = 0;

        try {
            $tr = new GoogleTranslate($target);
            $tr->setSource('es');
        } catch (\Throwable $e) {
            $this->error('GoogleTranslate: '.$e->getMessage());

            return self::FAILURE;
        }

        $out = [];
        foreach ($questoes as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }
            $num = (string) ($item['numero'] ?? $idx);
            $block = [];

            $p = trim((string) ($item['pergunta'] ?? ''));
            if ($p !== '') {
                try {
                    $block['pergunta'] = $tr->translate($p);
                } catch (\Throwable $e) {
                    $this->warn("Falha pergunta #{$num}: ".$e->getMessage());
                    $block['pergunta'] = $p;
                }
                usleep($sleepMs * 1000);
            }

            $fb = trim((string) ($item['feedback'] ?? ''));
            if ($fb !== '') {
                try {
                    $block['feedback'] = $tr->translate($fb);
                } catch (\Throwable $e) {
                    $this->warn("Falha feedback #{$num}: ".$e->getMessage());
                    $block['feedback'] = $fb;
                }
                usleep($sleepMs * 1000);
            }

            $nota = $item['nota'] ?? null;
            if (is_string($nota) && trim($nota) !== '') {
                try {
                    $block['nota'] = $tr->translate($nota);
                } catch (\Throwable $e) {
                    $block['nota'] = $nota;
                }
                usleep($sleepMs * 1000);
            }

            $textos = [];
            foreach (($item['opcoes'] ?? []) as $op) {
                if (! is_array($op)) {
                    continue;
                }
                $t = trim((string) ($op['texto'] ?? ''));
                if ($t === '') {
                    $textos[] = $t;

                    continue;
                }
                try {
                    $textos[] = $tr->translate($t);
                } catch (\Throwable $e) {
                    $textos[] = $t;
                }
                usleep($sleepMs * 1000);
            }
            if ($textos !== []) {
                $block['opcoes'] = $textos;
            }

            if ($block !== []) {
                $out[(string) $idx] = $block;
            }

            $processadas++;
            if ($processadas % 40 === 0 || $processadas === $totalQuestoes) {
                $this->line("  … {$processadas}/{$totalQuestoes}");
            }
        }

        $dir = storage_path('app/data/i18n/'.$localeDir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir.'/'.$filename;
        $json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            $this->error('json_encode falhou');

            return self::FAILURE;
        }
        file_put_contents($dest, $json);
        $this->info("Gravado: {$dest} (".count($out).' entradas)');

        return self::SUCCESS;
    }
}
