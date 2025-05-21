<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Models\CartItems;
use App\Models\Checkouts;
use App\Models\CheckoutItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\UserCartItems;
use App\Traits\UserId;
use Stripe\StripeClient;
use Exception;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    use UserId, UserCartItems;
    public function payment(Request $request)
    {
        // Initialize Stripe client
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        try {
            // Prepare line items and calculate total price
            $lineItems = [];
            $totalPrice = 0;
            $minimalCartItems = []; // For metadata (minimal data)

            foreach ($request->cartItems as $item) {
                $priceInCents = round(floatval($item['product']['price']) * 100);
                $quantity = $item['quantity'];
                $totalPrice += $priceInCents * $quantity;

                $lineItem = [
                    'price_data' => [
                        'currency' => "USD",
                        'product_data' => [
                            'name' => $item['product']['name'],
                        ],
                        'unit_amount' => $priceInCents,
                    ],
                    'quantity' => $quantity,
                ];

                $lineItems[] = $lineItem;

                // Add minimal item data for metadata
                $minimalCartItems[] = [
                    'name' => $item['product']['name'],
                    'price' => $item['product']['price'],
                    'quantity' => $quantity,
                    'product_ids' => $item['product']['id'] ?? null,
                ];
            }

            // Ensure metadata is under 500 characters
            $encodedCartItems = json_encode($minimalCartItems);
            if (strlen($encodedCartItems) > 500) {
                $minimalCartItems = array_slice($minimalCartItems, 0, 1); // Take first item as fallback
                $encodedCartItems = json_encode($minimalCartItems);
            }


            // Create Stripe Checkout Session
            $session = $stripe->checkout->sessions->create([
                'success_url' => env('STRIPE_SUCCESS_URL', 'http://localhost:3000/cart/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('STRIPE_CANCEL_URL', 'http://localhost:3000/cart/payment/cancel'),
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'total_price' => $totalPrice / 100,
                    'currency' => "USD",
                    'user_id' => $request->cartItems[0]['user_id'] ?? null,
                    'cart_items' => $encodedCartItems,
                ],
            ]);

            return response()->json([
                'data' => [
                    'id' => $session->id,
                    'url' => $session->url,
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);
            return response()->json([
                'error' => 'Failed to create checkout session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        try {
            $session = $stripe->checkout->sessions->retrieve(
                $request->query('session_id'),
                ['expand' => ['payment_intent', 'customer']]
            );

            if ($session->payment_status === 'paid' && $session->status === 'complete') {
                $checkout = Checkouts::where('session_id', $session->id)->first();

                if (!$checkout) {
                    $checkout = Checkouts::create([
                        'user_id' => $session->metadata->user_id,
                        'customer_email' => $session->customer_details->email,
                        'customer_name' => $session->customer_details->name,
                        'session_id' => $session->id,
                        'payment_intent_id' => $session->payment_intent->id ?? null,
                        'amount_total' => $session->amount_total / 100,
                        'currency' => $session->currency,
                        'payment_status' => $session->payment_status,
                        'payment_date' => Carbon::parse($session->payment_intent->created)->format('Y-m-d H:i:s'),
                        'payment_method' => $session->payment_intent->payment_method_types[0] ?? 'card',
                        'metadata' => [
                            'total_price' => $session->metadata->total_price,
                            'cart_items' => $session->metadata->cart_items,
                        ],
                    ]);

                    // Save cart items from metadata
                    $cartItems = json_decode($session->metadata->cart_items, true);
                    foreach ($cartItems as $item) {
                        CheckoutItem::create([
                            'checkout_id' => $checkout->id,
                            'product_name' => $item['name'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'product_ids' => $item['product_ids'], // Fix: Use product_ids directly
                        ]);
                    }
                }

                $response = response()->json([
                    'data' => [
                        'session_id' => $session->id,
                        'payment_status' => $session->payment_status,
                        'amount_total' => $session->amount_total / 100,
                        'currency' => $session->currency,
                        'total_price' => $session->metadata->total_price ?? $checkout->metadata['total_price'],
                        'payment_date' => Carbon::parse($session->payment_intent->created)->format('Y-m-d H:i:s'),
                        'payment_method' => $session->payment_intent->payment_method_types[0] ?? 'card',
                    ],
                ], 200);

                // Delete Cart Items For User
                $userId = $this->getUserId($request);
                CartItems::where('user_id', $userId)->delete();

                return SecurityHeaders::secureHeaders($response);
            }

            return response()->json([
                'error' => 'Payment not completed or invalid session.',
                'payment_status' => $session->payment_status,
            ], 400);
        } catch (Exception $e) {
            Log::error('Verify payment failed: ' . $e->getMessage(), [
                'session_id' => $request->query('session_id'),
                'exception' => $e,
            ]);
            return response()->json([
                'error' => 'Failed to verify payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}


