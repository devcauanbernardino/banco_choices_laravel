<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunidade_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->text('conteudo');
            $table->timestamps();

            $table->index(['created_at']);
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunidade_posts');
    }
};
