<?php

return [
    'addon_price_per_materia' => 0.01,
    'addon_fallback_plan_id' => 'semester',
    'plans' => [
        'monthly' => [
            'id' => 'monthly',
            'days' => 30,
            'price' => 0.01,
            'popular' => false,
        ],
        'semester' => [
            'id' => 'semester',
            'days' => 180,
            'price' => 0.01,
            'popular' => true,
        ],
        'annual' => [
            'id' => 'annual',
            'days' => 365,
            'price' => 0.01,
            'popular' => false,
        ],
    ],
];
