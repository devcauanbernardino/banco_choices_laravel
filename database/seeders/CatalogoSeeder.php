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
        $hasDescricaoCurta = Schema::hasColumn('faculdades', 'descricao_curta');
        foreach ([
            ['nome' => 'Medicina UBA', 'slug' => 'uba', 'ordem' => 1, 'descricao_curta' => 'Universidad de Buenos Aires · Ciclo Biomédico y Clínico'],
            ['nome' => 'Medicina UNLP', 'slug' => 'la-plata', 'ordem' => 2, 'descricao_curta' => 'Universidad Nacional de La Plata'],
            ['nome' => 'Medicina Barceló', 'slug' => 'barcelo', 'ordem' => 3, 'descricao_curta' => 'Universidad Barceló'],
            ['nome' => 'CBC / UBA XXI', 'slug' => 'cbc', 'ordem' => 4, 'descricao_curta' => 'Ciclo Básico Común · UBA XXI'],
        ] as $f) {
            $payload = [
                'nome' => $f['nome'],
                'ordem' => $f['ordem'],
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($hasDescricaoCurta) {
                $payload['descricao_curta'] = $f['descricao_curta'];
            }
            DB::table('faculdades')->updateOrInsert(
                ['slug' => $f['slug']],
                $payload
            );
        }

        $idUba = (int) DB::table('faculdades')->where('slug', 'uba')->value('id');
        $idLp = (int) DB::table('faculdades')->where('slug', 'la-plata')->value('id');
        $idBc = (int) DB::table('faculdades')->where('slug', 'barcelo')->value('id');
        $idCbc = (int) DB::table('faculdades')->where('slug', 'cbc')->value('id');

        // Agrupamentos UBA — o Ciclo Biomédico se divide em 1º/2º/3º Año (plan de
        // estudios real), e o Ciclo Clínico tem matérias próprias além de se
        // dividir em Clínicas e Quirúrgicas.
        $agrBiomedSlug = 'uba-ciclo-biomedico';
        DB::table('agrupamentos')->updateOrInsert(
            ['faculdade_id' => $idUba, 'slug' => $agrBiomedSlug],
            [
                'nome' => 'Ciclo Biomédico — 1er Año',
                'ordem' => 1,
                'tipo' => 'ano',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([
            ['uba-ciclo-biomedico-2', 'Ciclo Biomédico — 2do Año', 2],
            ['uba-ciclo-biomedico-3', 'Ciclo Biomédico — 3er Año', 3],
        ] as [$slug, $nome, $ord]) {
            DB::table('agrupamentos')->updateOrInsert(
                ['faculdade_id' => $idUba, 'slug' => $slug],
                [
                    'nome' => $nome,
                    'ordem' => $ord,
                    'tipo' => 'ano',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('agrupamentos')->updateOrInsert(
            ['faculdade_id' => $idUba, 'slug' => 'uba-ciclo-clinico'],
            [
                'nome' => 'Ciclo Clínico',
                'ordem' => 4,
                'tipo' => 'ciclo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Reflete o plan de estudios real da UBA: depois do Ciclo Clínico "puro"
        // (Medicina A, Patología II, Farmacología II, Salud Pública, Bioética II...)
        // vêm as rotações de Clínicas, depois Quirúrgicas, e por último o Ciclo
        // Internado Anual Rotatorio. Ficam "vazios" até termos banco de questões
        // pra alguma matéria desses estágios — não aparecem na UI enquanto isso
        // (ver CatalogoAjaxController::agrupamentos, que só lista agrupamentos
        // com pelo menos uma matéria).
        foreach ([
            ['uba-clinicas', 'Clínicas', 5],
            ['uba-quirurgicas', 'Quirúrgicas', 6],
            ['uba-ciclo-internado', 'Ciclo Internado', 7],
        ] as [$slug, $nome, $ord]) {
            DB::table('agrupamentos')->updateOrInsert(
                ['faculdade_id' => $idUba, 'slug' => $slug],
                [
                    'nome' => $nome,
                    'ordem' => $ord,
                    'tipo' => 'ciclo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $agrBioId = (int) DB::table('agrupamentos')->where('slug', $agrBiomedSlug)->value('id');
        $agrBio2Id = (int) DB::table('agrupamentos')->where('slug', 'uba-ciclo-biomedico-2')->value('id');
        $agrBio3Id = (int) DB::table('agrupamentos')->where('slug', 'uba-ciclo-biomedico-3')->value('id');
        $agrClinicoId = (int) DB::table('agrupamentos')->where('slug', 'uba-ciclo-clinico')->value('id');
        $agrQuirurgicasId = (int) DB::table('agrupamentos')->where('slug', 'uba-quirurgicas')->value('id');

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
                'agrupamento_id' => $agrBio3Id,
                'ordem' => 7,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 1)->update([
                'nome' => 'Microbiología y Parasitología',
                'slug' => 'microbiologia-y-parasitologia',
                'agrupamento_id' => $agrBio3Id,
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

        $agrLpPrimerAnId = (int) DB::table('agrupamentos')->where('slug', 'la-plata-primer-an')->value('id');
        $agrCbcPrimerAnId = (int) DB::table('agrupamentos')->where('slug', 'cbc-primer-an')->value('id');

        if (! DB::table('materias')->where('id', 3)->exists()) {
            DB::table('materias')->insert([
                'id' => 3,
                'nome' => 'Biología (UNLP)',
                'slug' => 'biologia-la-plata',
                'agrupamento_id' => $agrLpPrimerAnId,
                'ordem' => 1,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 3)->update([
                'nome' => 'Biología (UNLP)',
                'slug' => 'biologia-la-plata',
                'agrupamento_id' => $agrLpPrimerAnId,
                'ordem' => 1,
            ]);
        }

        if (! DB::table('materias')->where('id', 4)->exists()) {
            DB::table('materias')->insert([
                'id' => 4,
                'nome' => 'Biología (CBC)',
                'slug' => 'biologia-cbc',
                'agrupamento_id' => $agrCbcPrimerAnId,
                'ordem' => 1,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 4)->update([
                'nome' => 'Biología (CBC)',
                'slug' => 'biologia-cbc',
                'agrupamento_id' => $agrCbcPrimerAnId,
                'ordem' => 1,
            ]);
        }

        if (! DB::table('materias')->where('id', 5)->exists()) {
            DB::table('materias')->insert([
                'id' => 5,
                'nome' => 'Farmacología II',
                'slug' => 'farmacologia-ii-catedra-3',
                'agrupamento_id' => $agrClinicoId,
                'ordem' => 3,
            ]);
            $this->fixSqliteSequence('materias');
        } else {
            DB::table('materias')->where('id', 5)->update([
                'nome' => 'Farmacología II',
                'slug' => 'farmacologia-ii-catedra-3',
                'agrupamento_id' => $agrClinicoId,
                'ordem' => 3,
            ]);
        }

        DB::table('catedras')->updateOrInsert(
            ['materia_id' => 5, 'slug' => 'catedra-iii'],
            ['nome' => 'Cátedra III', 'ordem' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        // Primer Año: Histología-Biología Celular-Embriología-Genética (curso
        // combinado no plan de estudios, modelado aqui como matérias separadas).
        $ubiomedMateriasAno1 = [
            ['slug' => 'histologia', 'nome' => 'Histología', 'ordem' => 1],
            ['slug' => 'embriologia', 'nome' => 'Embriología', 'ordem' => 2],
            ['slug' => 'biologia-molecular-y-genetica', 'nome' => 'Biología Molecular y Genética', 'ordem' => 3],
        ];
        foreach ($ubiomedMateriasAno1 as $m) {
            DB::table('materias')->updateOrInsert(
                ['slug' => $m['slug']],
                [
                    'nome' => $m['nome'],
                    'agrupamento_id' => $agrBioId,
                    'ordem' => $m['ordem'],
                ]
            );
        }

        // Segundo Año: Química Biológica (Bioquímica) e Fisiología y Biofísica.
        $ubiomedMateriasAno2 = [
            ['slug' => 'fisiologia-y-biofisica', 'nome' => 'Fisiología y Biofísica', 'ordem' => 4],
            ['slug' => 'bioquimica', 'nome' => 'Bioquímica', 'ordem' => 5],
        ];
        foreach ($ubiomedMateriasAno2 as $m) {
            DB::table('materias')->updateOrInsert(
                ['slug' => $m['slug']],
                [
                    'nome' => $m['nome'],
                    'agrupamento_id' => $agrBio2Id,
                    'ordem' => $m['ordem'],
                ]
            );
        }

        // Tercer Año: Microbiología y Parasitología I/II, Inmunología Humana.
        DB::table('materias')->updateOrInsert(
            ['slug' => 'inmunologia-humana'],
            [
                'nome' => 'Inmunología Humana',
                'agrupamento_id' => $agrBio3Id,
                'ordem' => 6,
            ]
        );

        foreach ([
            ['slug' => 'patologia', 'nome' => 'Patología', 'agrupamento_id' => $agrClinicoId],
            ['slug' => 'farmacologia-i', 'nome' => 'Farmacología I', 'agrupamento_id' => $agrBioId],
            ['slug' => 'medicina-i', 'nome' => 'Medicina I', 'agrupamento_id' => $agrClinicoId],
            ['slug' => 'neurocirugia', 'nome' => 'Neurocirugía', 'agrupamento_id' => $agrQuirurgicasId],
        ] as $i => $row) {
            DB::table('materias')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'nome' => $row['nome'],
                    'agrupamento_id' => $row['agrupamento_id'],
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
            ['farmacologia-i', 'catedra-iii', 'Cátedra III', 1],
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
