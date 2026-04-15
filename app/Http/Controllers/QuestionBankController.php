<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $materias = $usuario->materias()->get();

        return view('questionbank.index', compact('usuario', 'materias'));
    }
}
