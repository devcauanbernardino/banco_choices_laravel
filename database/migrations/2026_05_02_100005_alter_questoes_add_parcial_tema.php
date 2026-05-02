<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->string('parcial', 16)->nullable()->after('overlay_key');
            $table->string('tema')->nullable()->after('parcial');

            $table->index(['materia_id', 'parcial']);
            $table->index(['materia_id', 'tema']);
        });
    }

    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->dropIndex(['materia_id', 'parcial']);
            $table->dropIndex(['materia_id', 'tema']);
            $table->dropColumn(['parcial', 'tema']);
        });
    }
};
