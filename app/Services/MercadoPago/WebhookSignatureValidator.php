<?php

namespace App\Services\MercadoPago;

class WebhookSignatureValidator
{
    public static function validate(string $rawBody, array $server, array $get, string $secret): bool
    {
        $secret = trim($secret);
        if ($secret === '') {
            return false;
        }

        $xSignature = (string) ($server['HTTP_X_SIGNATURE'] ?? '');
        $xRequestId = (string) ($server['HTTP_X_REQUEST_ID'] ?? '');
        if ($xSignature === '') {
            return false;
        }

        $dataId = self::extractDataId($get, $rawBody);
        if ($dataId === null || $dataId === '') {
            return false;
        }

        $ts = null;
        $v1 = null;
        foreach (explode(',', $xSignature) as $part) {
            $part = trim($part);
            if ($part === '') continue;
            $kv = explode('=', $part, 2);
            if (count($kv) !== 2) continue;
            $k = trim($kv[0]);
            $v = trim($kv[1]);
            if ($k === 'ts') $ts = $v;
            if ($k === 'v1') $v1 = $v;
        }

        if ($ts === null || $v1 === null) {
            return false;
        }

        $manifest = 'id:' . $dataId . ';request-id:' . $xRequestId . ';ts:' . $ts . ';';
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $v1);
    }

    private static function extractDataId(array $get, string $rawBody): ?string
    {
        if (isset($get['data_id'])) return (string) $get['data_id'];
        if (isset($get['data.id'])) return (string) $get['data.id'];
        if (isset($get['id'])) return (string) $get['id'];

        if ($rawBody !== '') {
            $json = json_decode($rawBody, true);
            if (is_array($json) && isset($json['data']['id'])) {
                return (string) $json['data']['id'];
            }
        }

        return null;
    }
}
