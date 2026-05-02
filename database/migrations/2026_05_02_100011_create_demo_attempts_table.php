<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_uuid', 40)->index();
            $table->string('ip', 45)->nullable()->index();
            $table->unsignedInteger('materia_id')->index();
            $table->unsignedInteger('questao_id')->nullable();
            $table->boolean('acertou')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);

            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
            $table->foreign('questao_id')->references('id')->on('questoes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_attempts');
    }
};
