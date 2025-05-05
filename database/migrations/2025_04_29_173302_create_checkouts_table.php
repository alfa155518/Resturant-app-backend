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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id', 191)->unique();
            $table->string('customer_name', 25);
            $table->string('customer_email', 30);
            $table->string('payment_intent_id', 191)->nullable()->unique();
            $table->decimal('amount_total', 10, 2)->unsigned();
            $table->string('currency', 3);
            $table->enum('payment_status', ['pending', 'processing', 'succeeded', 'failed', 'refunded', 'canceled', 'paid'])->default('pending')->index();
            $table->string('payment_method', 100);
            $table->timestamp('payment_date');
            $table->json('metadata')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
