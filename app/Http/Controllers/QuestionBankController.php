<?php

namespace App\Http\Controllers;

use App\Support\SimulationSession;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $materias = $usuario->materiasUnicas();
        $simuladoEmAndamento = SimulationSession::resumoAtual();

        return view('questionbank.index', compact('usuario', 'materias', 'simuladoEmAndamento'));
    }
}
