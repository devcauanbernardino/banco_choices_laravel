<?php

use App\Services\Questions\Farmaco2Cat3MetadataSync;
use App\Services\Questions\Farmaco2Cat3SectionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Em bancos "limpos" (ex.: suíte de testes, que só roda migrations, sem o CatalogoSeeder),
        // a matéria ainda não existe — o sync roda depois, via seeder/comando de setup.
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
