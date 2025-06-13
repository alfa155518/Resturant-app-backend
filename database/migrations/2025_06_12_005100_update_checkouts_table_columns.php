<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            // Make subtotal nullable with default 0
            $table->decimal('subtotal', 10, 2)->default(0)->change();
            
            // Make other numeric fields nullable with default 0
            $table->decimal('tax', 10, 2)->default(0)->change();
            $table->decimal('shipping', 10, 2)->default(0)->change();
            $table->decimal('total', 10, 2)->default(0)->change();
            
            // Make string fields nullable
            $table->string('customer_phone')->nullable()->change();
            $table->text('shipping_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes if needed
        Schema::table('checkouts', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->change();
            $table->decimal('tax', 10, 2)->change();
            $table->decimal('shipping', 10, 2)->change();
            $table->decimal('total', 10, 2)->change();
            $table->string('customer_phone')->change();
            $table->text('shipping_address')->change();
        });
    }
};
