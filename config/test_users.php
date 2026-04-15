<?php

/**
 * E-mails de teste: ao entrar, não recebem automaticamente as matérias padrão (1 e 2).
 * Útil para fluxos E2E sem poluir o vínculo de matérias.
 */
return [
    'skip_default_materias_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TEST_USER_SKIP_DEFAULT_MATERIAS', ''))
    ))),
];
