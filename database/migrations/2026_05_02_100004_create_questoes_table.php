<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questoes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('materia_id');
            $table->unsignedInteger('catedra_id')->nullable();
            $table->unsignedInteger('overlay_key')->default(0);
            $table->timestamps();

            $table->index(['materia_id']);
            $table->unique(['materia_id', 'overlay_key']);

            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
            $table->foreign('catedra_id')->references('id')->on('catedras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questoes');
    }
};
