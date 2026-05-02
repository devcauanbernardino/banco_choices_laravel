<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->unsignedInteger('agrupamento_id')->nullable()->after('nome');
            $table->unsignedInteger('ordem')->default(0)->after('agrupamento_id');
            $table->index(['agrupamento_id']);
            $table->foreign('agrupamento_id')->references('id')->on('agrupamentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['agrupamento_id']);
            $table->dropIndex(['agrupamento_id']);
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'agrupamento_id', 'ordem']);
        });
    }
};
