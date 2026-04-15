<?php

namespace App\Support;

/**
 * Planos e regras do fluxo de cadastro / addon (espelha config/signup_flow.php do legado).
 */
class SignupFlow
{
    public static function addonPlanFallbackId(): string
    {
        return (string) config('signup.addon_fallback_plan_id', 'semester');
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
            'monthly' => [
                'signup.plan.monthly.f1',
                'signup.plan.monthly.f2',
                'signup.plan.monthly.f3',
                'signup.plan.monthly.f4',
            ],
            'semester' => [
                'signup.plan.semester.f1',
                'signup.plan.semester.f2',
                'signup.plan.semester.f3',
                'signup.plan.semester.f4',
                'signup.plan.semester.f5',
            ],
            'annual' => [
                'signup.plan.annual.f1',
                'signup.plan.annual.f2',
                'signup.plan.annual.f3',
                'signup.plan.annual.f4',
                'signup.plan.annual.f5',
                'signup.plan.annual.f6',
            ],
            default => [],
        };

        $badge = match ($id) {
            'semester' => __('signup.plan.semester.badge'),
            'annual' => __('signup.plan.annual.badge'),
            default => null,
        };

        return [
            'id' => $def['id'],
            'name' => __("signup.plan.{$id}.name"),
            'duration' => __("signup.plan.{$id}.duration"),
            'durationDays' => $days,
            'price' => (float) $def['price'],
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
        $order = ['monthly', 'semester', 'annual'];
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
