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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->string('image');
            $table->string(column: 'image_public_id')->nullable();
            $table->string('category', 50)->index();
            $table->unsignedSmallInteger('calories')->default(0);
            $table->decimal('rating', 3, 1)->default(3);
            $table->string('prepTime', 20)->default('15 min'); // Changed from prep_time to prepTime
            $table->json('dietary')->nullable();
            $table->json(column: 'ingredients');
            $table->unsignedSmallInteger('stock')->default(5);
            $table->boolean('available')->default(true);
            $table->boolean('popular')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamps();
            // Add indexes for commonly queried fields
            $table->index(['price', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};

// DB::table('migrations')->where('migration', '2025_04_01_170524_create_menus_table')->delete();