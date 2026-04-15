<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    private const SUPPORTED = ['es_AR', 'pt_BR', 'en_US'];

    public function setLocale(Request $request)
    {
        $locale = $request->input('locale', 'es_AR');

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'es_AR';
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        $cookie = cookie('bclocale', $locale, 525600, '/'); // 1 year

        return redirect()->back()->withCookie($cookie);
    }
}
