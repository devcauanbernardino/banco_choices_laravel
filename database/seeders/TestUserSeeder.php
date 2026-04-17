<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Usuário para login manual / QA (mesmas regras de senha do cadastro).
     *
     * E-mail: teste@bancodechoices.local
     * Senha: ChoicesLocal2026!
     */
    public function run(): void
    {
        $email = 'teste@bancodechoices.local';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nome' => 'Usuário Teste',
                'senha' => Hash::make('ChoicesLocal2026!'),
            ]
        );

        $user->materias()->syncWithoutDetaching([1, 2]);
    }
}
