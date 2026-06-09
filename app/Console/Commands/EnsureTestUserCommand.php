<?php

namespace App\Console\Commands;

use App\Models\Materia;
use App\Models\User;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureTestUserCommand extends Command
{
    protected $signature = 'bancodechoices:ensure-test-user';

    protected $description = 'Cria ou atualiza o utilizador de teste com acesso a todas as matérias com banco de questões';

    public function handle(): int
    {
        $hash = Hash::make('BancoTeste2026#Local');
        $payload = [
            'nome' => 'Usuário Teste',
            'senha' => $hash,
        ];

        $materiaIds = QuestionBankLocator::filterIdsWithBank(
            Materia::query()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
        if ($materiaIds === []) {
            $materiaIds = QuestionBankLocator::allMateriaIdsWithBank();
        }

        foreach (['teste@bancodechoices.com', 'teste@bancodechoices.local'] as $email) {
            $user = User::updateOrCreate(['email' => $email], $payload);

            try {
                $user->materias()->sync($materiaIds);
                $this->line("OK {$email} (id {$user->id}) — matérias: ".implode(', ', $materiaIds));
            } catch (\Throwable $e) {
                $this->warn("Matérias não vinculadas para {$email}: ".$e->getMessage());
            }
        }

        $this->info('Senha: BancoTeste2026#Local');

        return self::SUCCESS;
    }
}
