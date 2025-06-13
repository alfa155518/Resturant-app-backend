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
            $table->string('payment_intent_id')->nullable()->after('session_id');
            $table->decimal('amount_total', 10, 2)->after('total');
            $table->string('currency', 3)->default('usd')->after('amount_total');
            $table->dateTime('payment_date')->nullable()->after('payment_status');
            $table->json('metadata')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->dropColumn([
                'payment_intent_id',
                'amount_total',
                'currency',
                'payment_date',
                'metadata'
            ]);
        });
    }
};
