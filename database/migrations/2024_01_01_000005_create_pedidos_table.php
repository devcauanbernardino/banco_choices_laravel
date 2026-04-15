<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('nome');
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('status', 32)->default('awaiting_payment');
            $table->string('stripe_payment_id', 128)->nullable()->comment('external_reference ORDER-...');
            $table->timestamp('data_criacao')->nullable()->useCurrent();
            $table->index('email');
            $table->index('stripe_payment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
