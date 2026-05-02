<?php

namespace App\Http\Controllers;

use App\Models\CreditoMovimento;
use App\Services\Referral\CodigoService;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->codigo_cupom === null || trim((string) $user->codigo_cupom) === '') {
            $cod = CodigoService::gerarUnicoPara($user);
            $user->forceFill(['codigo_cupom' => $cod])->saveQuietly();
        }

        /** @var \App\Models\User $fresh */
        $fresh = $user->fresh();

        /** @var \Illuminate\Database\Eloquent\Collection<int,CreditoMovimento> $mov */
        $mov = $fresh->creditoMovimentos()->orderByDesc('id')->limit(100)->get();

        return view('referral.show', [
            'user' => $fresh,
            'movimentos' => $mov,
        ]);
    }
}
