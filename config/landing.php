<?php

return [
    'stats' => [
        ['numero' => '47.000+', 'label_key' => 'landing.stats.preguntas'],
        ['numero' => 'Excelencia', 'label_key' => 'landing.stats.fuentes'],
        ['numero' => 'Seguimiento', 'label_key' => 'landing.stats.seguimiento'],
        ['numero' => '2.000+', 'label_key' => 'landing.stats.alumnos'],
    ],

    'footer' => [
        'objetivo_key' => 'landing.footer.objetivo',
        'social' => [
            'whatsapp' => env('LANDING_WHATSAPP_URL', ''),
            'instagram' => env('LANDING_INSTAGRAM_URL', ''),
        ],
    ],

    'demo' => [
        'questoes_por_materia' => env('DEMO_QUESTOES_POR_MATERIA', 5),
        'limite_sessoes_por_ip_dia' => env('DEMO_LIMITE_IP_DIA', 3),
        'limite_sessoes_ip_ua_7d' => env('DEMO_LIMITE_IP_UA_7D', 5),
    ],
];
