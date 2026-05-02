<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // Faculdades
        foreach ([
            ['nome' => 'UBA', 'slug' => 'uba', 'ordem' => 1],
            ['nome' => 'La Plata', 'slug' => 'la-plata', 'ordem' => 2],
            ['nome' => 'Barceló', 'slug' => 'barcelo', 'ordem' => 3],
            ['nome' => 'CBC', 'slug' => 'cbc', 'ordem' => 4],
        ] as $f) {
            DB::table('faculdades')->updateOrInsert(
                ['slug' => $f['slug']],
                [
                    'nome' => $f['nome'],
                    'ordem' => $f['ordem'],
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $idUba = (int) DB::table('faculdades')->where('slug', 'uba')->value('id');
        $idLp = (int) DB::table('faculdades')->where('slug', 'la-plata')->value('id');
        $idBc = (int) DB::table('faculdades')->where('slug', 'barcelo')->value('id');
        $idCbc = (int) DB::table('faculdades')->where('slug', 'cbc')->value('id');

        // Agrupamentos UBA
        $agrBiomedSlug = 'uba-ciclo-biomedico';
        DB::table('agrupamentos')->updateOrInsert(
            ['faculdade_id' => $idUba, 'slug' => $agrBiomedSlug],
            [
                'nome' => 'Ciclo Biomédico',
                'ordem' => 1,
                'tipo' => 'ciclo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('agrupamentos')->updateOrInsert(
            ['faculdade_id' => $idUba, 'slug' => 'uba-ciclo-clinico'],
            [
                'nome' => 'Ciclo Clínico',
                'ordem' => 2,
                'tipo' => 'ciclo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $agrBioId = (int) DB::table('agrupamentos')->where('slug', $agrBiomedSlug)->value('id');
        $agrClinicoId = (int) DB::table('agrupamentos')->where('slug', 'uba-ciclo-clinico')->value('id');

        // Placeholder agrupamentos (sem matérias)
        foreach ([
            [$idLp, 'la-plata-primer-an', '1º Año', 1],
            [$idBc, 'barcelo-primer-an', '1º Año', 1],
            [$idBc, 'barcelo-segundo-an', '2º Año', 2],
            [$idCbc, 'cbc-primer-an', '1º Año', 1],
            [$idCbc, 'cbc-segundo-an', '2º Año', 2],
        ] as [$fid, $slug, $nome, $ord]) {
            DB::table('agrupamentos')->updateOrInsert(
                ['faculdade_id' => $fid, 'slug' => $slug],
                [
                    'nome' => $nome,
                    'ordem' => $ord,
                    'tipo' => 'ano',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Matérias herdadas com bancos JSON: ids 1 e 2 primeiro (SQLite AUTO increment sync)
        if (! DB::table('materias')->where('id', 1)->exists()) {
            DB::table('materias')->insert([
                'id' => 1,
                'nome' => 'Microbiología y Parasitología',
                'slug' => 'microbiologia-y-parasitologia',
                'agrupamento_id' => $agrBioId,
                'ordem' => 7,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 1)->update([
                'nome' => 'Microbiología y Parasitología',
                'slug' => 'microbiologia-y-parasitologia',
                'agrupamento_id' => $agrBioId,
                'ordem' => 7,
            ]);
        }

        if (! DB::table('materias')->where('id', 2)->exists()) {
            DB::table('materias')->insert([
                'id' => 2,
                'nome' => 'Biología celular',
                'slug' => 'biologia-celular',
                'agrupamento_id' => $agrBioId,
                'ordem' => 9,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 2)->update([
                'nome' => 'Biología celular',
                'slug' => 'biologia-celular',
                'agrupamento_id' => $agrBioId,
                'ordem' => 9,
            ]);
        }

        $ubiomedMaterias = [
            ['slug' => 'histologia', 'nome' => 'Histología', 'ordem' => 1],
            ['slug' => 'embriologia', 'nome' => 'Embriología', 'ordem' => 2],
            ['slug' => 'biologia-molecular-y-genetica', 'nome' => 'Biología Molecular y Genética', 'ordem' => 3],
            ['slug' => 'fisiologia-y-biofisica', 'nome' => 'Fisiología y Biofísica', 'ordem' => 4],
            ['slug' => 'bioquimica', 'nome' => 'Bioquímica', 'ordem' => 5],
        ];

        foreach ($ubiomedMaterias as $m) {
            DB::table('materias')->updateOrInsert(
                ['slug' => $m['slug']],
                [
                    'nome' => $m['nome'],
                    'agrupamento_id' => $agrBioId,
                    'ordem' => $m['ordem'],
                ]
            );
        }

        DB::table('materias')->updateOrInsert(
            ['slug' => 'inmunologia-humana'],
            [
                'nome' => 'Inmunología Humana',
                'agrupamento_id' => $agrBioId,
                'ordem' => 6,
            ]
        );

        foreach ([
            ['slug' => 'patologia', 'nome' => 'Patología'],
            ['slug' => 'farmacologia-i', 'nome' => 'Farmacología I'],
            ['slug' => 'farmacologia-ii', 'nome' => 'Farmacología II'],
            ['slug' => 'medicina-i', 'nome' => 'Medicina I'],
        ] as $i => $row) {
            DB::table('materias')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'nome' => $row['nome'],
                    'agrupamento_id' => $agrClinicoId,
                    'ordem' => $i + 1,
                ]
            );
        }

        $idImmuno = (int) DB::table('materias')->where('slug', 'inmunologia-humana')->value('id');

        foreach ([
            ['inmunologia-humana', 'catedra-i', 'Cátedra I', 1],
            ['inmunologia-humana', 'catedra-ii', 'Cátedra II', 2],
            ['microbiologia-y-parasitologia', 'catedra-i', 'Cátedra I', 1],
            ['microbiologia-y-parasitologia', 'catedra-ii', 'Cátedra II', 2],
        ] as [$mSlug, $cSlug, $cNome, $ord]) {
            $mid = (int) DB::table('materias')->where('slug', $mSlug)->value('id');
            if ($mid <= 0) {
                continue;
            }
            DB::table('catedras')->updateOrInsert(
                ['materia_id' => $mid, 'slug' => $cSlug],
                ['nome' => $cNome, 'ordem' => $ord, 'created_at' => now(), 'updated_at' => now()]
            );
        }

    }

    private function fixSqliteSequence(string $table): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $max = (int) DB::table($table)->max('id');

        DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
        DB::statement("INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)", [$table, max(1, $max)]);
    }
}
