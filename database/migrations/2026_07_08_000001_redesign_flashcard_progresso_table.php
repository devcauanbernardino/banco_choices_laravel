<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flashcards deixam de reaproveitar o banco de questoes (questao_id) e passam a ter
     * conteudo proprio por materia (storage/app/data/flashcards_*.json), indexado por
     * overlay_key, no mesmo espirito do QuestionBankLocator. Sem uso real em producao
     * ate aqui (tabela vazia), entao recriamos direto em vez de migrar dados.
     */
    public function up(): void
    {
        Schema::dropIfExists('flashcard_progresso');

        Schema::create('flashcard_progresso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('materia_id');
            $table->unsignedInteger('overlay_key');
            $table->decimal('fator_facilidade', 4, 2)->default(2.50);
            $table->unsignedInteger('intervalo_dias')->default(0);
            $table->unsignedInteger('repeticoes')->default(0);
            $table->timestamp('proxima_revisao_em')->nullable();
            $table->timestamp('ultima_revisao_em')->nullable();
            $table->unsignedInteger('total_revisoes')->default(0);
            $table->timestamps();

            $table->unique(['usuario_id', 'materia_id', 'overlay_key']);
            $table->index(['usuario_id', 'materia_id', 'proxima_revisao_em']);

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_progresso');

        Schema::create('flashcard_progresso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('questao_id');
            $table->unsignedInteger('materia_id');
            $table->decimal('fator_facilidade', 4, 2)->default(2.50);
            $table->unsignedInteger('intervalo_dias')->default(0);
            $table->unsignedInteger('repeticoes')->default(0);
            $table->timestamp('proxima_revisao_em')->nullable();
            $table->timestamp('ultima_revisao_em')->nullable();
            $table->unsignedInteger('total_revisoes')->default(0);
            $table->timestamps();

            $table->unique(['usuario_id', 'questao_id']);
            $table->index(['usuario_id', 'materia_id', 'proxima_revisao_em']);

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('questao_id')->references('id')->on('questoes')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }
};
