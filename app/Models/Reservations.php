<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['formatted_reservation_date'];

    protected $casts = [
        'reservation_date' => 'datetime',
        'reservation_time' => 'string',
        'arrival_day' => 'string',
        'status' => 'string',
        'guest_count' => 'integer',
    ];

    /**
     * Get the formatted reservation date.
     *
     * @return string
     */
    public function getFormattedReservationDateAttribute()
    {
        return $this->reservation_date ? $this->reservation_date->format('l, F j, Y') : null;
    }

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


    /**
     * Validate reservation request data
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function validateReservationRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'user.name' => 'required|string|max:100',
            'user.phone' => 'required|string|max:20',
            'user.email' => 'required|email|max:100',
            'guest_count' => 'required|integer|min:1|max:50',
            'table.table_number' => 'required|string|max:20',
            'status' => 'required|in:pending,confirmed,cancelled',
            'reservation_date' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $reservationDate = \Carbon\Carbon::parse($value);
                    if ($reservationDate->isToday() && now()->format('H:i') >= '24:00') {
                        $fail('Reservations cannot be made for today after 12 AM.');
                    }
                },
            ],
            'reservation_time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request) {
                    try {
                        $datePart = \Carbon\Carbon::parse($request->reservation_date)->format('Y-m-d');
                        $reservationDateTime = \Carbon\Carbon::parse($datePart . ' ' . $value);
                        $openingTime = \Carbon\Carbon::parse($datePart . ' 17:00:00');
                        $closingTime = \Carbon\Carbon::parse($datePart . ' 22:00:00');

                        if ($reservationDateTime->lt($openingTime) || $reservationDateTime->gt($closingTime)) {
                            $fail('Reservation time must be between 17:00 and 22:00');
                        }
                    } catch (\Exception $e) {
                        $fail('Invalid time format. Please use HH:MM:SS format.');
                    }
                },
            ],
        ], [
            'reservation_date.after' => 'Reservation date must be a future date',
            'email.email' => 'Please enter a valid email address',
            'phone.max' => 'Phone number should not exceed 20 characters',
            'status.in' => 'Status must be one of: pending, confirmed, cancelled',
        ]);

        if ($validator->fails()) {
            return self::validationFailed($validator->errors()->first());
        }

        return true;
    }


    /**
     * Format reservation response
     * @return array
     */
    protected static function formatReservationResponse($reservation)
    {
        $response = [
            'id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'reservation_date' => $reservation->reservation_date,
            'arrival_day' => $reservation->arrival_day,
            'reservation_time' => $reservation->reservation_time,
            'guest_count' => $reservation->guest_count,
            'table_id' => $reservation->table_id,
            'status' => $reservation->status,
            'created_at' => $reservation->created_at,
            'updated_at' => $reservation->updated_at,
            'deleted_at' => $reservation->deleted_at,
            'formatted_reservation_date' => $reservation->formatted_reservation_date,
        ];

        // Safely add table data if relationship is loaded and exists
        if ($reservation->relationLoaded('table') && $reservation->table) {
            $response['table'] = [
                'table_number' => $reservation->table->table_number ?? null,
            ];
        } else {
            $response['table'] = null;
        }

        // Safely add user data if relationship is loaded and exists
        if ($reservation->relationLoaded('user') && $reservation->user) {
            $response['user'] = [
                'name' => $reservation->user->name ?? null,
                'email' => $reservation->user->email ?? null,
                'phone' => $reservation->user->phone ?? null,
            ];
        } else {
            $response['user'] = null;
        }

        return $response;
    }


    /**
     * Process updates in a single transaction
     * @param \Illuminate\Http\Request $request
     */
    protected static function updateReservationWithTransaction($request, $reservation)
    {
        return DB::transaction(function () use ($request, $reservation) {
            // Update reservation data
            $reservation->fill([
                'reservation_date' => $request->reservation_date,
                'reservation_time' => $request->reservation_time,
                'guest_count' => $request->guest_count,
                'status' => $request->status,
            ])->save();

            // Update user data if provided
            if (isset($request->user) && $reservation->user) {
                $reservation->user->update(
                    collect($request->user)
                        ->only(['name', 'email', 'phone'])
                        ->filter()
                        ->toArray()
                );
            }

            // Update table data if provided
            if (isset($request->table['table_number'])) {
                $table = $reservation->table()->first();
                if ($table) {
                    $table->update([
                        'table_number' => $request->table['table_number']
                    ]);
                }
            }

            return $reservation->fresh(['user', 'table']);
        });
    }

}