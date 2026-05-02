<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('referrer_user_id');
            $table->unsignedInteger('referido_user_id')->nullable();
            $table->string('referido_email');
            $table->string('codigo_usado', 64);
            $table->unsignedInteger('pedido_id')->nullable();
            $table->decimal('valor_credito_gerado', 10, 2)->default(0);
            $table->string('status', 24)->default('pending');

            $table->timestamps();

            $table->index('referido_email');
            $table->unique('referido_email');
            $table->index(['pedido_id']);
            $table->index(['referrer_user_id']);

            $table->foreign('referrer_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referido_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
