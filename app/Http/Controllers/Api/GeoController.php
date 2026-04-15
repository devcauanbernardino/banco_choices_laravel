<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function checkoutGeo(Request $request)
    {
        $ip = $request->ip();

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,countryCode");

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return response()->json([
            'country' => 'Argentina',
            'countryCode' => 'AR',
        ]);
    }
}
