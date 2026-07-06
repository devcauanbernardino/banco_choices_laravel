<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deck_progresso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('deck_carta_id');
            $table->decimal('fator_facilidade', 4, 2)->default(2.50);
            $table->unsignedInteger('intervalo_dias')->default(0);
            $table->unsignedInteger('repeticoes')->default(0);
            $table->timestamp('proxima_revisao_em')->nullable();
            $table->timestamp('ultima_revisao_em')->nullable();
            $table->unsignedInteger('total_revisoes')->default(0);
            $table->timestamps();

            $table->unique(['usuario_id', 'deck_carta_id']);
            $table->index(['usuario_id', 'proxima_revisao_em']);

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('deck_carta_id')->references('id')->on('deck_cartas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_progresso');
    }
};
