<?php

return [
    'access_token' => env('MP_ACCESS_TOKEN', ''),
    'public_key' => env('MP_PUBLIC_KEY', ''),
    'webhook_secret' => env('MP_WEBHOOK_SECRET', ''),
    'currency_id' => env('MP_CURRENCY_ID', 'ARS'),
    'site_url' => env('SITE_URL', 'http://localhost'),
];
