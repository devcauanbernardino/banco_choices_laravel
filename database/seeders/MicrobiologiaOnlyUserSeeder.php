<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MicrobiologiaOnlyUserSeeder extends Seeder
{
    /**
     * Conta com acesso apenas à disciplina Microbiologia (para testes).
     *
     * E-mail: microbiologia.solo@bancodechoices.local
     * Senha: MicroBio2026#Solo
     */
    public function run(): void
    {
        $microId = Materia::query()->where('slug', 'microbiologia-y-parasitologia')->value('id')
            ?? Materia::query()->where('id', 1)->value('id');
        if ($microId === null) {
            $this->command?->warn('Matéria Microbiología y Parasitología não encontrada. Execute CatalogoSeeder primeiro.');

            return;
        }

        $email = 'microbiologia.solo@bancodechoices.local';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nome' => 'Conta só Microbiologia',
                'senha' => Hash::make('MicroBio2026#Solo'),
            ]
        );

        $user->materias()->sync([$microId]);
    }
}
