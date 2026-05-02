<?php

return [
    /*
    | Envia Strict-Transport-Security mesmo quando o pedido não é considerado HTTPS
    | (útil se algo na frente terminar TLS mas o PHP não vir como secure).
    | Por defeito só envia HSTS quando $request->secure() é true (ex.: após TrustProxies).
    */
    'force_hsts' => filter_var(env('SECURITY_FORCE_HSTS', false), FILTER_VALIDATE_BOOLEAN),
];
