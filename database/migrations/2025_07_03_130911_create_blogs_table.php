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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('excerpt');
            $table->text('content');
            $table->string('image');
            $table->string('image_public_id')->nullable();
            $table->string('author_name');
            $table->timestamp('published_at')->useCurrent();
            $table->string('category');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->json('tags')->default('[]');
            $table->json('comments')->default('[]');
            $table->json('likes')->default('[]');
            $table->json('dislikes')->default('[]');
            $table->timestamps();

            // indexes
            $table->index(['status', 'published_at']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
