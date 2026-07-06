<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->unsignedInteger('materia_id')->nullable()->after('usuario_id');
            $table->boolean('compartilhado')->default(false)->after('descricao');
            $table->timestamp('compartilhado_em')->nullable()->after('compartilhado');
            $table->unsignedInteger('deck_origem_id')->nullable()->after('compartilhado_em');

            $table->foreign('materia_id')->references('id')->on('materias')->nullOnDelete();
            $table->foreign('deck_origem_id')->references('id')->on('decks')->nullOnDelete();
            $table->index(['materia_id', 'compartilhado']);
        });
    }

    public function down(): void
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->dropForeign(['materia_id']);
            $table->dropForeign(['deck_origem_id']);
            $table->dropColumn(['materia_id', 'compartilhado', 'compartilhado_em', 'deck_origem_id']);
        });
    }
};
