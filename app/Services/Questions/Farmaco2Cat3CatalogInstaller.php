<?php

namespace App\Services\Questions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class Farmaco2Cat3CatalogInstaller
{
    public static function ensureCatalog(): int
    {
        $agrClinicoId = (int) DB::table('agrupamentos')->where('slug', 'uba-ciclo-clinico')->value('id');
        if ($agrClinicoId <= 0) {
            throw new \RuntimeException('Agrupamento uba-ciclo-clinico não encontrado. Execute php artisan db:seed --class=CatalogoSeeder.');
        }

        $materiaId = Farmaco2Cat3SectionCatalog::MATERIA_ID;
        $payload = [
            'nome' => 'Farmacología II',
            'slug' => Farmaco2Cat3SectionCatalog::MATERIA_SLUG,
            'agrupamento_id' => $agrClinicoId,
            'ordem' => 3,
        ];

        if (DB::table('materias')->where('id', $materiaId)->exists()) {
            DB::table('materias')->where('id', $materiaId)->update($payload);
        } else {
            DB::table('materias')->insert(array_merge(['id' => $materiaId], $payload));
            self::fixSqliteSequence('materias');
        }

        DB::table('catedras')->updateOrInsert(
            [
                'materia_id' => $materiaId,
                'slug' => Farmaco2Cat3SectionCatalog::CATEDRA_SLUG,
            ],
            [
                'nome' => 'Cátedra III',
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $materiaId;
    }

    public static function catedraId(): ?int
    {
        return DB::table('catedras')
            ->where('materia_id', Farmaco2Cat3SectionCatalog::MATERIA_ID)
            ->where('slug', Farmaco2Cat3SectionCatalog::CATEDRA_SLUG)
            ->value('id');
    }

    private static function fixSqliteSequence(string $table): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $max = (int) DB::table($table)->max('id');
        DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
        DB::statement('INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)', [$table, max(1, $max)]);
    }
}
