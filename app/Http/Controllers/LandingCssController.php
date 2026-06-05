<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

/**
 * Serve landing-v2.css a partir do repositório Laravel.
 * No HostGator Plano B o docroot pode ficar com cópia estática desatualizada em /assets/css/.
 */
class LandingCssController extends Controller
{
    public function __invoke(): Response
    {
        $path = public_path('assets/css/landing-v2.css');

        if (! is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
