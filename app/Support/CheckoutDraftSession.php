<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Rascunhos de checkout na sessão (validação server-side do POST), espelhando checkout_session.php.
 */
class CheckoutDraftSession
{
    public const SIGNUP_KEY = 'checkout_draft';

    public const ADDON_KEY = 'checkout_draft_addon';

    /**
     * @param  list<int>  $materiasIds
     */
    public static function saveSignupDraft(
        string $orderId,
        string $planId,
        int $durationDays,
        float $unitPrice,
        array $materiasIds,
        float $expectedTotal
    ): void {
        $ids = array_values(array_unique(array_map('intval', $materiasIds)));
        sort($ids);
        session([self::SIGNUP_KEY => [
            'order_id' => $orderId,
            'plan_id' => $planId,
            'duration_days' => $durationDays,
            'unit_price' => $unitPrice,
            'materias_ids' => $ids,
            'expected_total' => $expectedTotal,
            'created' => time(),
        ]]);
    }

    /**
     * @return array{ok: true}|array{ok: false, reason: string}
     */
    public static function validateSignupPost(Request $request): array
    {
        $draft = session(self::SIGNUP_KEY);
        if (! is_array($draft)) {
            return ['ok' => false, 'reason' => 'no_draft'];
        }

        $created = (int) ($draft['created'] ?? 0);
        if ($created <= 0 || (time() - $created) > 7200) {
            session()->forget(self::SIGNUP_KEY);

            return ['ok' => false, 'reason' => 'draft_expired'];
        }

        $orderId = trim((string) $request->input('order_id', ''));
        if ($orderId === '' || $orderId !== (string) ($draft['order_id'] ?? '')) {
            return ['ok' => false, 'reason' => 'order_mismatch'];
        }

        $planId = (string) $request->input('plan_id', '');
        $def = SignupFlow::signupPlanForDisplayById($planId);
        if ($def === null) {
            return ['ok' => false, 'reason' => 'invalid_plan'];
        }

        $duration = (int) $request->input('plan_duration_days', 0);
        if ($duration !== (int) $def['durationDays']) {
            return ['ok' => false, 'reason' => 'duration_mismatch'];
        }

        $materiasRaw = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('materias', '')))));
        $materiasIds = array_values(array_unique(array_map('intval', $materiasRaw)));
        sort($materiasIds);
        $expectedIds = $draft['materias_ids'] ?? [];
        if (! is_array($expectedIds)) {
            return ['ok' => false, 'reason' => 'materias_invalid'];
        }
        $expectedIds = array_values(array_map('intval', $expectedIds));
        sort($expectedIds);
        if ($materiasIds !== $expectedIds) {
            return ['ok' => false, 'reason' => 'materias_mismatch'];
        }

        if ((string) ($draft['plan_id'] ?? '') !== $planId) {
            return ['ok' => false, 'reason' => 'plan_draft_mismatch'];
        }

        return ['ok' => true];
    }

    public static function clearSignupDraft(): void
    {
        session()->forget(self::SIGNUP_KEY);
    }

    /**
     * @param  list<int>  $materiasIds
     */
    public static function saveAddonDraft(
        string $orderId,
        string $planId,
        int $durationDays,
        float $unitPrice,
        array $materiasIds,
        float $expectedTotal,
        int $userId
    ): void {
        $ids = array_values(array_unique(array_map('intval', $materiasIds)));
        sort($ids);
        session([self::ADDON_KEY => [
            'order_id' => $orderId,
            'plan_id' => $planId,
            'duration_days' => $durationDays,
            'unit_price' => $unitPrice,
            'materias_ids' => $ids,
            'expected_total' => $expectedTotal,
            'user_id' => $userId,
            'created' => time(),
        ]]);
    }

    /**
     * @return array{ok: true}|array{ok: false, reason: string}
     */
    public static function validateAddonPost(Request $request, int $userId): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'reason' => 'not_logged_in'];
        }

        $draft = session(self::ADDON_KEY);
        if (! is_array($draft)) {
            return ['ok' => false, 'reason' => 'no_draft'];
        }
        if ((int) ($draft['user_id'] ?? 0) !== $userId) {
            return ['ok' => false, 'reason' => 'user_mismatch'];
        }

        $created = (int) ($draft['created'] ?? 0);
        if ($created <= 0 || (time() - $created) > 7200) {
            session()->forget(self::ADDON_KEY);

            return ['ok' => false, 'reason' => 'draft_expired'];
        }

        $orderId = trim((string) $request->input('order_id', ''));
        if ($orderId === '' || $orderId !== (string) ($draft['order_id'] ?? '')) {
            return ['ok' => false, 'reason' => 'order_mismatch'];
        }

        $planId = (string) $request->input('plan_id', '');
        $def = SignupFlow::signupPlanForDisplayById($planId);
        if ($def === null) {
            return ['ok' => false, 'reason' => 'invalid_plan'];
        }

        $duration = (int) $request->input('plan_duration_days', 0);
        if ($duration !== (int) $def['durationDays']) {
            return ['ok' => false, 'reason' => 'duration_mismatch'];
        }

        $materiasRaw = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('materias', '')))));
        $materiasIds = array_values(array_unique(array_map('intval', $materiasRaw)));
        sort($materiasIds);
        $expectedIds = $draft['materias_ids'] ?? [];
        if (! is_array($expectedIds)) {
            return ['ok' => false, 'reason' => 'materias_invalid'];
        }
        $expectedIds = array_values(array_map('intval', $expectedIds));
        sort($expectedIds);
        if ($materiasIds !== $expectedIds) {
            return ['ok' => false, 'reason' => 'materias_mismatch'];
        }

        $unit = SignupFlow::addonPricePerMateria();
        $draftUnit = (float) ($draft['unit_price'] ?? 0);
        if (abs($draftUnit - $unit) > 0.02) {
            return ['ok' => false, 'reason' => 'unit_price_mismatch'];
        }

        if ((string) ($draft['plan_id'] ?? '') !== $planId) {
            return ['ok' => false, 'reason' => 'plan_draft_mismatch'];
        }

        return ['ok' => true];
    }

    public static function clearAddonDraft(): void
    {
        session()->forget(self::ADDON_KEY);
    }
}
