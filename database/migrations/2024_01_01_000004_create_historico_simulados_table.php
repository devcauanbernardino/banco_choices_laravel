<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_simulados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('materia_id');
            $table->unsignedInteger('acertos')->default(0);
            $table->unsignedInteger('total_questoes')->default(0);
            $table->longText('detalhes_json')->nullable();
            $table->timestamp('data_realizacao')->nullable()->useCurrent();
            $table->index('usuario_id');
            $table->index('materia_id');
            $table->index('data_realizacao');
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_simulados');
    }
};
