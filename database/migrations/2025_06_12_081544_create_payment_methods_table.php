<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_infos_id')->constrained()->onDelete('cascade');
            $table->boolean('credit_card')->default(true);
            $table->boolean('debit_card')->default(true);
            $table->boolean('cash')->default(true);
            $table->boolean('paypal')->default(true);
            $table->boolean('apple_pay')->default(true);
            $table->boolean('google_pay')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
