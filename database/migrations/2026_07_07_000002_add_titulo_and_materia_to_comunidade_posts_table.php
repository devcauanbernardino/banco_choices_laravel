<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comunidade_posts', function (Blueprint $table) {
            $table->string('titulo', 180)->nullable()->after('usuario_id');
            $table->unsignedInteger('materia_id')->nullable()->after('titulo');

            $table->index('materia_id');
            $table->foreign('materia_id')->references('id')->on('materias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comunidade_posts', function (Blueprint $table) {
            $table->dropForeign(['materia_id']);
            $table->dropIndex(['materia_id']);
            $table->dropColumn(['titulo', 'materia_id']);
        });
    }
};
