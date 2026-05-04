<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            if (! Schema::hasColumn('questoes', 'justificativa')) {
                $table->text('justificativa')->nullable()->after('tema');
            }
            if (! Schema::hasColumn('questoes', 'fonte')) {
                $table->string('fonte', 500)->nullable()->after('justificativa');
            }
            if (! Schema::hasColumn('questoes', 'plano')) {
                $table->string('plano', 100)->nullable()->after('fonte');
            }
        });

        if (Schema::hasColumn('questoes', 'plano')) {
            $indexName = 'questoes_plano_index';
            $exists = collect(Schema::getIndexes('questoes'))->contains(fn ($i) => ($i['name'] ?? null) === $indexName);
            if (! $exists) {
                Schema::table('questoes', function (Blueprint $table) {
                    $table->index('plano');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            if (Schema::hasColumn('questoes', 'plano')) {
                try {
                    $table->dropIndex(['plano']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('plano');
            }
            if (Schema::hasColumn('questoes', 'fonte')) {
                $table->dropColumn('fonte');
            }
            if (Schema::hasColumn('questoes', 'justificativa')) {
                $table->dropColumn('justificativa');
            }
        });
    }
};
