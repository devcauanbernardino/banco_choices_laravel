<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deck_cartas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deck_id');
            $table->text('frente');
            $table->text('verso');
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index('deck_id');
            $table->foreign('deck_id')->references('id')->on('decks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_cartas');
    }
};
