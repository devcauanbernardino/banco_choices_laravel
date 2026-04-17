<?php

return [
    'access_token' => env('MP_ACCESS_TOKEN', ''),
    'public_key' => env('MP_PUBLIC_KEY', ''),
    'webhook_secret' => env('MP_WEBHOOK_SECRET', ''),
    'currency_id' => env('MP_CURRENCY_ID', 'ARS'),

    /*
    | Base pública do site (sem barra final), usada se MP_CHECKOUT_BASE_URL estiver vazio.
    | Prioridade: SITE_URL → APP_URL → http://localhost
    |
    | Testes só em localhost (ex.: php artisan serve → http://127.0.0.1:8000):
    | - Use credenciais de TESTE do Mercado Pago (MP_ACCESS_TOKEN começa com TEST-).
    | - APP_URL deve ser a mesma origem que você abre no navegador (alinhado ao artisan serve).
    | - http sem HTTPS: a API do MP não envia auto_return; o botão "Voltar ao site" pode não aparecer,
    |   mas após pagar o MP costuma redirecionar para back_urls.success com ?payment_id=... (confira a URL).
    | - Para retorno automático + webhook: use um túnel HTTPS (ngrok, Cloudflare Tunnel)
    |   e defina MP_CHECKOUT_BASE_URL=https://seu-subdominio.ngrok-free.app (mesma origem que o browser abre).
    | - E-mail de boas-vindas: com MAIL_MAILER=log o conteúdo vai para storage/logs/laravel.log
    */
    'site_url' => rtrim((string) (env('SITE_URL') ?: env('APP_URL', 'http://localhost')), '/'),

    // Sem barra final. Sobrescreve site_url nas back_urls / webhook quando definido (recomendado com HTTPS em dev).
    'checkout_base_url' => env('MP_CHECKOUT_BASE_URL'),
];
