<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_conteudo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('questao_id');
            $table->string('idioma', 8);
            $table->text('frente');
            $table->text('verso');
            $table->timestamps();

            $table->unique(['questao_id', 'idioma']);
            $table->foreign('questao_id')->references('id')->on('questoes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_conteudo');
    }
};
