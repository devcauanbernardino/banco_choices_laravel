<?php

use App\Services\Questions\Farmaco2Cat3MetadataSync;
use App\Services\Questions\Farmaco2Cat3SectionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reaplica o sync após consolidar os simulacros datados de 1er, 2do e 3er parcial
     * (ex.: "3er parcial 23-11-2023", "3er parcial 27-06-2024" etc.) em um único tema
     * "Simulacro — Xer parcial" por parcial.
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
