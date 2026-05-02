<?php

return [

    'referrer_credit_percent' => (float) env('REFERRER_CREDIT_PERCENT', 10),

    'desconto_referido_percent' => (float) env('REFERRAL_REFERIDO_DISCOUNT_PERCENT', 10),

    'minimo_saque' => (float) env('REFERRAL_MIN_SAQUE_ARS', 10000),

    'cupom_prefix' => env('REFERRAL_CUPOM_PREFIX', 'BC-'),

];
