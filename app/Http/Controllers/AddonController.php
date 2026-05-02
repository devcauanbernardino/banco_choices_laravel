<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Support\CheckoutDraftSession;
use App\Support\Countries;
use App\Support\PricingDisplay;
use App\Support\SignupFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    public function materias(Request $request)
    {
        $user = Auth::user();
        $ownedIds = array_values(array_unique($user->materias()->pluck('materias.id')->all()));

        $idsNaoPossuidos = Materia::query()
            ->when($ownedIds !== [], fn ($q) => $q->whereNotIn('id', $ownedIds))
            ->pluck('id')
            ->all();

        if ($request->isMethod('post')) {
            $request->validate([
                'materias' => 'required|array|min:1',
                'materias.*' => 'integer|exists:materias,id',
            ]);

            $allowed = array_map('intval', $idsNaoPossuidos);
            $picked = array_values(array_unique(array_filter(
                array_map('intval', $request->input('materias', [])),
                fn (int $id) => $id > 0 && in_array($id, $allowed, true)
            )));

            if ($picked === []) {
                return redirect()->route('addon.materias')->with('error', __('addon.select_min'));
            }

            $ultimoPlanoId = $user->buscarUltimoPlanoIdParaUsuarioId((int) $user->id);
            $plan = SignupFlow::addonResolvePlanForExtraMaterias($ultimoPlanoId);

            $request->session()->put('addon_materias', $picked);
            $request->session()->put('addon_plan', $plan);

            return redirect()->route('addon.checkout');
        }

        $excludeOwnedCsv = implode(',', $ownedIds);

        return view('addon.materias', [
            'excludeOwnedCsv' => $excludeOwnedCsv,
            'temMateriasCompraveis' => count($idsNaoPossuidos) > 0,
        ]);
    }

    /**
     * Legado: URL antiga; hoje o plano é resolvido automaticamente.
     */
    public function planoRedirect(Request $request)
    {
        $user = Auth::user();
        $materiasIds = array_values(array_filter(array_map('intval', (array) $request->session()->get('addon_materias', []))));

        if ($materiasIds === []) {
            return redirect()->route('addon.materias');
        }

        $ultimoPlanoId = $user->buscarUltimoPlanoIdParaUsuarioId((int) $user->id);
        $request->session()->put('addon_plan', SignupFlow::addonResolvePlanForExtraMaterias($ultimoPlanoId));

        return redirect()->route('addon.checkout');
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();

        $materiasIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $request->session()->get('addon_materias', [])),
            fn (int $id) => $id > 0
        )));

        if ($materiasIds === []) {
            return redirect()->route('addon.materias')->with('error', __('addon.select_min'));
        }

        $ownedIds = array_values(array_unique($user->materias()->pluck('materias.id')->all()));
        foreach ($materiasIds as $mid) {
            if (in_array($mid, $ownedIds, true)) {
                $request->session()->forget(['addon_materias', 'addon_plan']);

                return redirect()->route('addon.materias')->with('error', __('addon.select_min'));
            }
        }

        $plan = $request->session()->get('addon_plan');
        if (! is_array($plan) || empty($plan['id'])) {
            $ultimoPlanoId = $user->buscarUltimoPlanoIdParaUsuarioId((int) $user->id);
            $plan = SignupFlow::addonResolvePlanForExtraMaterias($ultimoPlanoId);
            $request->session()->put('addon_plan', $plan);
        }

        $planId = (string) ($plan['id'] ?? '');
        $merged = SignupFlow::signupPlanForDisplayById($planId);
        if ($merged !== null) {
            $plan = array_merge($plan, $merged);
        }

        $materiasInfo = Materia::whereIn('id', $materiasIds)->orderBy('nome')->get();
        if ($materiasInfo->count() !== count($materiasIds)) {
            return redirect()->route('addon.materias')->with('error', __('addon.select_min'));
        }

        $unitPrice = SignupFlow::addonPricePerMateria();
        $totalPrice = $unitPrice * count($materiasIds);
        $orderId = 'ADDON-'.time().'-'.random_int(1000, 9999);

        CheckoutDraftSession::saveAddonDraft(
            $orderId,
            $planId,
            (int) ($plan['durationDays'] ?? 0),
            $unitPrice,
            $materiasIds,
            $totalPrice,
            (int) $user->id
        );

        $request->session()->put('checkout_order_id', $orderId);

        return view('addon.checkout', [
            'materiasInfo' => $materiasInfo,
            'plan' => $plan,
            'planId' => $planId,
            'materiasIds' => $materiasIds,
            'orderId' => $orderId,
            'unitPrice' => $unitPrice,
            'totalPrice' => $totalPrice,
            'totalPriceFormatted' => PricingDisplay::formatArsForCheckout($totalPrice),
            'unitPriceFormatted' => PricingDisplay::formatArsForCheckout($unitPrice),
            'settlementFormatted' => PricingDisplay::formatArsSettlement($totalPrice),
            'user' => $user,
            'countries' => Countries::forSelect(),
        ]);
    }
}
