<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if (! $user->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
            'confirma_senha' => 'required|same:nova_senha',
        ]);

        $user = Auth::user();
        $user->senha = Hash::make((string) $request->input('nova_senha'));
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', __('perfil.flash_ok'));
    }
}
