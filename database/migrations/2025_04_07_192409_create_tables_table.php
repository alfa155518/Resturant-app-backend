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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('table_number', 20)->unique();
            $table->unsignedSmallInteger('capacity')->index();
            $table->string('image', 255);
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->text('details')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_reservable')->default(true);
            $table->unsignedTinyInteger('min_guests')->unsigned()->default(1);
            $table->unsignedTinyInteger('max_guests')->unsigned();
            $table->enum('status', ['active', 'maintenance', 'reserved', 'inactive'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
