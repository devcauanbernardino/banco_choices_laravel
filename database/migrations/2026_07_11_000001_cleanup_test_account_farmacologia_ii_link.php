<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A migration 2026_07_10_000001 não conseguiu apagar a matéria duplicada
     * "Farmacología II" (slug farmacologia-ii, id 14) porque a conta de teste
     * teste2@bancodechoices.com tinha um vínculo em usuarios_materias com ela
     * (concedido manualmente numa sessão anterior, fora do fluxo do seeder) —
     * o guard de segurança viu esse vínculo e abortou a exclusão, corretamente.
     * Removemos aqui só esse vínculo de teste e tentamos apagar de novo.
     */
    public function up(): void
    {
        $materia = DB::table('materias')->where('slug', 'farmacologia-ii')->first();
        if ($materia === null) {
            return;
        }

        $materiaId = $materia->id;

        $testUserIds = DB::table('users')
            ->whereIn('email', ['teste2@bancodechoices.com', 'teste2local@bancodechoices.com'])
            ->pluck('id');

        DB::table('usuarios_materias')
            ->where('materia_id', $materiaId)
            ->whereIn('usuario_id', $testUserIds)
            ->delete();

        $tablesWithMateriaId = collect(Schema::getTables())
            ->pluck('name')
            ->filter(fn (string $table) => Schema::hasColumn($table, 'materia_id') && $table !== 'catedras');

        foreach ($tablesWithMateriaId as $table) {
            if ($table === 'materias') {
                continue;
            }

            if (DB::table($table)->where('materia_id', $materiaId)->exists()) {
                // Ainda há dados reais vinculados — não é seguro remover automaticamente.
                return;
            }
        }

        DB::table('catedras')->where('materia_id', $materiaId)->delete();
        DB::table('materias')->where('id', $materiaId)->delete();
    }

    public function down(): void
    {
        // Não recria: a matéria era um duplicado, restaurada apenas pelo CatalogoSeeder se necessário.
    }
};
