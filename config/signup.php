<?php

return [
    'addon_price_per_materia' => 29.90,
    'addon_fallback_plan_id' => 'semester',
    'plans' => [
        'monthly' => [
            'id' => 'monthly',
            'days' => 30,
            'price' => 29.90,
            'popular' => false,
        ],
        'semester' => [
            'id' => 'semester',
            'days' => 180,
            'price' => 119.90,
            'popular' => true,
        ],
        'annual' => [
            'id' => 'annual',
            'days' => 365,
            'price' => 199.90,
            'popular' => false,
        ],
    ],
];
