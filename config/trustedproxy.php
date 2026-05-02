<?php

/**
 * Proxies confiáveis (load balancer, Cloudflare, Forge, etc.).
 * Defina TRUSTED_PROXIES no .env em produção atrás de proxy reverso — por exemplo `*`
 * para confiar no IP imediato (REMOTE_ADDR), valor habitual em PaaS/Docker.
 *
 * @see https://laravel.com/docs/requests#configuring-trusted-proxies
 */
return [
    'proxies' => env('TRUSTED_PROXIES'),
];
