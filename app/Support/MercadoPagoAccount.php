<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Resolve qual conta do Mercado Pago usar: locale 'pt_BR' -> conta BR (BRL, Pix nativo do Checkout Pro);
 * qualquer outro locale -> conta AR (ARS).
 */
class MercadoPagoAccount
{
    /**
     * @return array{access_token: string, public_key: string, currency_id: string, webhook_secret: string}
     */
    public static function resolveForCurrentLocale(): array
    {
        return self::resolveForLocale(App::getLocale());
    }

    /**
     * @return array{access_token: string, public_key: string, currency_id: string, webhook_secret: string}
     */
    public static function resolveForLocale(string $locale): array
    {
        $key = $locale === 'pt_BR' ? 'br' : 'ar';

        return config("mercadopago.accounts.{$key}");
    }

    /**
     * Todas as contas configuradas (usado pelo webhook, que não sabe de antemão qual conta originou o pagamento).
     *
     * @return array<string, array{access_token: string, public_key: string, currency_id: string, webhook_secret: string}>
     */
    public static function all(): array
    {
        return config('mercadopago.accounts', []);
    }
}
