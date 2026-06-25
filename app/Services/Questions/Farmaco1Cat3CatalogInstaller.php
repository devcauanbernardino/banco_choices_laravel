<?php

namespace App\Services\Questions;

use Illuminate\Support\Facades\DB;

final class Farmaco1Cat3CatalogInstaller
{
    /**
     * Matéria e agrupamento já existem via CatalogoSeeder (slug farmacologia-i).
     * Aqui garantimos só a cátedra III.
     */
    public static function ensureCatalog(): int
    {
        $materiaId = (int) DB::table('materias')->where('slug', Farmaco1Cat3SectionCatalog::MATERIA_SLUG)->value('id');
        if ($materiaId <= 0) {
            throw new \RuntimeException('Matéria farmacologia-i não encontrada. Execute php artisan db:seed --class=CatalogoSeeder.');
        }

        DB::table('catedras')->updateOrInsert(
            [
                'materia_id' => $materiaId,
                'slug' => Farmaco1Cat3SectionCatalog::CATEDRA_SLUG,
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
        $materiaId = (int) DB::table('materias')->where('slug', Farmaco1Cat3SectionCatalog::MATERIA_SLUG)->value('id');

        return DB::table('catedras')
            ->where('materia_id', $materiaId)
            ->where('slug', Farmaco1Cat3SectionCatalog::CATEDRA_SLUG)
            ->value('id');
    }
}
