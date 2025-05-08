<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Models\Reservations;
use App\Models\Table;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;


class ReservationsController extends Controller
{
    use UserId;

    /**
     * Reserve a table for the user.
     *
     * @param Request $request
     * @param int $id Table ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserveTable(Request $request, $id)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'reservation_date' => 'required|date|after:now',
            'arrival_day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'reservation_time' => 'required|date_format:H:i',
            'guest_count' => 'required|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return Reservations::errorResponse($validator->errors(), 422);
        }

        // Validate arrival_day is within 2 days of reservation_date
        $reservationDate = Carbon::parse($request->reservation_date)->startOfDay();
        $allowedDays = collect([
            $reservationDate->copy(),
            $reservationDate->copy()->addDay(),
            $reservationDate->copy()->addDays(2),
        ])->map->format('l')->toArray();

        if (!in_array($request->arrival_day, $allowedDays)) {
            return Reservations::errorResponse([
                'arrival_day' => 'The arrival day must be within 2 days of the reservation date.'
            ], 422);
        }

        try {
            $userId = $this->getUserId($request);

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($request, $id, $userId, $reservationDate) {
                // Lock the table record to prevent race conditions
                $table = Table::where('id', $id)
                    ->where('is_reservable', true)
                    ->where('is_available', true)
                    ->lockForUpdate()
                    ->first();

                if (!$table) {
                    return Reservations::errorResponse(['message' => 'This table is not available for reservation'], 400);
                }

                if ($request->guest_count > $table->max_guests) {
                    return Reservations::errorResponse([
                        'message' => "Guest count must not exceed {$table->max_guests}"
                    ], 400);
                }

                // Check for overlapping reservations
                $hasOverlap = Reservations::overlapping(
                    $table->id,
                    $reservationDate->toDateString(),
                    $request->reservation_time
                )->exists();

                if ($hasOverlap) {
                    return Reservations::errorResponse(['message' => 'This table is already reserved for the requested time'], 409);
                }

                // Create reservation
                $reservation = Reservations::create([
                    'user_id' => $userId,
                    'reservation_date' => $reservationDate,
                    'arrival_day' => $request->arrival_day,
                    'reservation_time' => $request->reservation_time,
                    'guest_count' => $request->guest_count,
                    'table_id' => $table->id,
                    'status' => Reservations::STATUS_PENDING,
                ]);

                // Update table availability
                $table->update([
                    'is_available' => false,
                    'is_reservable' => false,
                    'status' => 'reserved'
                ]);
                // $table->save();
                return SecurityHeaders::secureHeaders(response()->json([
                    'status' => 'success',
                    'message' => 'Table reserved successfully',
                    // 'data' => $reservation
                ], 201));
            });
        } catch (\Exception $e) {
            return Reservations::errorResponse(['message' => 'Failed to reserve table'], 500);
        }
    }



    /**
     * Retrieve all tables associated with the user's reservations.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserReservations(Request $request): JsonResponse
    {
        // Ensure the user is authenticated
        $userId = $this->getUserId($request);


        // Fetch reservations with their associated tables
        $reservations = Reservations::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->with([
                'table' => function ($query) {
                    $query->whereNull('deleted_at')
                        ->select(['id', 'name', 'image', 'description']);
                }
            ])
            ->select([
                'id',
                'reservation_date',
                'arrival_day',
                'reservation_time',
                'guest_count',
                'status',
                'table_id',
                'created_at',
            ])
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'date' => $reservation->reservation_date->format('Y-m-d'),
                    'time' => substr($reservation->reservation_time, 0, 5), // e.g., "20:30"
                    'guests' => $reservation->guest_count,
                    'status' => $reservation->status,
                    'table_id' => $reservation->table_id,
                    'table' => $reservation->table ? [
                        'name' => $reservation->table->name,
                        'image' => $reservation->table->image,
                        'description' => $reservation->table->description,
                    ] : null,
                ];
            });

        // Return empty array if no tables found
        if ($reservations->isEmpty()) {
            return self::notFound('Table');
        }
        return response()->json(['data' => $reservations], 200);
    }


    /**
     * Cancel a reservation by ID
     *
     * @param Request $request
     * @param int $id Reservation ID
     * @return JsonResponse
     */
    public function cancelReservation(Request $request, $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            // Use transaction to ensure atomicity and prevent race conditions
            return DB::transaction(function () use ($userId, $id) {
                // Find reservation with lock to prevent race conditions
                $reservation = Reservations::where('user_id', $userId)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$reservation) {
                    return self::notFound('Reservation');
                }

                // Check if reservation can be cancelled (not already cancelled)
                if ($reservation->status === Reservations::STATUS_CANCELLED) {
                    return SecurityHeaders::secureHeaders(response()->json([
                        'status' => 'error',
                        'message' => 'Reservation is already cancelled'
                    ], 400));
                }

                // Get the table before deleting the reservation
                $table = Table::find($reservation->table_id);

                // Update reservation status to cancelled
                $reservation->status = Reservations::STATUS_CANCELLED;
                $reservation->save();

                // Update table availability if table exists
                if ($table) {
                    $table->update([
                        'is_available' => true,
                        'is_reservable' => true,
                        'status' => 'active'
                    ]);
                }

                // Return response with security headers
                return SecurityHeaders::secureHeaders(response()->json([
                    'status' => 'success',
                    'message' => 'Reservation cancelled successfully'
                ], 200));
            });
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Reservation cancellation failed: ' . $e->getMessage(), [
                'reservation_id' => $id,
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::serverError();
        }
    }

}