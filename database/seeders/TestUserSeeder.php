<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\User;
use App\Support\QuestionBankLocator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Usuário para login manual / QA (mesmas regras de senha do cadastro).
     *
     * E-mail: teste@bancodechoices.com (domínio real — evita bloqueio em alguns validadores)
     * Senha: BancoTeste2026#Local
     * Acesso: todas as matérias com banco de questões disponível.
     */
    public function run(): void
    {
        $password = Hash::make('BancoTeste2026#Local');
        $payload = [
            'nome' => 'Usuário Teste',
            'senha' => $password,
        ];

        $materiaIds = QuestionBankLocator::filterIdsWithBank(
            Materia::query()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
        if ($materiaIds === []) {
            $materiaIds = QuestionBankLocator::allMateriaIdsWithBank();
        }

        foreach (['teste@bancodechoices.com', 'teste@bancodechoices.local'] as $email) {
            $user = User::updateOrCreate(
                ['email' => $email],
                $payload
            );

            try {
                $user->materias()->sync($materiaIds);
            } catch (\Throwable) {
                // Catálogo ainda não migrado — utilizador continua válido para login
            }
        }
    }
}
