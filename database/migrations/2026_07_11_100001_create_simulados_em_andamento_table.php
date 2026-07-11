<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulados_em_andamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id')->unique();
            $table->longText('dados');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulados_em_andamento');
    }
};
