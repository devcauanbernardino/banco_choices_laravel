<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('senha'),
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Garantir matérias padrão (1 e 2)
            $user = Auth::user();
            $user->garantirMaterias([1, 2]);

            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('login')
            ->withInput($request->only('email'))
            ->with('error', 'logininvalido');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
