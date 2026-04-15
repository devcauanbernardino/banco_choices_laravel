<?php

namespace App\Support;

class SimulationSession
{
    private const KEY = 'simulado';

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
        return session(self::KEY . '.' . $key);
    }

    public function set(string $key, mixed $value): void
    {
        session([self::KEY . '.' . $key => $value]);
    }

    public function all(): ?array
    {
        return session(self::KEY);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }
}
