<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReservationsController extends Controller
{

    /**
     * Get all reservations.
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersReservations()
    {
        try {
            $reservations = Reservations::whereNull('deleted_at')->with([
                'table' => function ($query) {
                    $query->whereNull('deleted_at')
                        ->select(['id', 'table_number', 'max_guests']);
                },
                'user' => function ($query) {
                    $query->select(['id', 'name', 'email', 'phone']);
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
                    'user_id',

                ])
                ->get()
                ->map(function ($reservation) {
                    $reservation->setAppends(['formatted_reservation_date']);
                    return $reservation;
                });

            return response()->json([
                'status' => 'success',
                'data' => $reservations
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching user reservations: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }



    /**
     * Update a reservation
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateReservation(Request $request, $id)
    {
        try {
            // Eager load relationships to prevent N+1 queries
            $reservation = Reservations::with(['user', 'table'])->find($id);
            if (!$reservation) {
                return self::notFound('Reservation');
            }

            // Process updates in a single transaction
            $updatedReservation = Reservations::updateReservationWithTransaction($request, $reservation);

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation updated successfully',
                'data' => Reservations::formatReservationResponse($updatedReservation)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating reservation: ' . $e->getMessage(), [
                'exception' => $e,
                'reservation_id' => $id ?? null,
                'request_data' => $request->except(['password', 'token'])
            ]);
            return self::serverError();
        }
    }


    /**
     * Delete a reservation
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteReservation($id)
    {
        try {
            $reservation = Reservations::find($id);

            if (!$reservation) {
                return self::notFound('Reservation');
            }

            // Store the ID for verification after deletion
            $reservationId = $reservation->id;

            // Delete the reservation
            $reservation->delete();

            // Verify the reservation was actually deleted
            $stillExists = Reservations::where('id', $reservationId)->exists();

            if ($stillExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete reservation'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting reservation: ' . $e->getMessage(), [
                'exception' => $e,
                'reservation_id' => $id ?? null
            ]);
            return self::serverError();
        }
    }
}