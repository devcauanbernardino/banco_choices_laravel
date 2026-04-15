<?php

namespace Database\Seeders;

use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            ['id' => 1, 'nome' => 'Microbiología'],
            ['id' => 2, 'nome' => 'Biología'],
        ];

        foreach ($materias as $m) {
            Materia::firstOrCreate(['id' => $m['id']], $m);
        }
    }
}
