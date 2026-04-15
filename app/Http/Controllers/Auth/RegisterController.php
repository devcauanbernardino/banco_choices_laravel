<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'senha' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/',
            ],
            'confirma-senha' => 'required|same:senha',
            'materias' => 'required|array|min:1',
            'materias.*' => 'integer|exists:materias,id',
        ]);

        $user = User::create([
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'senha' => Hash::make($request->input('senha')),
        ]);

        $user->materias()->attach($request->input('materias'));

        return redirect()->route('login')->with('registered', true);
    }
}
