<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('favorite_products', function (Blueprint $table) {
            // Use bigIncrements for better security with large datasets
            $table->bigIncrements('id');

            // Add explicit unsigned big integer for foreign keys
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');

            // Add proper foreign key constraints with explicit references
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('menus')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Add timestamp with timezone awareness
            $table->timestamp('added_at')->useCurrent();

            // Standard timestamps with explicit null handling
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Add composite index for query optimization
            $table->index(['user_id', 'product_id'], 'idx_user_product');

            // Ensure a user can only favorite a product once with explicit name
            $table->unique(['user_id', 'product_id'], 'unq_user_product_favorite');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_products');
    }
};
