<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('tema');
            $table->index(['materia_id', 'is_demo']);
            $table->index(['is_demo']);
        });
    }

    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->dropIndex(['materia_id', 'is_demo']);
            $table->dropIndex(['is_demo']);
            $table->dropColumn(['is_demo']);
        });
    }
};
