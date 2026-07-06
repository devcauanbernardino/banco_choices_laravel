<?php

namespace App\Services\Decks;

use RuntimeException;
use ZipArchive;

/**
 * Extrai pares frente/verso de um pacote .apkg do Anki.
 *
 * Um .apkg é um zip contendo um banco SQLite (collection.anki2 ou
 * collection.anki21) com as notas do usuário. Esta primeira versão só lê
 * texto dos tipos de nota Básico e Cloze — ignora mídia (imagens/áudio)
 * e formatação HTML complexa.
 */
final class AnkiApkgImporter
{
    private const MAX_UPLOAD_BYTES = 50 * 1024 * 1024; // 50 MB
    private const MAX_CARTAS = 5000;

    /**
     * @return list<array{frente: string, verso: string}>
     *
     * @throws RuntimeException se o arquivo não for um .apkg válido
     */
    public static function extrairCartas(string $caminhoArquivo): array
    {
        if (! is_file($caminhoArquivo) || filesize($caminhoArquivo) > self::MAX_UPLOAD_BYTES) {
            throw new RuntimeException('invalid_file');
        }

        $zip = new ZipArchive;
        if ($zip->open($caminhoArquivo) !== true) {
            throw new RuntimeException('invalid_zip');
        }

        $dbEntryName = null;
        foreach (['collection.anki21', 'collection.anki2'] as $candidate) {
            if ($zip->locateName($candidate) !== false) {
                $dbEntryName = $candidate;
                break;
            }
        }

        if ($dbEntryName === null) {
            $zip->close();
            throw new RuntimeException('not_anki_package');
        }

        $tmpDbPath = tempnam(sys_get_temp_dir(), 'anki_import_');
        if ($tmpDbPath === false) {
            $zip->close();
            throw new RuntimeException('tmp_file_error');
        }

        try {
            $stream = $zip->getStream($dbEntryName);
            if ($stream === false) {
                throw new RuntimeException('read_error');
            }

            $out = fopen($tmpDbPath, 'wb');
            $written = 0;
            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 1024);
                if ($chunk === false) {
                    break;
                }
                $written += strlen($chunk);
                if ($written > self::MAX_UPLOAD_BYTES * 3) {
                    // banco descompactado desproporcionalmente grande: aborta (defesa contra zip bomb)
                    throw new RuntimeException('database_too_large');
                }
                fwrite($out, $chunk);
            }
            fclose($out);
            fclose($stream);

            return self::lerCartasDoBanco($tmpDbPath);
        } finally {
            $zip->close();
            @unlink($tmpDbPath);
        }
    }

    /**
     * @return list<array{frente: string, verso: string}>
     */
    private static function lerCartasDoBanco(string $dbPath): array
    {
        $pdo = new \PDO('sqlite:'.$dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $modelsJson = $pdo->query('SELECT models FROM col LIMIT 1')->fetchColumn();
        $models = json_decode((string) $modelsJson, true) ?? [];

        $tipoPorModelo = [];
        foreach ($models as $modelId => $model) {
            $nomeCampos = array_map(fn ($f) => $f['name'], $model['flds'] ?? []);
            $nomeModelo = mb_strtolower((string) ($model['name'] ?? ''));
            $ehCloze = str_contains($nomeModelo, 'cloze') || (int) ($model['type'] ?? 0) === 1;
            $tipoPorModelo[(string) $modelId] = ['campos' => $nomeCampos, 'cloze' => $ehCloze];
        }

        $cartas = [];
        $stmt = $pdo->query('SELECT mid, flds FROM notes');
        foreach ($stmt as $row) {
            if (count($cartas) >= self::MAX_CARTAS) {
                break;
            }

            $mid = (string) $row['mid'];
            $campos = explode("\x1f", (string) $row['flds']);
            $info = $tipoPorModelo[$mid] ?? ['campos' => [], 'cloze' => false];

            if ($info['cloze']) {
                $texto = self::limparHtml($campos[0] ?? '');
                $extra = self::limparHtml($campos[1] ?? '');
                [$frente, $verso] = self::renderizarCloze($texto, $extra);
            } else {
                $frente = self::limparHtml($campos[0] ?? '');
                $verso = self::limparHtml($campos[1] ?? '');
            }

            $frente = trim($frente);
            $verso = trim($verso);
            if ($frente === '' || $verso === '') {
                continue;
            }

            $cartas[] = ['frente' => mb_substr($frente, 0, 2000), 'verso' => mb_substr($verso, 0, 2000)];
        }

        return $cartas;
    }

    /**
     * @return array{0: string, 1: string} [frente, verso]
     */
    private static function renderizarCloze(string $texto, string $extra): array
    {
        $frente = (string) preg_replace('/\{\{c\d+::(.*?)(::.*?)?\}\}/su', '[...]', $texto);
        $verso = (string) preg_replace('/\{\{c\d+::(.*?)::.*?\}\}/su', '$1', $texto);
        $verso = (string) preg_replace('/\{\{c\d+::(.*?)\}\}/su', '$1', $verso);

        if ($extra !== '') {
            $verso .= "\n\n".$extra;
        }

        return [$frente, $verso];
    }

    private static function limparHtml(string $html): string
    {
        $texto = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $texto = preg_replace('/<\/(div|p|li)>/i', "\n", $texto) ?? $texto;
        $texto = strip_tags($texto);
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/[ \t]+/', ' ', $texto) ?? $texto;
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto) ?? $texto;

        return trim($texto);
    }
}
