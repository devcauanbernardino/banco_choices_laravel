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
     * E-mail: teste@bancodechoices.com (domínio real — evita bloqueio em alguns validadores)
     * Senha: BancoTeste2026#Local
     */
    public function run(): void
    {
        $password = Hash::make('BancoTeste2026#Local');
        $payload = [
            'nome' => 'Usuário Teste',
            'senha' => $password,
        ];

        foreach (['teste@bancodechoices.com', 'teste@bancodechoices.local'] as $email) {
            $user = User::updateOrCreate(
                ['email' => $email],
                $payload
            );

            try {
                $user->materias()->syncWithoutDetaching([1, 2, 5]);
            } catch (\Throwable) {
                // Catálogo ainda não migrado — utilizador continua válido para login
            }
        }
    }
}
