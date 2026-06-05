<?php

namespace App\Support;

final class Branding
{
    /**
     * Path relative to public/ for the active logo asset.
     */
    public static function logoPublicPath(): string
    {
        $override = config('branding.logo');
        if (is_string($override) && $override !== '' && is_file(public_path($override))) {
            return $override;
        }

        $candidates = [
            'img/logo-bd-transparente.png',
            'img/logo-bd-transparente.webp',
            'img/logo-bd-transparente.jpg',
            'img/logo-bd-transparente.jpeg',
            'assets/img/logo-bd-transparente.png',
            'assets/img/logo-bd-transparente.webp',
            'assets/img/logo-bd-transparente.jpg',
            'assets/img/logo-bd-transparente.jpeg',
            'img/logo-bd-transparente.svg',
            'assets/img/logo-bd-transparente.svg',
        ];

        foreach ($candidates as $rel) {
            if (is_file(public_path($rel))) {
                return $rel;
            }
        }

        return 'img/logo-bd-transparente.svg';
    }

    public static function logoUrl(): string
    {
        return asset(self::logoPublicPath());
    }

    /**
     * Logo with solid background — better legibility as browser tab icon.
     */
    /**
     * Relative paths to try (docroot Plan B and repo public/).
     *
     * @return list<string>
     */
    public static function faviconRelativeCandidates(): array
    {
        $configured = config('branding.favicon');
        $list = is_string($configured) && $configured !== ''
            ? [$configured]
            : [];

        return array_values(array_unique([
            ...$list,
            'img/favicon-bd-round.svg',
            'img/favicon-bd-round.png',
            'assets/img/favicon-bd-round.svg',
            'assets/img/favicon-bd-round.png',
            'img/logo-bd.png',
            'assets/img/logo-bd.png',
            'img/logo-bd-transparente.png',
            'assets/img/logo-bd-transparente.png',
            'img/logo-bd-transparente.svg',
            'assets/img/logo-bd-transparente.svg',
            'img/favicon-logo.svg',
            'assets/img/favicon-logo.svg',
            'assets/img/favicon.svg',
            'img/logo-bd-favicon.png',
            'assets/img/logo-bd-favicon.png',
        ]));
    }

    /**
     * @return array{path: string, mime: string, rel: string}|null
     */
    public static function resolveFaviconFile(): ?array
    {
        foreach (self::faviconRelativeCandidates() as $rel) {
            foreach (self::absolutePathsForPublicFile($rel) as $full) {
                if (! is_file($full)) {
                    continue;
                }

                return [
                    'path' => $full,
                    'mime' => self::mimeForPublicFile($rel),
                    'rel' => $rel,
                ];
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function absolutePathsForPublicFile(string $rel): array
    {
        $rel = ltrim($rel, '/');

        return array_values(array_unique([
            public_path($rel),
            base_path('public/'.$rel),
        ]));
    }

    private static function mimeForPublicFile(string $rel): string
    {
        return match (strtolower(pathinfo($rel, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'ico' => 'image/x-icon',
            default => 'image/png',
        };
    }

    public static function faviconPublicPath(): string
    {
        return self::resolveFaviconFile()['rel'] ?? 'img/favicon-bd-round.svg';
    }

    public static function faviconMimeType(): string
    {
        $resolved = self::resolveFaviconFile();

        return $resolved['mime'] ?? 'image/png';
    }

    /**
     * Public URL with cache-bust (Plan B: rota Laravel ou asset no docroot).
     */
    public static function faviconUrl(): string
    {
        $resolved = self::resolveFaviconFile();
        $rel = $resolved['rel'] ?? 'img/favicon-bd-round.svg';

        $base = \Illuminate\Support\Facades\Route::has('favicon')
            ? route('favicon')
            : asset($rel);

        if ($resolved !== null && is_file($resolved['path'])) {
            return $base.'?v='.filemtime($resolved['path']);
        }

        return $base;
    }

    /**
     * Inline data-URI for SVG — evita cache agressivo de /favicon.ico no browser.
     */
    public static function faviconInlineDataUri(): ?string
    {
        $resolved = self::resolveFaviconFile();
        if ($resolved === null || $resolved['mime'] !== 'image/svg+xml') {
            return null;
        }

        $svg = @file_get_contents($resolved['path']);
        if (! is_string($svg) || trim($svg) === '') {
            return null;
        }

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }
}
