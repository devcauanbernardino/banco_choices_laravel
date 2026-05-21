<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;

class QuestionsBuildI18nCommand extends Command
{
    protected $signature = 'questions:build-i18n
        {file : Ficheiro em storage/app/data, ex.: questoes_biologia_final_v2.json}
        {target : Código alvo do Google: pt ou en}
        {--sleep=120 : Milissegundos entre chamadas (rate limit)}
        {--limit= : Traduzir apenas as primeiras N questões (testes / incremental)}
        {--fresh : Ignora traduções já gravadas e recomeça do zero}';

    protected $description = 'Gera storage/app/data/i18n/{locale}/file com traduções (es → pt/en); retoma se o ficheiro já existir';

    public function handle(): int
    {
        if (! class_exists(GoogleTranslate::class)) {
            $this->error('Pacote stichoza/google-translate-php não instalado. Execute: composer install');

            return self::FAILURE;
        }

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

        $dir = storage_path('app/data/i18n/'.$localeDir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir.'/'.$filename;

        $sleepMs = max(0, (int) $this->option('sleep'));
        $questoes = $data['questoes'];
        $limitOpt = $this->option('limit');
        if ($limitOpt !== null && $limitOpt !== '') {
            $limit = max(1, (int) $limitOpt);
            $questoes = array_slice($questoes, 0, $limit, true);
            $this->warn('Modo --limit='.$limit.': só '.count($questoes).' questões.');
        }

        $out = [];
        if (! $this->option('fresh') && is_file($dest)) {
            $existing = json_decode((string) file_get_contents($dest), true);
            if (is_array($existing)) {
                $out = $existing;
                $this->info('Retomando: '.count($out).' entradas já em '.$dest);
            }
        }

        $totalQuestoes = count($questoes);
        $this->info('A traduzir até '.$totalQuestoes." questões (es → {$target})…");

        try {
            $tr = new GoogleTranslate($target);
            $tr->setSource('es');
        } catch (\Throwable $e) {
            $this->error('GoogleTranslate: '.$e->getMessage());

            return self::FAILURE;
        }

        $processadas = 0;
        $novas = 0;
        $ignoradas = 0;

        foreach ($questoes as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }

            $key = (string) $idx;
            if (isset($out[$key]) && self::blockComplete($out[$key], $item)) {
                $ignoradas++;
                $processadas++;

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
                $out[$key] = $block;
                $novas++;
            }

            $processadas++;
            if ($novas > 0 && $novas % 20 === 0) {
                $this->persist($dest, $out);
                $this->line("  … guardado {$novas} novas (índice {$idx})");
            }

            if ($processadas % 100 === 0 || $processadas === $totalQuestoes) {
                $this->line("  … percorridas {$processadas}/{$totalQuestoes} (novas: {$novas}, já tinham: {$ignoradas})");
            }
        }

        $this->persist($dest, $out);
        $this->info("Gravado: {$dest} (".count($out).' entradas, '.$novas.' novas nesta execução)');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<string, mixed>  $source
     */
    private static function blockComplete(array $block, array $source): bool
    {
        $pergunta = trim((string) ($source['pergunta'] ?? ''));
        if ($pergunta !== '' && trim((string) ($block['pergunta'] ?? '')) === '') {
            return false;
        }

        $srcOpts = array_values(array_filter(
            $source['opcoes'] ?? [],
            static fn ($op) => is_array($op) && trim((string) ($op['texto'] ?? '')) !== ''
        ));
        $dstOpts = $block['opcoes'] ?? [];
        if (count($srcOpts) > 0 && (! is_array($dstOpts) || count($dstOpts) < count($srcOpts))) {
            return false;
        }

        return trim((string) ($block['pergunta'] ?? '')) !== '' || $srcOpts === [];
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $out
     */
    private function persist(string $dest, array $out): void
    {
        uksort($out, static fn ($a, $b) => (int) $a <=> (int) $b);
        $map = [];
        foreach ($out as $k => $v) {
            $map[(string) $k] = $v;
        }
        $json = json_encode($map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new \RuntimeException('json_encode falhou ao gravar '.$dest);
        }
        file_put_contents($dest, $json);
    }
}
