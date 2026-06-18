<?php

namespace App\Support;

/**
 * Planos e regras do fluxo de cadastro / addon (espelha config/signup_flow.php do legado).
 */
class SignupFlow
{
    public static function addonPlanFallbackId(): string
    {
        return (string) config('signup.addon_fallback_plan_id', 'weekly');
    }

    public static function addonPricePerMateria(): float
    {
        $env = env('ADDON_PRICE_PER_MATERIA');
        if ($env !== null && $env !== '') {
            $v = str_replace(',', '.', trim((string) $env));
            if (is_numeric($v)) {
                $f = (float) $v;
                if ($f > 0) {
                    return $f;
                }
            }
        }

        return (float) config('signup.addon_price_per_materia', 29.90);
    }

    public static function addonPricePerMateriaBrl(): float
    {
        return (float) config('signup.addon_price_per_materia_brl', 5.90);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function signupPlanForDisplayById(string $id): ?array
    {
        $plans = config('signup.plans', []);
        $id = strtolower(trim($id));
        if (! isset($plans[$id])) {
            return null;
        }

        $def = $plans[$id];
        $days = (int) ($def['days'] ?? 0);

        $featureKeys = match ($id) {
            'daily' => [
                'signup.plan.daily.f1',
                'signup.plan.daily.f2',
                'signup.plan.daily.f3',
            ],
            'weekly' => [
                'signup.plan.weekly.f1',
                'signup.plan.weekly.f2',
                'signup.plan.weekly.f3',
                'signup.plan.weekly.f4',
            ],
            'monthly' => [
                'signup.plan.monthly.f1',
                'signup.plan.monthly.f2',
                'signup.plan.monthly.f3',
                'signup.plan.monthly.f4',
                'signup.plan.monthly.f5',
            ],
            default => [],
        };

        $badge = match ($id) {
            'weekly' => __('signup.plan.weekly.badge'),
            'monthly' => __('signup.plan.monthly.badge'),
            default => null,
        };

        return [
            'id' => $def['id'],
            'name' => __("signup.plan.{$id}.name"),
            'duration' => __("signup.plan.{$id}.duration"),
            'durationDays' => $days,
            'price' => (float) $def['price'],
            'priceBrl' => (float) ($def['price_brl'] ?? $def['price']),
            'description' => __("signup.plan.{$id}.desc"),
            'features' => array_map(static fn (string $k) => __($k), $featureKeys),
            'badge' => $badge,
            'popular' => (bool) ($def['popular'] ?? false),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function signupPlansForDisplay(): array
    {
        $order = ['daily', 'weekly', 'monthly'];
        $out = [];
        foreach ($order as $id) {
            $p = self::signupPlanForDisplayById($id);
            if ($p !== null) {
                $out[] = $p;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public static function addonResolvePlanForExtraMaterias(?string $ultimoPlanoId): array
    {
        if ($ultimoPlanoId !== null && $ultimoPlanoId !== '') {
            $p = self::signupPlanForDisplayById($ultimoPlanoId);
            if ($p !== null) {
                return $p;
            }
        }

        $fb = self::signupPlanForDisplayById(self::addonPlanFallbackId());
        if ($fb === null) {
            throw new \RuntimeException('addon_plan_fallback_id inválido');
        }

        return $fb;
    }
}
