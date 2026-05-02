<?php

namespace App\Services\Referral;

use App\Models\User;

class CodigoService
{
    private const INTENTOS_MAX = 5;

    /** @return non-empty-string */
    public static function gerarUnicoPara(User $user): string
    {
        $pref = trim((string) config('referral.cupom_prefix', 'BC-'));
        $pref = $pref !== '' ? $pref : 'BC-';

        for ($t = 0; $t < self::INTENTOS_MAX; $t++) {
            $rnd = '';
            try {
                for ($k = 0; $k < 6; $k++) {
                    $pool = random_int(0, 35);
                    $chr = $pool < 10 ? (string) $pool : chr(65 + ($pool - 10));
                    $rnd .= $chr;
                }
            } catch (\Throwable) {
                $rnd = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            }

            $code = strtoupper($pref.$rnd);
            $exists = User::query()->where('codigo_cupom', $code)->exists();
            if (! $exists) {
                return $code;
            }
        }

        return strtoupper($pref.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)));
    }
}
