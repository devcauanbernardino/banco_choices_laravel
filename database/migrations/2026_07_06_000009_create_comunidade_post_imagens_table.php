<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunidade_post_imagens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->string('caminho', 255);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('comunidade_posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunidade_post_imagens');
    }
};
