<?php

namespace App\Support;

/**
 * Mensagens de erro específicas para o fluxo de checkout (sessão, plano, Mercado Pago).
 */
final class CheckoutErrorMessages
{
    public static function forDraftReason(string $reason): string
    {
        $key = match ($reason) {
            'no_draft' => 'signup.err.checkout_no_draft',
            'draft_expired' => 'signup.err.checkout_draft_expired',
            'order_mismatch' => 'signup.err.checkout_order_mismatch',
            'invalid_plan' => 'signup.err.checkout_invalid_plan',
            'duration_mismatch' => 'signup.err.checkout_duration_mismatch',
            'materias_invalid' => 'signup.err.checkout_materias_invalid',
            'materias_mismatch' => 'signup.err.checkout_materias_mismatch',
            'price_mismatch' => 'signup.err.checkout_price_mismatch',
            'draft_total_mismatch' => 'signup.err.checkout_draft_total_mismatch',
            'plan_draft_mismatch' => 'signup.err.checkout_plan_draft_mismatch',
            'user_mismatch' => 'signup.err.checkout_user_mismatch',
            'unit_price_mismatch' => 'signup.err.checkout_unit_price_mismatch',
            'not_logged_in' => 'signup.err.checkout_not_logged_in',
            default => 'signup.err.payment_failed',
        };

        return __($key);
    }

    public static function planNotFound(): string
    {
        return __('signup.err.checkout_plan_not_found');
    }

    public static function mercadopagoNotConfigured(): string
    {
        return __('signup.err.mercadopago_not_configured');
    }

    public static function mercadopagoPreferenceFailed(): string
    {
        return __('signup.err.mercadopago_preference_failed');
    }
}
