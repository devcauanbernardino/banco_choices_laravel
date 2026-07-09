<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove a matéria "Farmacología II" (slug farmacologia-ii) duplicada e sem conteúdo,
     * já que a matéria 5 (slug farmacologia-ii-catedra-3) passou a se chamar apenas
     * "Farmacología II". Só apaga se não houver nenhum vínculo (compra, questão, progresso etc.).
     */
    public function up(): void
    {
        $materia = DB::table('materias')->where('slug', 'farmacologia-ii')->first();
        if ($materia === null) {
            return;
        }

        $materiaId = $materia->id;

        $tablesWithMateriaId = collect(Schema::getTables())
            ->pluck('name')
            ->filter(fn (string $table) => Schema::hasColumn($table, 'materia_id'));

        foreach ($tablesWithMateriaId as $table) {
            if ($table === 'materias') {
                continue;
            }

            if (DB::table($table)->where('materia_id', $materiaId)->exists()) {
                // Há dados vinculados a esta matéria — não é seguro remover automaticamente.
                return;
            }
        }

        DB::table('catedras')->where('materia_id', $materiaId)->delete();
        DB::table('materias')->where('id', $materiaId)->delete();
    }

    public function down(): void
    {
        // Não recria: a matéria era um duplicado vazio, restaurada apenas pelo CatalogoSeeder se necessário.
    }
};
