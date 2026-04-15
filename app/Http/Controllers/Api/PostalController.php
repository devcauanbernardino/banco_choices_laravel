<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use League\ISO3166\Exception\OutOfBoundsException;
use League\ISO3166\ISO3166;

class PostalController extends Controller
{
    /**
     * POST body: country (ISO alpha-2), postal_code (or legacy: cep for Brazil only).
     */
    public function lookup(Request $request)
    {
        $country = strtoupper((string) $request->input('country', ''));
        $postalRaw = (string) $request->input('postal_code', $request->input('cep', ''));

        if ($country === '' && $request->filled('cep')) {
            $country = 'BR';
        }

        if (! preg_match('/^[A-Z]{2}$/', $country)) {
            return response()->json(['error' => 'invalid_country'], 422);
        }

        try {
            (new ISO3166)->alpha2($country);
        } catch (OutOfBoundsException) {
            return response()->json(['error' => 'invalid_country'], 422);
        }

        if ($country === 'BR') {
            $cep = preg_replace('/\D/', '', $postalRaw);
            if (strlen($cep) !== 8) {
                return response()->json(['error' => 'invalid_postal'], 422);
            }

            return $this->viacep($cep);
        }

        $postal = trim($postalRaw);
        if ($postal === '') {
            return response()->json(['error' => 'invalid_postal'], 422);
        }

        try {
            $response = Http::timeout(8)->get(
                'https://api.zippopotam.us/'.strtolower($country).'/'.rawurlencode($postal)
            );
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    return response()->json($data);
                }
            }
        } catch (\Throwable) {
            // ignore
        }

        return response()->json(['error' => 'not_found'], 404);
    }

    public function viacep(string $cep)
    {
        $cep = preg_replace('/\D/', '', $cep);

        try {
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && (! empty($data['erro']))) {
                    return response()->json(['error' => 'not_found'], 404);
                }

                return response()->json($data);
            }
        } catch (\Throwable) {
            // ignore
        }

        return response()->json(['error' => 'not_found'], 404);
    }
}
