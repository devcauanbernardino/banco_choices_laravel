<?php

return [
    'access_token' => env('MP_ACCESS_TOKEN', ''),
    'public_key' => env('MP_PUBLIC_KEY', ''),
    'webhook_secret' => env('MP_WEBHOOK_SECRET', ''),
    'currency_id' => env('MP_CURRENCY_ID', 'ARS'),
    // Sem barra final. Em produção use HTTPS para auto_return e webhooks funcionarem com a API do MP.
    'site_url' => env('SITE_URL', 'http://localhost'),
    // URL pública HTTPS para back_urls + auto_return (pode ser ngrok). Se vazio, usa site_url.
    'checkout_base_url' => env('MP_CHECKOUT_BASE_URL'),
];
