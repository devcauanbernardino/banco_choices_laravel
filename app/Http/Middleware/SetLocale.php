<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    private const SUPPORTED = ['es_AR', 'pt_BR', 'en_US'];
    private const DEFAULT_LOCALE = 'es_AR';

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');

        if (!$locale || !in_array($locale, self::SUPPORTED, true)) {
            $locale = $request->cookie('bclocale');
        }

        if (!$locale || !in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT_LOCALE;
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
