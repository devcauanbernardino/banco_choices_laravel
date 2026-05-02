<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agrupamentos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('faculdade_id');
            $table->string('nome');
            $table->string('slug');
            $table->unsignedInteger('ordem')->default(0);
            $table->string('tipo', 16)->default('outro');
            $table->timestamps();
            $table->index(['faculdade_id']);
            $table->unique(['faculdade_id', 'slug']);
            $table->foreign('faculdade_id')->references('id')->on('faculdades')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agrupamentos');
    }
};
