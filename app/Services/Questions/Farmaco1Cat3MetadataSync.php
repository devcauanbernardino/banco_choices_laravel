<?php

namespace App\Services\Questions;

use App\Models\Questao;
use App\Support\QuestionBankLocator;
use Illuminate\Support\Facades\DB;

final class Farmaco1Cat3MetadataSync
{
    /**
     * Cria linhas em questoes (se faltarem) e preenche parcial, tema, cátedra e fonte a partir de origem_seccion no JSON.
     * O id da matéria é resolvido pelo slug (varia entre ambientes — nunca hardcoded).
     *
     * @return array{inserted: int, updated: int, sem_seccion: int, por_parcial: array<string, int>}
     */
    public static function sync(?int $materiaId = null): array
    {
        $materiaId ??= (int) DB::table('materias')->where('slug', Farmaco1Cat3SectionCatalog::MATERIA_SLUG)->value('id');
        if ($materiaId <= 0) {
            throw new \RuntimeException('Matéria farmacologia-i não encontrada. Execute php artisan db:seed --class=CatalogoSeeder.');
        }

        // Corrige linhas gravadas por engano sob outro materia_id em execuções anteriores (bug de id hardcoded).
        Questao::query()
            ->where('fonte', Farmaco1Cat3SectionCatalog::FONTE)
            ->where('materia_id', '!=', $materiaId)
            ->update(['materia_id' => $materiaId]);

        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        if ($lista === []) {
            throw new \RuntimeException(
                'Banco JSON vazio para matéria '.$materiaId.'. Verifique storage/app/data/questoes_farmaco1_cat3.json'
            );
        }

        $catedraId = Farmaco1Cat3CatalogInstaller::catedraId();
        $inserted = 0;
        $updated = 0;
        $semSeccion = 0;
        $porParcial = [];

        foreach ($lista as $overlayKey => $blob) {
            if (! is_array($blob)) {
                continue;
            }

            $seccion = isset($blob['origem_seccion']) ? trim((string) $blob['origem_seccion']) : '';
            if ($seccion === '') {
                $semSeccion++;

                continue;
            }

            $resolved = Farmaco1Cat3SectionCatalog::resolve($seccion);
            $parcial = $resolved['parcial'];
            $tema = $resolved['tema'];

            $porParcial[$parcial] = ($porParcial[$parcial] ?? 0) + 1;

            $existing = Questao::query()
                ->where('materia_id', $materiaId)
                ->where('overlay_key', $overlayKey)
                ->first();

            $attrs = [
                'materia_id' => $materiaId,
                'catedra_id' => $catedraId,
                'overlay_key' => $overlayKey,
                'parcial' => $parcial,
                'tema' => $tema,
                'fonte' => Farmaco1Cat3SectionCatalog::FONTE,
                'is_demo' => false,
            ];

            if ($existing === null) {
                Questao::query()->create($attrs);
                $inserted++;

                continue;
            }

            $existing->update([
                'catedra_id' => $catedraId,
                'parcial' => $parcial,
                'tema' => $tema,
                'fonte' => Farmaco1Cat3SectionCatalog::FONTE,
            ]);
            $updated++;
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'sem_seccion' => $semSeccion,
            'por_parcial' => $porParcial,
        ];
    }
}
