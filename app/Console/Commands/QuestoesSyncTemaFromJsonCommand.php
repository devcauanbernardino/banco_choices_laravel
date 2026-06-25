<?php

namespace App\Console\Commands;

use App\Models\Questao;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;

class QuestoesSyncTemaFromJsonCommand extends Command
{
    protected $signature = 'questoes:sync-tema {materia_id : ID da matéria} {--force : Sobrescreve tema já preenchido (padrão: só preenche onde está vazio)}';

    protected $description = 'Copia o campo "tema" do JSON de questões (por overlay_key) para a tabela questoes';

    public function handle(): int
    {
        $materiaId = (int) $this->argument('materia_id');

        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        if ($lista === []) {
            $this->error("Nenhum banco JSON encontrado para a matéria {$materiaId}.");

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $updated = 0;

        foreach ($lista as $overlayKey => $item) {
            $tema = is_array($item) ? ($item['tema'] ?? null) : null;
            if (! is_string($tema) || trim($tema) === '') {
                continue;
            }

            $query = Questao::query()
                ->where('materia_id', $materiaId)
                ->where('overlay_key', $overlayKey);

            if (! $force) {
                $query->where(function ($q) {
                    $q->whereNull('tema')->orWhere('tema', '');
                });
            }

            $updated += $query->update(['tema' => $tema]);
        }

        $this->info("Tema sincronizado: {$updated} questão(ões) atualizada(s) na matéria {$materiaId}.");

        return self::SUCCESS;
    }
}
