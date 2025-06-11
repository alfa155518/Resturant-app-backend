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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_infos_id')->constrained()->onDelete('cascade');
            $table->boolean('new_order')->default(true);
            $table->boolean('order_status')->default(true);
            $table->boolean('new_reservation')->default(true);
            $table->boolean('reservation_reminder')->default(true);
            $table->boolean('new_review')->default(true);
            $table->boolean('low_inventory')->default(false);
            $table->boolean('daily_summary')->default(true);
            $table->boolean('weekly_summary')->default(true);
            $table->boolean('marketing_emails')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
