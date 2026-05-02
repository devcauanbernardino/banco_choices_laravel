<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catedras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('materia_id');
            $table->string('nome');
            $table->string('slug');
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->index(['materia_id']);
            $table->unique(['materia_id', 'slug']);
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catedras');
    }
};
