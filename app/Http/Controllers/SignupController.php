<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Support\QuestionBankLocator;
use App\Support\SignupFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignupController extends Controller
{
    public function selecionarMaterias()
    {
        $presetMateriaId = (int) request()->query('materia_id', 0);

        if ($presetMateriaId > 0 && (
            ! DB::table('materias')->where('id', $presetMateriaId)->exists()
            || ! QuestionBankLocator::hasBank($presetMateriaId)
        )) {
            $presetMateriaId = 0;
        }

        return view('signup.select-materias', compact('presetMateriaId'));
    }

    public function storeMaterias(Request $request)
    {
        $raw = (array) $request->input('materias', []);
        $ids = [];
        foreach ($raw as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        if ($ids !== []) {
            $valid = DB::table('materias')->whereIn('id', $ids)->pluck('id')->all();
            $ids = array_values(array_intersect($ids, array_map('intval', $valid)));
        }

        if ($ids === []) {
            return redirect()->back()->withErrors([
                'materias' => __('signup.err.min_materias'),
            ])->withInput();
        }

        $withBank = QuestionBankLocator::filterIdsWithBank($ids);
        if ($withBank === []) {
            return redirect()->back()->withErrors([
                'materias' => __('signup.err.materia_sem_banco'),
            ])->withInput();
        }
        if (count($withBank) < count($ids)) {
            return redirect()->back()->withErrors([
                'materias' => __('signup.err.materia_sem_banco'),
            ])->withInput();
        }
        $ids = $withBank;

        $request->session()->put('signup_materias', $ids);

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
