<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Support\SignupFlow;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function selecionarMaterias()
    {
        $materias = Materia::all();

        return view('signup.select-materias', compact('materias'));
    }

    public function storeMaterias(Request $request)
    {
        $request->validate(
            [
                'materias' => 'required|array|min:1',
                'materias.*' => 'integer|exists:materias,id',
            ],
            [
                'materias.required' => __('signup.err.min_materias'),
                'materias.array' => __('signup.err.min_materias'),
                'materias.min' => __('signup.err.min_materias'),
                'materias.*.integer' => __('signup.err.checkout_materias_invalid'),
                'materias.*.exists' => __('signup.err.checkout_materias_invalid'),
            ]
        );

        $request->session()->put('signup_materias', $request->input('materias'));

        return redirect()->route('signup.plano');
    }

    public function selecionarPlano(Request $request)
    {
        $materias = $request->session()->get('signup_materias', []);
        if (empty($materias)) {
            return redirect()->route('signup.materias');
        }

        $materiasInfo = Materia::whereIn('id', $materias)->get();
        $plans = SignupFlow::signupPlansForDisplay();

        return view('signup.select-plano', compact('materiasInfo', 'plans', 'materias'));
    }

    public function storePlano(Request $request)
    {
        $request->validate(
            [
                'plan_id' => 'required|in:monthly,semester,annual',
            ],
            [
                'plan_id.required' => __('signup.err.plan_required'),
                'plan_id.in' => __('signup.err.checkout_invalid_plan'),
            ]
        );

        $request->session()->put('signup_plan', $request->input('plan_id'));

        return redirect()->route('checkout.show');
    }
}
