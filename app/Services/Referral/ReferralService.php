<?php

namespace App\Services\Referral;

use App\Models\Pedido;
use App\Models\Referral;
use App\Models\User;

class ReferralService
{
    /**
     * @return array{ok: bool, referrer: ?User, msg: ?string}
     */
    public static function validarCodigo(string $codigo, string $referidoEmail, ?int $autenticatedUserId = null): array
    {
        $codigo = strtoupper(trim($codigo));
        if ($codigo === '') {
            return ['ok' => false, 'referrer' => null, 'msg' => 'empty'];
        }

        /** @var User|null $referrer */
        $referrer = User::query()->where('codigo_cupom', $codigo)->first();
        if (! $referrer) {
            return ['ok' => false, 'referrer' => null, 'msg' => 'invalid'];
        }

        if ($autenticatedUserId !== null && (int) $referrer->id === $autenticatedUserId) {
            return ['ok' => false, 'referrer' => null, 'msg' => 'self'];
        }

        $emailNorm = mb_strtolower(trim($referidoEmail));
        if (mb_strtolower(trim($referrer->email)) === $emailNorm) {
            return ['ok' => false, 'referrer' => null, 'msg' => 'self_email'];
        }

        $dup = Referral::query()->whereRaw('LOWER(TRIM(referido_email)) = ?', [$emailNorm])->exists();
        if ($dup) {
            return ['ok' => false, 'referrer' => null, 'msg' => 'duplicate_referee'];
        }

        return ['ok' => true, 'referrer' => $referrer, 'msg' => null];
    }

    public static function aplicarDescontoSubtotal(float $subtotalBruto): float
    {
        $pct = (float) config('referral.desconto_referido_percent', 0);
        if ($pct <= 0 || $subtotalBruto <= 0) {
            return round($subtotalBruto, 2);
        }

        return round(max(0, $subtotalBruto * (1 - $pct / 100)), 2);
    }

    public static function creditoGeradoPorPedido(?string $cupomCodigo, float $valorPagoLiquido): float
    {
        if ($valorPagoLiquido <= 0 || $cupomCodigo === null || trim($cupomCodigo) === '') {
            return 0.0;
        }
        $pct = (float) config('referral.referrer_credit_percent', 0);
        if ($pct <= 0) {
            return 0.0;
        }

        return round($valorPagoLiquido * ($pct / 100), 2);
    }

    public static function processarFulfillmentPorPedidoId(int $pedidoId): void
    {
        /** @var Pedido|null $pedido */
        $pedido = Pedido::query()->find($pedidoId);
        if (! $pedido || $pedido->status !== 'completed') {
            return;
        }

        $cupRaw = trim((string) ($pedido->codigo_cupom_usado ?? ''));
        if ($cupRaw === '') {
            return;
        }

        if (Referral::query()->where('pedido_id', $pedidoId)->exists()) {
            return;
        }

        $referrer = User::query()->where('codigo_cupom', strtoupper($cupRaw))->first();
        if (! $referrer) {
            return;
        }

        $valor = (float) $pedido->valor_total;
        $cred = self::creditoGeradoPorPedido($cupRaw, $valor);
        if ($cred <= 0) {
            return;
        }

        $emailRef = mb_strtolower(trim((string) $pedido->email));

        /** @var ?User $referee */
        $referee = User::query()->whereRaw('LOWER(TRIM(email)) = ?', [$emailRef])->first();

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $pedido,
            $referrer,
            $referee,
            $cred,
            $cupRaw,
            $emailRef
        ): void {

            Referral::query()->create([
                'referrer_user_id' => $referrer->id,
                'referido_user_id' => $referee?->id,
                'referido_email' => $emailRef,
                'codigo_usado' => strtoupper($cupRaw),
                'pedido_id' => (int) $pedido->id,
                'valor_credito_gerado' => $cred,
                'status' => 'credited',
            ]);

            $referrer->increment('saldo_credito', $cred);

            $referrer->creditoMovimentos()->create([
                'tipo' => 'referral_credit',
                'valor' => $cred,
                'referencia_tipo' => 'pedido',
                'referencia_id' => $pedido->id,
                'descricao' => 'Crédito por indicação — pedido #'.$pedido->id,
            ]);
        });
    }
}
