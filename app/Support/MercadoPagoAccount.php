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
        return self::resolveForKey($locale === 'pt_BR' ? 'br' : 'ar');
    }

    /**
     * @return array{access_token: string, public_key: string, currency_id: string, webhook_secret: string}
     */
    public static function resolveForKey(string $key): array
    {
        $key = $key === 'br' ? 'br' : 'ar';

        return config("mercadopago.accounts.{$key}");
    }

    /**
     * Conta sugerida como padrão no checkout, com base no idioma atual (o usuário pode trocar manualmente).
     */
    public static function defaultKeyForCurrentLocale(): string
    {
        return App::getLocale() === 'pt_BR' ? 'br' : 'ar';
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
