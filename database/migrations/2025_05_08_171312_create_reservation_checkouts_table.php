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
        Schema::create('reservation_checkouts', function (Blueprint $table) {
            $table->id();

            // User relationship with stricter constraints
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Associated user for the checkout');


            // Customer information with stricter validation
            $table->string('customer_name', 50)
                ->nullable(false)
                ->comment('Customer full name');

            $table->string('customer_email', 100)
                ->nullable(false)
                ->comment('Customer email address');

            // Payment tracking with enhanced security
            $table->string('session_id')
                ->nullable()
                ->unique()
                ->comment('External payment system session ID');
            $table->string('payment_intent_id', 255)
                ->nullable()
                ->unique()
                ->comment('External payment system transaction ID');


            $table->string('currency', 3)
                ->default('USD')
                ->comment('Transaction currency code');

            // Enhanced payment status tracking
            $table->enum('payment_status', [
                'pending',
                'processing',
                'succeeded',
                'failed',
                'refunded',
                'canceled',
                'paid'
            ])
                ->default('pending')
                ->index()
                ->comment('Current status of the payment');

            // Payment method with enhanced tracking
            $table->string('payment_method', 50)
                ->nullable(false)
                ->comment('Payment method used');

            // Timestamps with more precise tracking
            $table->timestamp('payment_date')->useCurrent();

            // Metadata with additional constraints
            $table->json('metadata')
                ->nullable()
                ->comment('Additional transaction metadata');

            $table->timestamps();

            // Comprehensive indexing for performance
            $table->index(['payment_status', 'created_at']);
            $table->index(['user_id', 'payment_status']);
            $table->index(['payment_intent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_checkouts');
    }
};
