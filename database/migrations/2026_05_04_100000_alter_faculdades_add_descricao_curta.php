<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('faculdades', 'descricao_curta')) {
            Schema::table('faculdades', function (Blueprint $table) {
                $table->string('descricao_curta', 200)->nullable()->after('nome');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('faculdades', 'descricao_curta')) {
            Schema::table('faculdades', function (Blueprint $table) {
                $table->dropColumn('descricao_curta');
            });
        }
    }
};
