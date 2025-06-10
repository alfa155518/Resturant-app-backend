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
        Schema::create('restaurant_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address', 500);
            $table->string('phone', 20);
            $table->string('email', 255)
                ->unique();
            $table->string('website', 255)
                ->nullable();
            $table->string('logo', 255)
                ->nullable();
            $table->string('logo_public_id', 255)
                ->nullable();
            $table->string('message', 255)
                ->nullable();
            $table->text('description')
                ->nullable();
            $table->string('timezone', 50)
                ->default('UTC');
            $table->boolean('is_active')
                ->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_infos');
    }
};
