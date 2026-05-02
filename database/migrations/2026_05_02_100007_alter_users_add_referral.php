<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('codigo_cupom', 32)->nullable()->unique()->after('email');
            $table->decimal('saldo_credito', 10, 2)->default(0)->after('codigo_cupom');
            $table->string('referido_por_codigo', 64)->nullable()->after('saldo_credito');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['codigo_cupom']);
            $table->dropColumn(['codigo_cupom', 'saldo_credito', 'referido_por_codigo']);
        });
    }
};
