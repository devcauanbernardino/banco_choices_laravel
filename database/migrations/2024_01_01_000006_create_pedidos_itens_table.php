<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_itens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pedido_id');
            $table->unsignedInteger('materia_id');
            $table->string('plano_id', 32);
            $table->decimal('preco', 10, 2)->default(0);
            $table->date('data_expiracao')->nullable();
            $table->index('pedido_id');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('materia_id')->references('id')->on('materias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_itens');
    }
};
