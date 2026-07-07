<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunidade_post_curtidas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('usuario_id');
            $table->timestamps();

            $table->unique(['post_id', 'usuario_id']);
            $table->foreign('post_id')->references('id')->on('comunidade_posts')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunidade_post_curtidas');
    }
};
