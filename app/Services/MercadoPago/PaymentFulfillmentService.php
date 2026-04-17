<?php

namespace App\Services\MercadoPago;

use App\Mail\AccessGrantedExistingUser;
use App\Mail\WelcomeNewUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Resources\Payment;
use PDO;

class PaymentFulfillmentService
{
    public static function isPaymentProcessed(PDO $pdo, int $mpPaymentId): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM mp_payment_processed WHERE mp_payment_id = ? LIMIT 1');
        $stmt->execute([$mpPaymentId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function markPaymentProcessed(PDO $pdo, int $mpPaymentId, string $status, ?string $extRef): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO mp_payment_processed (mp_payment_id, created_at, payment_status, external_reference)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$mpPaymentId, Carbon::now()->format('Y-m-d H:i:s'), $status, $extRef]);
    }

    /**
     * @param  array<string, mixed>  $metaFallback  Completa metadados em falta no objeto Payment (ex.: sessão Laravel após checkout).
     */
    public static function processPaymentNotification(PDO $pdo, Payment $payment, array $metaFallback = []): array
    {
        $mpId = (int) ($payment->id ?? 0);
        if ($mpId <= 0) {
            return ['handled' => false, 'detail' => 'payment_id_invalid'];
        }

        if (self::isPaymentProcessed($pdo, $mpId)) {
            return ['handled' => true, 'detail' => 'already_processed'];
        }

        $status = (string) ($payment->status ?? '');
        $extRef = (string) ($payment->external_reference ?? '');
        $meta = self::metadataToArray($payment->metadata);

        foreach ($metaFallback as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $sk = (string) $k;
            if (! isset($meta[$sk]) || $meta[$sk] === '' || $meta[$sk] === null) {
                $meta[$sk] = $v;
            }
        }

        if ($extRef === '' && isset($metaFallback['external_reference']) && (string) $metaFallback['external_reference'] !== '') {
            $extRef = (string) $metaFallback['external_reference'];
        }

        Log::info("MP process_payment id={$mpId} status={$status} ext_ref={$extRef}");

        if ($status === 'approved') {
            return self::handleApproved($pdo, $payment, $mpId, $extRef, $meta);
        }

        if (in_array($status, ['pending', 'in_process', 'authorized'], true)) {
            self::updatePedidoNonFinal($pdo, $extRef, 'pending_mp');

            return ['handled' => true, 'detail' => 'pending_recorded'];
        }

        if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back', 'in_mediation'], true)) {
            self::updatePedidoNonFinal($pdo, $extRef, 'rejected');
            try {
                self::markPaymentProcessed($pdo, $mpId, $status, $extRef !== '' ? $extRef : null);
            } catch (\PDOException $e) {
                if (! self::isDuplicateKey($e)) {
                    throw $e;
                }
            }

            return ['handled' => true, 'detail' => 'terminal_non_approved'];
        }

        return ['handled' => true, 'detail' => 'status_'.$status];
    }

    private static function handleApproved(PDO $pdo, Payment $payment, int $mpId, string $extRef, array $meta): array
    {
        $pdo->beginTransaction();
        try {
            $pedido = null;
            if ($extRef !== '') {
                $lockSql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
                    ? 'SELECT * FROM pedidos WHERE stripe_payment_id = :r ORDER BY id DESC LIMIT 1'
                    : 'SELECT * FROM pedidos WHERE stripe_payment_id = :r ORDER BY id DESC LIMIT 1 FOR UPDATE';
                $stmt = $pdo->prepare($lockSql);
                $stmt->execute([':r' => $extRef]);
                $pedido = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($pedido && ($pedido['status'] ?? '') === 'completed') {
                    $pdo->rollBack();

                    return ['handled' => true, 'detail' => 'pedido_already_completed'];
                }
            }

            $email = $pedido['email'] ?? ($meta['email'] ?? '');
            $nome = $pedido['nome'] ?? ($meta['name'] ?? '');
            $planId = (string) ($meta['plan_id'] ?? '');
            $planDays = (int) ($meta['plan_duration_days'] ?? 0);
            $materias = self::parseMaterias((string) ($meta['materias'] ?? ''));

            if ($email === '' || $nome === '' || $planId === '' || $planDays <= 0 || $materias === []) {
                $pdo->rollBack();
                Log::warning('MP missing_metadata for approved payment', [
                    'mp_id' => $mpId,
                    'ext_ref' => $extRef,
                    'meta_keys' => array_keys($meta),
                    'has_pedido' => $pedido !== null,
                ]);

                return ['handled' => false, 'detail' => 'missing_metadata'];
            }

            $valorTotal = $pedido ? (float) $pedido['valor_total'] : (float) ($payment->transaction_amount ?? 0);

            if (! $pedido) {
                $stmt = $pdo->prepare(
                    'INSERT INTO pedidos (email, nome, valor_total, status, stripe_payment_id, data_criacao)
                     VALUES (:email, :nome, :valor, :st, :oref, :criado)'
                );
                $stmt->execute([
                    ':email' => $email,
                    ':nome' => $nome,
                    ':valor' => $valorTotal,
                    ':st' => 'awaiting_payment',
                    ':oref' => $extRef !== '' ? $extRef : ('MP-'.$mpId),
                    ':criado' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
                $pedidoId = (int) $pdo->lastInsertId();
            } else {
                $pedidoId = (int) $pedido['id'];
            }

            $existing = User::where('email', $email)->first();

            if ($existing) {
                $existing->garantirMaterias($materias);
                try {
                    Mail::to($email)->send(new AccessGrantedExistingUser($nome, $planId));
                } catch (\Throwable $e) {
                    Log::warning('Mail error: '.$e->getMessage());
                }
            } else {
                $plainPassword = self::generateRandomPassword();
                $user = User::create([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => password_hash($plainPassword, PASSWORD_DEFAULT),
                ]);
                $user->materias()->attach($materias);

                try {
                    Mail::to($email)->send(new WelcomeNewUser($nome, $email, $plainPassword, $valorTotal, $planId));
                } catch (\Throwable $e) {
                    Log::warning('Mail error (WelcomeNewUser): '.$e->getMessage());
                }
            }

            self::upsertPedidoItens($pdo, $pedidoId, $materias, $planId, $planDays, $valorTotal);

            $stmt = $pdo->prepare("UPDATE pedidos SET status = 'completed' WHERE id = :id");
            $stmt->execute([':id' => $pedidoId]);

            try {
                self::markPaymentProcessed($pdo, $mpId, 'approved', $extRef !== '' ? $extRef : null);
            } catch (\PDOException $e) {
                if (! self::isDuplicateKey($e)) {
                    throw $e;
                }
            }

            $pdo->commit();
            Log::info("MP approved_fulfilled mp_id={$mpId} pedido_id={$pedidoId}");

            return ['handled' => true, 'detail' => 'fulfilled'];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Log::error('MP approved_error: '.$e->getMessage());

            return ['handled' => false, 'detail' => 'exception'];
        }
    }

    private static function upsertPedidoItens(PDO $pdo, int $pedidoId, array $materias, string $planId, int $planDays, float $valorTotal): void
    {
        $preco = count($materias) > 0 ? $valorTotal / count($materias) : $valorTotal;
        $stmt = $pdo->prepare(
            'INSERT INTO pedidos_itens (pedido_id, materia_id, plano_id, preco, data_expiracao)
             VALUES (:pedido_id, :materia_id, :plano_id, :preco, :expira)'
        );

        foreach ($materias as $mid) {
            $check = $pdo->prepare('SELECT id FROM pedidos_itens WHERE pedido_id = ? AND materia_id = ? LIMIT 1');
            $check->execute([$pedidoId, $mid]);
            if ($check->fetch()) {
                continue;
            }

            $expira = Carbon::now()->addDays($planDays)->format('Y-m-d');

            $stmt->execute([
                ':pedido_id' => $pedidoId,
                ':materia_id' => $mid,
                ':plano_id' => $planId,
                ':preco' => $preco,
                ':expira' => $expira,
            ]);
        }
    }

    private static function updatePedidoNonFinal(PDO $pdo, string $extRef, string $status): void
    {
        if ($extRef === '') {
            return;
        }
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = :st WHERE stripe_payment_id = :r AND status = 'awaiting_payment'");
            $stmt->execute([':st' => $status, ':r' => $extRef]);
        } catch (\Throwable $e) {
            Log::warning('update_pedido_nonfinal: '.$e->getMessage());
        }
    }

    private static function parseMaterias(string $csv): array
    {
        return array_values(array_filter(array_map(fn ($p) => is_numeric(trim($p)) ? (int) trim($p) : null, explode(',', $csv))));
    }

    private static function metadataToArray(null|array|object $metadata): array
    {
        if ($metadata === null) {
            return [];
        }
        if (is_array($metadata)) {
            return $metadata;
        }

        return json_decode(json_encode($metadata), true) ?: [];
    }

    private static function isDuplicateKey(\Throwable $e): bool
    {
        return $e instanceof \PDOException && ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate'));
    }

    public static function generateRandomPassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $nums = '0123456789';
        $special = '!@#$%^&*';
        $all = $upper.$lower.$nums.$special;

        $pwd = $upper[random_int(0, 25)].$lower[random_int(0, 25)].$nums[random_int(0, 9)].$special[random_int(0, 7)];
        for ($i = 4; $i < $length; $i++) {
            $pwd .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($pwd);
    }
}
