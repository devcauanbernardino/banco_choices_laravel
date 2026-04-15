<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mp_payment_processed', function (Blueprint $table) {
            $table->unsignedBigInteger('mp_payment_id')->primary();
            $table->dateTime('created_at');
            $table->string('payment_status', 32);
            $table->string('external_reference', 128)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_payment_processed');
    }
};
