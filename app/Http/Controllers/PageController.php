<?php

namespace App\Http\Controllers;

use App\Support\PricingDisplay;

class PageController extends Controller
{
    public function index()
    {
        $landingPlans = [];
        $raw = config('signup.plans', []);
        foreach (['monthly', 'semester', 'annual'] as $id) {
            if (! isset($raw[$id])) {
                continue;
            }
            $p = $raw[$id];
            $days = max(1, (int) ($p['days'] ?? 30));
            $price = (float) ($p['price'] ?? 0);
            $months = $days / 30.437;
            $perMonth = $months > 0 ? $price / $months : $price;
            $landingPlans[$id] = [
                'id' => (string) ($p['id'] ?? $id),
                'days' => $days,
                'popular' => (bool) ($p['popular'] ?? false),
                'price_total' => $price,
                'price_total_fmt' => PricingDisplay::formatArsForCheckout($price),
                'per_month' => $perMonth,
                'per_month_fmt' => PricingDisplay::formatArsForCheckout(round($perMonth, 2)),
            ];
        }

        return view('pages.index', compact('landingPlans'));
    }
}
