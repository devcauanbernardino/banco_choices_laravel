<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureTestUserCommand extends Command
{
    protected $signature = 'bancodechoices:ensure-test-user';

    protected $description = 'Cria ou atualiza o utilizador de teste (login QA em produção)';

    public function handle(): int
    {
        $hash = Hash::make('BancoTeste2026#Local');
        $payload = [
            'nome' => 'Usuário Teste',
            'senha' => $hash,
        ];

        foreach (['teste@bancodechoices.com', 'teste@bancodechoices.local'] as $email) {
            $user = User::updateOrCreate(['email' => $email], $payload);

            try {
                $user->materias()->syncWithoutDetaching([1, 2, 5]);
            } catch (\Throwable $e) {
                $this->warn("Matérias não vinculadas para {$email}: ".$e->getMessage());
            }

            $this->line("OK {$email} (id {$user->id})");
        }

        $this->info('Senha: BancoTeste2026#Local');

        return self::SUCCESS;
    }
}
