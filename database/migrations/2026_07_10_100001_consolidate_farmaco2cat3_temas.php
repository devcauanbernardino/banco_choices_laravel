<?php

use App\Services\Questions\Farmaco2Cat3MetadataSync;
use App\Services\Questions\Farmaco2Cat3SectionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reaplica o sync após consolidar os temas "Final DD-MM-AAAA" (5 datas) em um único
     * "Final", e "Casos clínicos — 2do parcial (1..5)" em um único "Casos clínicos — 2do parcial".
     */
    public function up(): void
    {
        if (! DB::table('materias')->where('id', Farmaco2Cat3SectionCatalog::MATERIA_ID)->exists()) {
            return;
        }

        Farmaco2Cat3MetadataSync::sync();
    }

    public function down(): void
    {
        // Idempotente: nada a reverter (metadados são recalculados a partir do JSON a cada sync).
    }
};
