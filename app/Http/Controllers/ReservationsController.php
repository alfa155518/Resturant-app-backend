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
                ]);

                return SecurityHeaders::secureHeaders(response()->json([
                    'message' => 'Table reserved successfully',
                    'data' => $reservation
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


        // Fetch tables with their reservations for the user
        $tables = Table::whereIn('id', function ($query) use ($userId) {
            $query->select('table_id')
                ->from('reservations')
                ->where('user_id', $userId)
                ->whereNull('deleted_at'); // Respect soft deletes for reservations
        })
            ->with([
                'reservation' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->whereNull('deleted_at')
                        ->select([
                            'id',
                            'table_id',
                            'reservation_date',
                            'arrival_day',
                            'reservation_time',
                            'guest_count',
                            'status',
                            'created_at',
                        ]);
                }
            ])
            ->whereNull('deleted_at') // Respect soft deletes for tables
            ->get();

        // Return empty array if no tables found
        if ($tables->isEmpty()) {
            return self::notFound('Table');
        }

        return response()->json(['data' => $tables], 200);
    }



}