<?php

namespace App\Console\Commands;

use App\Support\Question;
use Illuminate\Console\Command;

class QuestionsRepairCommand extends Command
{
    protected $signature = 'questions:repair
                            {files?* : Nomes dos JSON em storage/app/data (ex.: questoes_microbiologia_refinado.json)}
                            {--force : Gravar alterações e criar cópia .bak antes}
                            {--dry-run : Só mostrar o que seria alterado (não grava)}';

    protected $description = 'Normaliza questões nos JSON (opções A–E → lista), preenche feedback ausente e lista problemas estruturais';

    public function handle(): int
    {
        $names = $this->argument('files');
        if ($names === []) {
            $names = ['questoes_microbiologia_refinado.json', 'questoes_biologia_final_v2.json'];
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if ($dryRun && $force) {
            $this->error('Use só uma opção: --dry-run ou --force');

            return self::FAILURE;
        }

        $willWrite = $force && ! $dryRun;

        foreach ($names as $name) {
            $path = storage_path('app/data/'.$name);
            if (! is_file($path)) {
                $this->warn("Ignorado (ficheiro inexistente): {$path}");

                continue;
            }

            $this->repairFile($path, $willWrite);
        }

        if (! $willWrite) {
            $this->newLine();
            $this->comment('Nada foi gravado. Para aplicar alterações com backup .bak: php artisan questions:repair --force');
        }

        return self::SUCCESS;
    }

    private function repairFile(string $path, bool $willWrite): void
    {
        $basename = basename($path);
        $json = file_get_contents($path);
        if ($json === false) {
            $this->error("Não foi possível ler: {$path}");

            return;
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            $this->error("JSON inválido: {$basename}");

            return;
        }

        if (! isset($data['questoes']) || ! is_array($data['questoes'])) {
            $this->warn("Formato sem chave \"questoes\": {$basename}");

            return;
        }

        $lista = $data['questoes'];
        if ($lista === []) {
            $this->warn("Lista de questões vazia: {$basename}");

            return;
        }

        $changed = 0;
        $warnings = [];
        $fbFilled = 0;

        foreach ($lista as $i => $item) {
            if (! is_array($item)) {
                $warnings[] = "Índice {$i}: item não é objeto/array";

                continue;
            }

            $beforeHash = md5(json_encode($item, JSON_UNESCAPED_UNICODE));
            $num = (string) ($item['numero'] ?? ($i + 1));

            $normalized = $this->normalizeQuestionRow($item, $num, $warnings);
            $q = new Question($normalized);

            $opts = $q->getOpcoes();
            if (count($opts) < 2) {
                $warnings[] = "#{$num}: menos de 2 opções";
            }

            $gab = $q->getCorrectAnswer();
            if ($gab !== '' && is_numeric($gab) && (int) $gab >= count($opts)) {
                $warnings[] = "#{$num}: gabarito índice {$gab} fora do intervalo (0–".(max(0, count($opts) - 1)).')';
            }

            if (! Question::editorialFeedbackPresent($normalized['feedback'] ?? null)) {
                $normalized['feedback'] = Question::synthesizeFeedbackEs($normalized);
                $fbFilled++;
            }

            $afterHash = md5(json_encode($normalized, JSON_UNESCAPED_UNICODE));
            if ($beforeHash !== $afterHash) {
                $changed++;
            }

            $lista[$i] = $normalized;
        }

        $data['questoes'] = $lista;

        $this->info($basename);
        $this->line('  Questões: '.count($lista));
        $this->line('  Linhas alteradas (normalização e/ou feedback): '.$changed.' (feedback preenchido/sintetizado nesta passagem: '.$fbFilled.')');

        if ($warnings !== []) {
            $this->warn('  Avisos estruturais: '.count($warnings));
            foreach (array_slice($warnings, 0, 25) as $w) {
                $this->line('    · '.$w);
            }
            if (count($warnings) > 25) {
                $this->line('    … +'.(count($warnings) - 25).' mais');
            }
        }

        if (! $willWrite) {
            $this->comment('  (não gravado — usa --force para aplicar + backup .bak)');

            return;
        }

        $bak = $path.'.bak.'.date('Ymd_His');
        if (! @copy($path, $bak)) {
            $this->error('  Falha ao criar backup: '.$bak);

            return;
        }
        $this->line('  Backup: '.basename($bak));

        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n";
        if ($encoded === false || file_put_contents($path, $encoded) === false) {
            $this->error('  Falha ao gravar '.$path);

            return;
        }
        $this->info('  Ficheiro atualizado.');
    }

    /**
     * Converte opções com chaves A/B/C/D para lista ordenada; apara textos.
     *
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    private function normalizeQuestionRow(array $item, string $numero, array &$warnings): array
    {
        foreach (['pergunta', 'enunciado', 'texto', 'questao'] as $k) {
            if (isset($item[$k]) && is_string($item[$k])) {
                $item[$k] = trim($item[$k]);
            }
        }

        $raw = $item['opcoes'] ?? $item['alternativas'] ?? $item['opciones'] ?? null;
        if (! is_array($raw)) {
            return $item;
        }

        $first = reset($raw);
        if ($first !== false && is_array($first) && (isset($first['texto']) || isset($first['text']))) {
            if (isset($item['opcoes']) && is_array($item['opcoes'])) {
                foreach ($item['opcoes'] as $idx => $op) {
                    if (! is_array($op)) {
                        continue;
                    }
                    foreach (['texto', 'text'] as $tk) {
                        if (isset($op[$tk]) && is_string($op[$tk])) {
                            $item['opcoes'][$idx][$tk] = trim($op[$tk]);
                        }
                    }
                }
            }

            return $item;
        }

        $numericKeys = array_keys($raw) === range(0, count($raw) - 1);
        if (! $numericKeys) {
            $order = ['A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e'];
            $ordered = [];
            foreach ($order as $letter) {
                if (array_key_exists($letter, $raw)) {
                    $ordered[] = trim((string) $raw[$letter]);
                }
            }
            if ($ordered !== []) {
                $item['opcoes'] = $ordered;

                return $item;
            }

            $warnings[] = "#{$numero}: chaves de opções não reconhecidas (use A–E ou lista ordenada)";
        } else {
            $item['opcoes'] = array_values(array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $raw));
        }

        return $item;
    }
}
