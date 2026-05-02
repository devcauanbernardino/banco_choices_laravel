<?php

namespace Database\Seeders;

use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            ['id' => 1, 'nome' => 'Microbiologia'],
            ['id' => 2, 'nome' => 'Biología celular'],
        ];

        foreach ($materias as $m) {
            Materia::updateOrCreate(['id' => $m['id']], $m);
        }
    }
}
