<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const STATUS_PENDING = 'pending';
    private const STATUS_CONFIRMED = 'confirmed';
    private const STATUS_CANCELLED = 'cancelled';
    private const ALLOWED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
    ];

    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade')
                ->comment('References the user who made the reservation, null for guest bookings');
            $table->dateTimeTz('reservation_date')
                ->index()
                ->comment('Date and time of the reservation with timezone');
            $table->enum('arrival_day', [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday'
            ])
                ->comment('Day of the week for arrival, within 2 days of reservation_date');
            $table->time('reservation_time')
                ->comment('Specific time of the reservation (e.g., 14:30:00)');
            $table->unsignedSmallInteger('guest_count')
                ->comment('Number of guests (1-100) for the reservation');
            $table->foreignId('table_id')
                ->nullable()
                ->constrained('tables')
                ->onUpdate('cascade')
                ->onDelete('cascade')
                ->comment('References the reserved table, null if unassigned');
            $table->enum('status', self::ALLOWED_STATUSES)
                ->default(self::STATUS_PENDING)
                ->index()
                ->comment('Reservation status: pending, confirmed, or cancelled');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reservation_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};