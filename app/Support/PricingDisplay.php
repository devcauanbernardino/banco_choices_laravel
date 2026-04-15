<?php

namespace App\Support;

/**
 * Formatação ARS para telas de checkout (espelho simples de pricing_display.php).
 */
class PricingDisplay
{
    public static function formatArsForCheckout(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }

    public static function formatArsSettlement(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}
