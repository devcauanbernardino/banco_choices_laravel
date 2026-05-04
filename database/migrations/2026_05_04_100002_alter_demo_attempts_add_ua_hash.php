<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('demo_attempts', 'user_agent_hash')) {
            Schema::table('demo_attempts', function (Blueprint $table) {
                $table->string('user_agent_hash', 64)->nullable()->after('ip')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('demo_attempts', 'user_agent_hash')) {
            Schema::table('demo_attempts', function (Blueprint $table) {
                try { $table->dropIndex(['user_agent_hash']); } catch (\Throwable $e) {}
                $table->dropColumn('user_agent_hash');
            });
        }
    }
};
