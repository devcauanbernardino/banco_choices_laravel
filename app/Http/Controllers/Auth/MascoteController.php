<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MascoteController extends Controller
{
    public const OPCOES = ['robo', 'fantasma', 'gato'];

    public function show()
    {
        $user = Auth::user();
        if ($user->mascote) {
            return redirect()->route('dashboard');
        }

        return view('auth.choose-mascote');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mascote' => 'required|in:'.implode(',', self::OPCOES),
        ]);

        $user = Auth::user();
        $user->mascote = $request->input('mascote');
        $user->save();

        return redirect()->route('dashboard')->with('success', __('mascote.flash_chosen'));
    }
}
