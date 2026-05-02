<?php

namespace App\Support;

use App\Mail\AccessGrantedExistingUser;
use App\Models\Pedido;
use App\Models\User;
use App\Services\Referral\ReferralService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Fulfillment local quando o total fica zerado sem passar pelo Mercado Pago.
 */
class PaidAccessGrant
{
    /**
     * @param  list<int>  $materiaIds
     */
    public static function addonComCreditoOuGratuito(User $usuario, float $valorTotalPedido, string $planId, int $planDays, array $materiaIds, ?string $externalRef, float $valorCreditoUsado, ?string $codigoCupom): Pedido
    {
        $email = trim((string) $usuario->email);
        $nome = trim((string) $usuario->nome);

        /** @var Pedido $pedido */
        $pedido = Pedido::query()->create([
            'email' => $email,
            'nome' => $nome,
            'valor_total' => round(max(0, $valorTotalPedido), 2),
            'status' => 'completed',
            'stripe_payment_id' => $externalRef ?? ('LOCAL-'.$usuario->id.'-'.time()),
            'codigo_cupom_usado' => $codigoCupom ? strtoupper(trim($codigoCupom)) : null,
        ]);

        $usuario->garantirMaterias($materiaIds);

        $preco = count($materiaIds) > 0 ? $valorTotalPedido / count($materiaIds) : $valorTotalPedido;
        foreach ($materiaIds as $mid) {
            DB::table('pedidos_itens')->insert([
                'pedido_id' => $pedido->id,
                'materia_id' => $mid,
                'plano_id' => $planId,
                'preco' => round($preco, 2),
                'data_expiracao' => Carbon::now()->addDays($planDays)->format('Y-m-d'),
            ]);
        }

        if ($valorCreditoUsado > 0.005) {
            $usuario->decrement('saldo_credito', $valorCreditoUsado);

            $usuario->creditoMovimentos()->create([
                'tipo' => 'purchase_use',
                'valor' => -1 * round($valorCreditoUsado, 2),
                'referencia_tipo' => 'pedido',
                'referencia_id' => $pedido->id,
                'descricao' => 'Abatimento na compra de matérias — pedido #'.$pedido->id,
            ]);
        }

        try {
            Mail::to($email)->send(new AccessGrantedExistingUser($nome, $planId));
        } catch (\Throwable $e) {
            Log::warning('Mail error (credit addon): '.$e->getMessage());
        }

        try {
            ReferralService::processarFulfillmentPorPedidoId((int) $pedido->id);
        } catch (\Throwable $e) {
            Log::warning('Referral fulfilment (local): '.$e->getMessage());
        }

        return $pedido;
    }
}
