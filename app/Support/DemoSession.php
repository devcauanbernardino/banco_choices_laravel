<?php

namespace App\Support;

class DemoSession
{
    private const KEY = 'demo_simulado';

    public function init(array $data): void
    {
        session([self::KEY => $data]);
    }

    public function isActive(): bool
    {
        return session()->has(self::KEY);
    }

    public function get(string $key): mixed
    {
        return session(self::KEY.'.'.$key);
    }

    public function set(string $key, mixed $value): void
    {
        session([self::KEY.'.'.$key => $value]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }
}
