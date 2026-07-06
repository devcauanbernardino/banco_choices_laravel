<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunidade_denuncias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->string('denunciavel_tipo', 20);
            $table->unsignedInteger('denunciavel_id');
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->index(['denunciavel_tipo', 'denunciavel_id']);
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunidade_denuncias');
    }
};
