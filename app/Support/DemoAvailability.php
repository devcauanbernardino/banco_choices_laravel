<?php

namespace App\Support;

use App\Models\Faculdade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DemoAvailability
{
    /**
     * @return array<string, int> slug da faculdade => total de questões is_demo
     */
    public static function demoCountByFaculdadeSlug(): array
    {
        $rows = DB::table('questoes as q')
            ->join('materias as m', 'm.id', '=', 'q.materia_id')
            ->join('agrupamentos as a', 'a.id', '=', 'm.agrupamento_id')
            ->join('faculdades as f', 'f.id', '=', 'a.faculdade_id')
            ->where('q.is_demo', true)
            ->groupBy('f.slug')
            ->selectRaw('f.slug, COUNT(*) as total')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row->slug] = (int) $row->total;
        }

        return $map;
    }

    public static function hasDemo(?string $slug): bool
    {
        if ($slug === null || $slug === '') {
            return false;
        }

        return (self::demoCountByFaculdadeSlug()[$slug] ?? 0) > 0;
    }

    /**
     * @param  Collection<int, Faculdade>  $faculdades
     * @return Collection<int, Faculdade>
     */
    public static function filterWithDemo(Collection $faculdades): Collection
    {
        $counts = self::demoCountByFaculdadeSlug();

        return $faculdades->filter(
            fn (Faculdade $f) => ($counts[$f->slug] ?? 0) > 0
        )->values();
    }

    /**
     * @param  Collection<int, Faculdade>  $faculdades
     */
    public static function firstWithDemo(Collection $faculdades): ?Faculdade
    {
        $counts = self::demoCountByFaculdadeSlug();

        foreach ($faculdades as $faculdade) {
            if (($counts[$faculdade->slug] ?? 0) > 0) {
                return $faculdade;
            }
        }

        return null;
    }
}
