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
    public static function faviconPublicPath(): string
    {
        $candidates = [
            'img/logo-bd-favicon.png',
            'assets/img/logo-bd-favicon.png',
            'img/logo-bd.png',
            'img/logo-bd.webp',
            'img/logo-bd.jpg',
            'img/logo-bd.jpeg',
            'assets/img/logo-bd.png',
            'assets/img/logo-bd.webp',
            'assets/img/logo-bd.jpg',
            'assets/img/logo-bd.jpeg',
            'img/logo-bd.svg',
            'assets/img/logo-bd.svg',
        ];

        foreach ($candidates as $rel) {
            if (is_file(public_path($rel))) {
                return $rel;
            }
        }

        return self::logoPublicPath();
    }

    public static function faviconMimeType(): string
    {
        return match (strtolower(pathinfo(self::faviconPublicPath(), PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
    }
}
