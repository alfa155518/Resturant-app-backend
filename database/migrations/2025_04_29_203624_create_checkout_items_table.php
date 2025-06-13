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
        // Make sure checkouts table exists first
        if (!Schema::hasTable('checkouts')) {
            return;
        }
        Schema::create('checkout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_id')
                  ->constrained('checkouts')
                  ->onDelete('cascade');
            $table->string('product_name', 191);
            $table->decimal('price', 8, 2)->unsigned();
            $table->integer('quantity')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('checkout_items')) {
Schema::dropIfExists('checkout_items');
        }
    }
};
