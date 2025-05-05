<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Reservations extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'reservation_date',
        'arrival_day',
        'reservation_time',
        'guest_count',
        'table_id',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'reservation_time' => 'string',
        'arrival_day' => 'string',
        'status' => 'string',
        'guest_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Scope to check for overlapping reservations.
     */
    public function scopeOverlapping($query, $tableId, $reservationDate, $reservationTime)
    {
        $startTime = \Carbon\Carbon::parse($reservationDate . ' ' . $reservationTime)
            ->subHour(); // Allow 1-hour buffer before
        $endTime = \Carbon\Carbon::parse($reservationDate . ' ' . $reservationTime)
            ->addHour(); // Allow 1-hour buffer after

        return $query->where('table_id', $tableId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->where('reservation_date', '>=', $startTime)
            ->where('reservation_date', '<=', $endTime);
    }

    /**
     * Standardize error responses.
     *
     * @param array|string $errors
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($errors, $status)
    {
        return response()->json([
            'status' => 'error',
            'errors' => is_array($errors) ? $errors : ['message' => $errors],
        ], $status);
    }
}