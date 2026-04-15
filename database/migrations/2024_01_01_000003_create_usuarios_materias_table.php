<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_materias', function (Blueprint $table) {
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('materia_id');
            $table->primary(['usuario_id', 'materia_id']);
            $table->index('usuario_id');
            $table->index('materia_id');
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_materias');
    }
};
