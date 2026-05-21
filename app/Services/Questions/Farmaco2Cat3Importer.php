<?php

namespace App\Services\Questions;

use App\Support\Question;

final class Farmaco2Cat3Importer
{
    public const DEFAULT_SOURCE_URL = 'https://raw.githubusercontent.com/farmaco2cat3choice/farmaco2cat3choice/main/preguntas.of.js';

    /**
     * @param  array<string, list<array<string, mixed>>>  $porSeccion
     * @return array{
     *   titulo: string,
     *   subtitulo: string,
     *   total_questoes: int,
     *   questoes: list<array<string, mixed>>,
     *   skipped: list<string>,
     * }
     */
    public function buildBank(
        array $porSeccion,
        ?string $onlySection = null,
        string $titulo = 'Farmacología 2 - Cátedra 3',
        string $subtitulo = 'Importado de farmaco2cat3choice.github.io',
    ): array {
        $skipped = [];
        $questoes = [];
        $numero = 1;

        $sections = $onlySection !== null && $onlySection !== ''
            ? [$onlySection => $porSeccion[$onlySection] ?? []]
            : $porSeccion;

        if ($onlySection !== null && $onlySection !== '' && ! isset($porSeccion[$onlySection])) {
            return [
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'total_questoes' => 0,
                'questoes' => [],
                'skipped' => ["Seção inexistente: {$onlySection}"],
            ];
        }

        foreach ($sections as $seccionId => $lista) {
            if (! is_array($lista)) {
                continue;
            }
            foreach ($lista as $idx => $raw) {
                if (! is_array($raw)) {
                    $skipped[] = "{$seccionId}#{$idx}: item inválido";

                    continue;
                }
                $converted = $this->convertOne($raw, $numero, (string) $seccionId);
                if ($converted === null) {
                    $skipped[] = $this->skipReason($raw, $seccionId, $idx);

                    continue;
                }
                $questoes[] = $converted;
                $numero++;
            }
        }

        return [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'total_questoes' => count($questoes),
            'questoes' => $questoes,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>|null
     */
    public function convertOne(array $raw, int $numero, string $seccionId): ?array
    {
        if (! empty($raw['multiple'])) {
            return null;
        }

        $correcta = $raw['correcta'] ?? [];
        if (! is_array($correcta) || count($correcta) !== 1) {
            return null;
        }

        $opciones = $raw['opciones'] ?? [];
        if (! is_array($opciones) || count($opciones) !== 4) {
            return null;
        }

        $idx = (int) $correcta[0];
        if ($idx < 0 || $idx > 3) {
            return null;
        }

        $pergunta = trim((string) ($raw['pregunta'] ?? ''));
        if ($pergunta === '') {
            return null;
        }

        $opcoes = [];
        foreach (['A', 'B', 'C', 'D'] as $i => $letra) {
            $opcoes[] = [
                'letra' => $letra,
                'texto' => trim((string) $opciones[$i]),
            ];
        }

        $row = [
            'numero' => $numero,
            'pergunta' => $pergunta,
            'opcoes' => $opcoes,
            'tipo' => 'multipla_escolha',
            'nota' => $this->detectNota($pergunta),
            'resposta_correta' => chr(ord('A') + $idx),
            'origem_seccion' => $seccionId,
        ];

        $row['feedback'] = Question::synthesizeFeedbackEs($row);

        return $row;
    }

    public function detectNota(string $pergunta): ?string
    {
        $lower = mb_strtolower($pergunta);
        if (str_contains($lower, 'incorrecta') || str_contains($lower, 'no es correcta') || str_contains($lower, 'no es la correcta')) {
            return 'Selecionar opção INCORRETA';
        }
        if (str_contains($lower, 'excepto') || str_contains($lower, 'salvo')) {
            return 'Selecionar opção EXCETO (incorreta)';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function skipReason(array $raw, string $seccionId, int $idx): string
    {
        if (! empty($raw['multiple'])) {
            return "{$seccionId}#{$idx}: múltipla seleção (checkbox)";
        }
        $correcta = $raw['correcta'] ?? [];
        if (is_array($correcta) && count($correcta) > 1) {
            return "{$seccionId}#{$idx}: mais de uma resposta correta";
        }
        $n = is_array($raw['opciones'] ?? null) ? count($raw['opciones']) : 0;
        if ($n !== 4) {
            return "{$seccionId}#{$idx}: {$n} opções (esperado 4)";
        }

        return "{$seccionId}#{$idx}: não convertível";
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function fetchSectionsFromJsFile(string $jsPath): array
    {
        $extractor = base_path('scripts/farmaco2cat3-extract.cjs');
        if (! is_file($extractor)) {
            throw new \RuntimeException("Script de extração não encontrado: {$extractor}");
        }

        $node = $this->resolveNodeBinary();
        $cmd = escapeshellarg($node).' '.escapeshellarg($extractor).' '.escapeshellarg($jsPath);
        $output = shell_exec($cmd);
        if (! is_string($output) || trim($output) === '') {
            throw new \RuntimeException('Falha ao extrair preguntas (Node retornou vazio). Verifique se Node está instalado.');
        }

        $decoded = json_decode($output, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('JSON inválido retornado pelo extrator Node.');
        }

        return $decoded;
    }

    public function downloadSourceJs(string $url, string $destPath): void
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 120,
                'header' => "User-Agent: BancoChoicesImporter/1.0\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false || $body === '') {
            throw new \RuntimeException("Não foi possível baixar: {$url}");
        }
        $dir = dirname($destPath);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException("Não foi possível criar diretório: {$dir}");
        }
        if (file_put_contents($destPath, $body) === false) {
            throw new \RuntimeException("Não foi possível gravar: {$destPath}");
        }
    }

    private function resolveNodeBinary(): string
    {
        foreach (['node', 'node.exe'] as $bin) {
            $found = trim((string) shell_exec(PHP_OS_FAMILY === 'Windows'
                ? "where {$bin} 2>nul"
                : "command -v {$bin} 2>/dev/null"));
            if ($found !== '') {
                $first = preg_split('/\r?\n/', $found)[0] ?? $bin;

                return trim($first) !== '' ? trim($first) : $bin;
            }
        }

        return 'node';
    }
}
