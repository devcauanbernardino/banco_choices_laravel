<?php

namespace App\Console\Commands;

use App\Models\Questao;
use App\Services\Questions\QuestionTemaSuggester;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class QuestionsSuggestTemasCommand extends Command
{
    protected $signature = 'questions:suggest-temas
                            {file? : Nome do JSON em storage/app/data (ex.: questoes_microbiologia_refinado.json)}
                            {--materia-id= : Carrega o ficheiro mapeado por QuestionBankLocator para esta matéria}
                            {--taxonomy= : Caminho para JSON: objeto "Nome do tema" -> lista de palavras‑chave}
                            {--output= : Guardar sugestões em JSON (senão imprime resumo no terminal)}
                            {--apply : Grava na tabela questoes apenas onde tema está vazio (requer --materia-id)}';

    protected $description = 'Sugere tema por questão a partir do texto (enunciado, opções, feedback) e de uma taxonomia de palavras‑chave.';

    public function handle(): int
    {
        $taxonomyPath = $this->option('taxonomy');
        if (! is_string($taxonomyPath) || trim($taxonomyPath) === '') {
            $this->error('É obrigatório passar --taxonomy=/caminho/para/taxonomia.json');
            $this->line('Formato do JSON: { "Egito antigo": ["egito", "faraó", "nilo"], "Feudalismo": ["feudal", "vassalo"] }');

            return self::FAILURE;
        }

        $fullTaxPath = str_starts_with($taxonomyPath, DIRECTORY_SEPARATOR)
            || preg_match('#^[A-Za-z]:\\\\#', $taxonomyPath) === 1
            ? $taxonomyPath
            : base_path($taxonomyPath);

        if (! is_readable($fullTaxPath)) {
            $this->error('Taxonomia ilegível: '.$fullTaxPath);

            return self::FAILURE;
        }

        $taxonomyRaw = json_decode((string) file_get_contents($fullTaxPath), true);
        if (! is_array($taxonomyRaw) || $taxonomyRaw === []) {
            $this->error('Taxonomia JSON inválida ou vazia.');

            return self::FAILURE;
        }

        /** @var array<string, array<int, string>|string> $taxonomy */
        $taxonomy = $taxonomyRaw;

        $fileArg = $this->argument('file');
        $materiaOpt = $this->option('materia-id');

        $path = null;
        if ($fileArg !== null && $fileArg !== '') {
            $name = (string) $fileArg;
            $path = str_contains($name, DIRECTORY_SEPARATOR) || str_contains($name, '/')
                ? $name
                : storage_path('app/data/'.$name);
        } elseif ($materiaOpt !== null && $materiaOpt !== '') {
            $mid = (int) $materiaOpt;
            $path = QuestionBankLocator::resolvePath($mid);
        } else {
            $this->error('Indique {file} em storage/app/data ou --materia-id=<id>.');

            return self::FAILURE;
        }

        if (! is_string($path) || ! is_readable($path)) {
            $this->error('Ficheiro de questões ilegível: '.(string) $path);

            return self::FAILURE;
        }

        $raw = json_decode((string) file_get_contents($path), true);
        if (! is_array($raw)) {
            $this->error('JSON inválido.');

            return self::FAILURE;
        }

        $questoes = [];
        if (isset($raw['questoes']) && is_array($raw['questoes'])) {
            $questoes = array_values(array_filter($raw['questoes'], 'is_array'));
        } elseif (array_is_list($raw)) {
            $questoes = array_values(array_filter($raw, 'is_array'));
        }

        if ($questoes === []) {
            $this->warn('Nenhuma questão encontrada no JSON.');

            return self::SUCCESS;
        }

        $suggestions = [];
        $semMatch = 0;

        foreach ($questoes as $i => $blob) {
            if (! is_array($blob)) {
                continue;
            }
            $meta = QuestionTemaSuggester::suggest($blob, $taxonomy);
            if ($meta['tema'] === null) {
                $semMatch++;
            }
            $numero = $blob['numero'] ?? $i;
            $suggestions[] = [
                'overlay_key' => $i,
                'numero' => $numero,
                'tema_existente_json' => isset($blob['tema']) ? (string) $blob['tema'] : null,
                'tema_sugerido' => $meta['tema'],
                'score' => $meta['score'],
                'hits' => $meta['hits'],
                'preview' => mb_strlen($meta['texto_usado']) > 220
                    ? mb_substr($meta['texto_usado'], 0, 217).'…'
                    : $meta['texto_usado'],
            ];
        }

        $outPath = $this->option('output');
        $payload = [
            'fonte' => $path,
            'taxonomia' => $fullTaxPath,
            'total' => count($suggestions),
            'sem_correspondencia' => $semMatch,
            'itens' => $suggestions,
        ];

        if (is_string($outPath) && trim($outPath) !== '') {
            $dest = str_starts_with($outPath, DIRECTORY_SEPARATOR)
                || preg_match('#^[A-Za-z]:\\\\#', $outPath) === 1
                ? $outPath
                : base_path($outPath);
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Sugestões gravadas em: '.$dest);
        } else {
            $this->table(
                ['overlay', 'num', 'sugerido', 'score'],
                array_map(fn ($r) => [
                    $r['overlay_key'],
                    $r['numero'],
                    $r['tema_sugerido'] ?? '—',
                    $r['score'],
                ], array_slice($suggestions, 0, 40))
            );
            if (count($suggestions) > 40) {
                $this->warn('Mostrando só as primeiras 40 linhas; use --output=... para o JSON completo.');
            }
        }

        $this->info('Total: '.count($suggestions).' · Sem match na taxonomia: '.$semMatch);

        if ($this->option('apply')) {
            $midApply = $this->option('materia-id');
            if ($midApply === null || $midApply === '') {
                $this->error('--apply exige --materia-id=<id> para alinhar overlay_key à tabela questoes.');

                return self::FAILURE;
            }
            $midInt = (int) $midApply;
            $updated = 0;
            foreach ($suggestions as $row) {
                $tema = $row['tema_sugerido'] ?? null;
                if ($tema === null || $tema === '') {
                    continue;
                }
                $ok = Questao::query()
                    ->where('materia_id', $midInt)
                    ->where('overlay_key', (int) $row['overlay_key'])
                    ->where(function ($q) {
                        $q->whereNull('tema')->orWhere('tema', '');
                    })
                    ->update(['tema' => $tema]);
                $updated += $ok;
            }
            $this->info("Linhas questoes atualizadas (tema vazio → sugerido): {$updated}");
        }

        return self::SUCCESS;
    }
}
