<?php

namespace App\Http\Controllers;

use App\Support\Branding;
use Symfony\Component\HttpFoundation\Response;

class FaviconController extends Controller
{
    public function __invoke(): Response
    {
        $resolved = Branding::resolveFaviconFile();

        if ($resolved === null) {
            abort(404);
        }

        ['path' => $path, 'mime' => $mime] = $resolved;

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
