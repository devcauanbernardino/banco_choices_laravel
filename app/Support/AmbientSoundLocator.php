<?php

namespace App\Support;

/**
 * Sons ambiente reais que o usuario envia manualmente (upload direto na
 * pasta, sem passar por deploy/git). Convencao: cada som e uma subpasta em
 * public/assets/audio/<slug>/ contendo um arquivo de audio (mp3/ogg/wav/
 * flac/m4a) - o nome do arquivo em si nao importa, pega o primeiro audio
 * encontrado dentro da pasta. O botao na UI so aparece quando a pasta/arquivo
 * existir. 'rain' e especial: tem fallback sintetizado no motor JS
 * (public/assets/js/pomodoro-engine.js) quando nao ha arquivo real ainda.
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
            $dir = $root.DIRECTORY_SEPARATOR.$entry;
            if (! is_dir($dir)) {
                continue;
            }
            $file = self::findAudioFile($dir, $entry);
            if ($file !== null) {
                $slug = self::SLUG_ALIASES[$entry] ?? $entry;
                $out[$slug] = $entry.'/'.$file;
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
