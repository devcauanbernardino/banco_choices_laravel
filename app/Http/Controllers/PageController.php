<?php

namespace App\Http\Controllers;

use App\Models\Faculdade;
use App\Support\DemoAvailability;

class PageController extends Controller
{
    public function index()
    {
        $faculdades = Faculdade::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->with(['agrupamentos' => fn ($q) => $q->orderBy('ordem')])
            ->get();

        $stats = (array) config('landing.stats', []);

        return view('pages.index', [
            'faculdades' => $faculdades,
            'stats' => $stats,
            'demoCounts' => DemoAvailability::demoCountByFaculdadeSlug(),
        ]);
    }
}
