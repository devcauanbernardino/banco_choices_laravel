<?php

namespace App\Support;

use League\ISO3166\ISO3166;

final class Countries
{
    /**
     * @return array<string, string> alpha2 => English name (sorted by name)
     */
    public static function forSelect(): array
    {
        $iso = new ISO3166;
        $out = [];
        foreach ($iso->iterator(ISO3166::KEY_ALPHA2) as $alpha2 => $row) {
            $out[$alpha2] = $row['name'];
        }
        asort($out, SORT_NATURAL | SORT_FLAG_CASE);

        return $out;
    }

    /**
     * @return list<string>
     */
    public static function validAlpha2Codes(): array
    {
        $codes = [];
        foreach ((new ISO3166)->all() as $row) {
            $codes[] = $row['alpha2'];
        }

        return $codes;
    }
}
