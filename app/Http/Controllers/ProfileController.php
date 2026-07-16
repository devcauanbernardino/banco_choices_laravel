<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $materias = $user->materiasUnicas();

        $stats = DB::table('historico_simulados')
            ->where('usuario_id', $user->id)
            ->selectRaw('COUNT(id) as simulados, SUM(total_questoes) as questoes, SUM(acertos) as acertos')
            ->first();

        $totalQuestoes = (int) ($stats->questoes ?? 0);
        $totalAcertos = (int) ($stats->acertos ?? 0);
        $mediaGeral = $totalQuestoes > 0 ? round(($totalAcertos / $totalQuestoes) * 100, 1) : 0;

        return view('profile.show', [
            'usuario' => $user,
            'materias' => $materias,
            'totalSimulados' => (int) ($stats->simulados ?? 0),
            'totalQuestoes' => $totalQuestoes,
            'mediaGeral' => $mediaGeral,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $nome = trim((string) $request->input('nome', ''));

        if ($nome === '') {
            return redirect()->route('profile.show')->with('error', __('perfil.err.nome_vazio'));
        }

        $user->nome = $nome;
        $user->save();

        return redirect()->route('profile.show')->with('success', __('perfil.flash_ok'));
    }
}
