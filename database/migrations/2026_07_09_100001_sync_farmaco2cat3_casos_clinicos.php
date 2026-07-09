<?php

use App\Services\Questions\Farmaco2Cat3MetadataSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Farmaco2Cat3MetadataSync::sync();
    }

    public function down(): void
    {
        // Idempotente: nada a reverter (metadados são recalculados a partir do JSON a cada sync).
    }
};
