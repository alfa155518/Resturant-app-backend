<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Models\ReservationCheckouts;
use App\Models\Reservations;
use App\Traits\UserId;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReservationCheckoutsController extends Controller
{
    use UserId;
    public function createCheckoutSession(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tables,id',
            'name' => 'required|string',
            'description' => 'required|string',
            'image_url' => 'required|string',
            'price' => 'required|numeric',
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        if ($validator->fails()) {
            return self::validationFailed($validator->errors()->first());
        }

        $item = $request->all();

        $userId = $this->getUserId($request);

        $lineItem = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $item['name'],
                    'images' => [$item['image_url'] ?? '']
                ],
                'unit_amount' => intval($item['price'] * 100), // Stripe needs amount in cents
            ],
            'quantity' => 1,
        ];
        try {

            // Create Stripe session
            $session = $stripe->checkout->sessions->create([
                'success_url' => 'http://localhost:3000/profile/successReservation?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://localhost:3000/profile/reservationFailed',
                'line_items' => [$lineItem],
                'mode' => 'payment',
                'metadata' => [
                    'table_id' => $item['id'],
                    'item_name' => $item['name'],
                    'item_description' => $item['description'] ?? '',
                    'item_image_url' => $item['image_url'] ?? '',
                    'item_price' => $item['price'],
                    'user_id' => $userId,
                    'reservation_time' => $item['reservation_time'],
                    'reservation_id' => $item['reservation_id'],
                    'guest_count' => $item['guest_count'],
                ],
            ]);
            return response()->json(['status' => 'success', 'url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Error creating checkout session: ' . $e->getMessage(), [
                'exception' => $e,
                // 'request_data' => $request->except(['password', 'token'])
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function verifyPayment(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));


        try {

            $session = $stripe->checkout->sessions->retrieve(
                $request->query('session_id'),
                ['expand' => ['payment_intent']]
            );

            if ($session->payment_status === 'paid' && $session->status === 'complete') {
                $reservationCheckout = ReservationCheckouts::where('session_id', $session->id)->first();
                if (!$reservationCheckout) {
                    $reservationCheckout = ReservationCheckouts::create([
                        'user_id' => $session->metadata->user_id,
                        'customer_email' => $session->customer_details->email,
                        'customer_name' => $session->customer_details->name,
                        'session_id' => $session->id,
                        'payment_intent_id' => $session->payment_intent->id ?? null,
                        'currency' => $session->currency,
                        'payment_status' => $session->payment_status,
                        'payment_date' => Carbon::parse($session->payment_intent->created)->format('Y-m-d H:i:s'),
                        'payment_method' => $session->payment_intent->payment_method_types[0] ?? 'card',
                        'metadata' => [
                            'item_name' => $session->metadata->item_name,
                            'item_description' => $session->metadata->item_description,
                            'item_image_url' => $session->metadata->item_image_url,
                            'item_price' => $session->metadata->item_price,
                            'user_id' => $session->metadata->user_id,
                            'table_id' => $session->metadata->table_id,
                            'reservation_id' => $session->metadata->reservation_id,
                            'reservation_time' => $session->metadata->reservation_time,
                            'guest_count' => $session->metadata->guest_count,
                        ],
                    ]);
                }

                // Find and update reservation and table status
                $reservationId = $session->metadata->reservation_id;
                if ($reservationId) {
                    $reservation = Reservations::find($reservationId);
                    if ($reservation) {
                        $reservation->status = 'confirmed';
                        $reservation->save();
                    }
                }

                $response = response()->json([
                    'data' => [
                        'id' => $reservationCheckout->id,
                        'payment_status' => $session->payment_status,
                        'reservation_id' => $session->metadata->reservation_id,
                        'reservation_time' => $session->metadata->reservation_time,
                        'guest_count' => $session->metadata->guest_count,
                        'payment_date' => $session->payment_intent->created,
                        'table_id' => $session->metadata->table_id,
                        'payment_method' => $reservationCheckout->payment_method,
                        'table_name' => "T00" . $session->metadata->table_id,
                        'pdf_download_url' => route('reservation_checkout_download.pdf', ['reservation_checkout_id' => $reservationCheckout->id]),
                    ],
                ], 200);

                return SecurityHeaders::secureHeaders($response);
            }

            return response()->json(['error' => 'Payment not completed'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
