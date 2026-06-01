<?php

namespace App\Console\Commands;

use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class QuestionsBuildI18nAllCommand extends Command
{
    protected $signature = 'questions:build-i18n-all
        {--target=both : pt, en ou both}
        {--missing-only : Só bancos/locales com cobertura incompleta (predefinido)}
        {--no-missing-only : Traduzir todos os bancos mesmo que já completos}
        {--sleep=120 : Milissegundos entre chamadas ao Google Translate}
        {--limit= : Limite de questões por banco (testes)}
        {--fresh : Recomeça do zero (ignora traduções já gravadas)}';

    protected $description = 'Gera traduções pt/en para todos os bancos conhecidos (questions:build-i18n em lote)';

    public function handle(): int
    {
        $targetOpt = strtolower((string) $this->option('target'));
        $targets = match ($targetOpt) {
            'pt' => ['pt'],
            'en' => ['en'],
            'both' => ['pt', 'en'],
            default => null,
        };
        if ($targets === null) {
            $this->error('--target deve ser pt, en ou both');

            return self::FAILURE;
        }

        $missingOnly = ! $this->option('no-missing-only');
        $sleep = (string) $this->option('sleep');
        $limit = $this->option('limit');
        $fresh = $this->option('fresh') ? ['--fresh' => true] : [];

        $files = QuestionBankLocator::allKnownFilenames();
        $exit = self::SUCCESS;

        foreach ($files as $file) {
            $sourcePath = storage_path('app/data/'.$file);
            if (! is_file($sourcePath)) {
                $this->warn("Ignorado (inexistente): {$file}");

                continue;
            }

            $total = $this->questionCount($sourcePath);
            if ($total === 0) {
                $this->warn("Ignorado (sem questões): {$file}");

                continue;
            }

            foreach ($targets as $target) {
                $localeDir = $target === 'pt' ? 'pt_BR' : 'en_US';
                $overlayPath = storage_path("app/data/i18n/{$localeDir}/{$file}");
                $existing = is_file($overlayPath)
                    ? count(json_decode((string) file_get_contents($overlayPath), true) ?: [])
                    : 0;

                if ($missingOnly && $existing >= $total) {
                    $this->line("✓ {$file} → {$localeDir}: {$existing}/{$total} (completo)");

                    continue;
                }

                $this->info("→ {$file} → {$target} ({$existing}/{$total})…");

                $params = [
                    'file' => $file,
                    'target' => $target,
                    '--sleep' => $sleep,
                ];
                if ($limit !== null && $limit !== '') {
                    $params['--limit'] = $limit;
                }
                $params = array_merge($params, $fresh);

                $code = Artisan::call('questions:build-i18n', $params, $this->output);
                if ($code !== self::SUCCESS) {
                    $exit = self::FAILURE;
                }
            }
        }

        $this->newLine();
        $this->comment('Cobertura: php artisan questions:analyse --i18n');

        return $exit;
    }

    private function questionCount(string $path): int
    {
        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            return 0;
        }

        return count($data['questoes'] ?? []);
    }
}
