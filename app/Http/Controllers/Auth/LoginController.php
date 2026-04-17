<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $skipEmails = array_map('strtolower', config('test_users.skip_default_materias_emails', []));
            if (! in_array(strtolower((string) $user->email), $skipEmails, true)) {
                $user->garantirMaterias([1, 2]);
            }

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
