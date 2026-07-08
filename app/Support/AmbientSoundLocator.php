<?php

namespace App\Support;

/**
 * Sons ambiente reais que o usuario envia manualmente (upload direto na
 * pasta, sem passar por deploy/git). Aceita duas convencoes em
 * public/assets/audio/: (a) uma subpasta <slug>/ contendo um arquivo de
 * audio (mp3/ogg/wav/flac/m4a) - o nome do arquivo dentro nao importa; ou
 * (b) o arquivo solto direto na raiz (upload "achatado", comum em
 * ferramentas de upload de pasta do cPanel que nao preservam subpastas) -
 * nesse caso o slug vem do nome do arquivo sem extensao. O botao na UI so
 * aparece quando o arquivo existir. 'rain'/'chuva' e especial: tem
 * fallback sintetizado no motor JS (public/assets/js/pomodoro-engine.js)
 * quando nao ha arquivo real ainda.
 */
final class AmbientSoundLocator
{
    private const EXTENSIONS = ['mp3', 'ogg', 'wav', 'flac', 'm4a'];

    /**
     * Pastas com nome equivalente ao slug interno ja usado pelo motor JS
     * (que tem fallback sintetizado so pra 'rain') - evita duplicar botao.
     */
    private const SLUG_ALIASES = [
        'chuva' => 'rain',
    ];

    /**
     * @return array<string, string> slug => caminho relativo (a partir de assets/audio/), so os que existem em disco
     */
    public static function available(): array
    {
        $root = public_path('assets/audio');
        if (! is_dir($root)) {
            return [];
        }

        $out = [];
        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $root.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($path)) {
                // Convencao esperada: public/assets/audio/<slug>/<arquivo>.
                $file = self::findAudioFile($path, $entry);
                if ($file !== null) {
                    $slug = self::SLUG_ALIASES[$entry] ?? $entry;
                    $out[$slug] = $entry.'/'.$file;
                }

                continue;
            }

            // Tolerante a upload "achatado" (ferramentas de upload de pasta do
            // cPanel as vezes nao preservam subpastas): mp3/wav/etc. soltos
            // direto em public/assets/audio/ tambem contam, usando o nome do
            // arquivo (sem extensao) como slug.
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (in_array($ext, self::EXTENSIONS, true)) {
                $slugFromFile = pathinfo($entry, PATHINFO_FILENAME);
                $slug = self::SLUG_ALIASES[$slugFromFile] ?? $slugFromFile;
                if (! isset($out[$slug])) {
                    $out[$slug] = $entry;
                }
            }
        }

        return $out;
    }

    /**
     * @return array<string, string> slug => URL publica, so os que existem em disco
     */
    public static function availableUrls(): array
    {
        $out = [];
        foreach (self::available() as $slug => $relPath) {
            $out[$slug] = asset('assets/audio/'.$relPath);
        }

        return $out;
    }

    private static function findAudioFile(string $dir, string $slug): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            if (is_file($dir.DIRECTORY_SEPARATOR.$slug.'.'.$ext)) {
                return $slug.'.'.$ext;
            }
        }

        foreach (scandir($dir) ?: [] as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, self::EXTENSIONS, true)) {
                return $file;
            }
        }

        return null;
    }
}
