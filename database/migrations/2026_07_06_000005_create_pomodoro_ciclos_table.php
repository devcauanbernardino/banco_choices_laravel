<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pomodoro_ciclos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('materia_id');
            $table->string('sessao_uid', 40);
            $table->unsignedInteger('duracao_minutos');
            $table->timestamp('concluido_em');
            $table->timestamps();

            $table->index(['usuario_id', 'concluido_em']);
            $table->index(['usuario_id', 'materia_id']);
            $table->index('sessao_uid');

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pomodoro_ciclos');
    }
};
