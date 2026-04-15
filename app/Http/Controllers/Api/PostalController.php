<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostalController extends Controller
{
    public function lookup(Request $request)
    {
        $cep = preg_replace('/\D/', '', $request->input('cep', ''));

        if (strlen($cep) !== 8) {
            return response()->json(['error' => 'CEP inválido'], 422);
        }

        return $this->viacep($cep);
    }

    public function viacep(string $cep)
    {
        $cep = preg_replace('/\D/', '', $cep);

        try {
            $response = Http::timeout(3)->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return response()->json(['error' => 'CEP não encontrado'], 404);
    }
}
