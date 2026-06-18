<?php

return [
    'addon_price_per_materia' => 29.90,
    'addon_price_per_materia_brl' => 5.90,
    'addon_fallback_plan_id' => 'weekly',
    'plans' => [
        'daily' => [
            'id' => 'daily',
            'days' => 1,
            'price' => 3990,
            'price_brl' => 14.90,
            'popular' => false,
        ],
        'weekly' => [
            'id' => 'weekly',
            'days' => 7,
            'price' => 9990,
            'price_brl' => 35.90,
            'popular' => true,
        ],
        'monthly' => [
            'id' => 'monthly',
            'days' => 30,
            'price' => 14990,
            'price_brl' => 54.90,
            'popular' => false,
        ],
    ],
];
