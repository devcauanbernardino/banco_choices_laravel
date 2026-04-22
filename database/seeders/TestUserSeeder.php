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
     * Senha: BancoTeste2026#Local
     */
    public function run(): void
    {
        $email = 'teste@bancodechoices.local';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nome' => 'Usuário Teste',
                'senha' => Hash::make('BancoTeste2026#Local'),
            ]
        );

        $user->materias()->syncWithoutDetaching([1, 2]);
    }
}
