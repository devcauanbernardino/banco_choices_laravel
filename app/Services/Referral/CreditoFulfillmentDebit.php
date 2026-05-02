<?php

namespace App\Services\Referral;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditoFulfillmentDebit
{
    /** Chama-se após o pedido ficar completed (HTTP / webhook). */
    public static function debitarCreditoUtilizadoAddon(string $emailComprador, float $valor, ?int $pedidoId): void
    {
        if ($valor < 0.005) {
            return;
        }

        /** @var User|null $user */
        $user = User::query()->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim($emailComprador))])->first();

        if (! $user) {
            return;
        }

        $valor = round($valor, 2);
        if ((float) $user->saldo_credito + 1e-6 < $valor) {
            Log::warning('Saldo crédito insuficiente no fulfillment debit', ['pedido_id' => $pedidoId, 'user_id' => $user->id]);

            return;
        }

        try {
            DB::transaction(function () use ($user, $valor, $pedidoId): void {
                $user->refresh();
                $user->decrement('saldo_credito', $valor);
                $user->creditoMovimentos()->create([
                    'tipo' => 'purchase_use',
                    'valor' => -1 * $valor,
                    'referencia_tipo' => $pedidoId && $pedidoId > 0 ? 'pedido' : null,
                    'referencia_id' => $pedidoId && $pedidoId > 0 ? $pedidoId : null,
                    'descricao' => $pedidoId ? 'Créditos usados no pedido #'.$pedidoId : 'Créditos usados numa compra',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Erro debitar crédito addon: '.$e->getMessage());
        }
    }
}
