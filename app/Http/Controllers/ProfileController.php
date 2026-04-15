<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $materias = $user->materias()->get();

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
        $nome = trim($request->input('nome', ''));

        if ($nome === '') {
            return redirect()->route('profile.show')->with('error', 'nome_vazio');
        }

        $senhaAtual = $request->input('senha_atual', '');
        $novaSenha = $request->input('nova_senha', '');

        if ($senhaAtual !== '' && $novaSenha !== '') {
            if (!Hash::check($senhaAtual, $user->getAuthPassword())) {
                return redirect()->route('profile.show')->with('error', 'senha_incorreta');
            }

            if (strlen($novaSenha) < 8) {
                return redirect()->route('profile.show')->with('error', 'senha_curta');
            }

            $user->senha = Hash::make($novaSenha);
        }

        $user->nome = $nome;
        $user->save();

        return redirect()->route('profile.show')->with('success', true);
    }
}
