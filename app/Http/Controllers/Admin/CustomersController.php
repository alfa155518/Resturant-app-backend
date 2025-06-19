<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\handelUploadPhoto;
use App\Http\Controllers\Controller;
use App\Models\CheckoutItem;
use App\Models\Checkouts;
use App\Models\User;
use App\Traits\AdminSecurityHeaders;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Log;

class CustomersController extends Controller
{
    use UserId, AdminSecurityHeaders;

    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Get all customers with selected fields
     *
     * @return mixed
     */
    public function getCustomers()
    {
        // Get all customers with selected fields
        $customers = User::where('role', 'customer')
            ->select(['id', 'avatar', 'name', 'email', 'phone', 'created_at', 'is_active'])
            ->get();

        // Get order statistics for each customer
        $orderStats = Checkouts::select(
            'user_id',
            \DB::raw('COUNT(*) as order_count'),
            \DB::raw('SUM(amount_total) as total_amount'),
            \DB::raw('MAX(created_at) as last_order')
        )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        try {
            // Map customers with their order information
            $result = Cache::rememberForever('customers_with_orders', function () use ($customers, $orderStats) {
                return $customers->map(function ($customer) use ($orderStats) {
                    $stats = $orderStats->get($customer->id);

                    return [
                        'avatar' => $customer->avatar,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'created_at' => $customer->created_at,
                        'is_active' => $customer->is_active,
                        'amount_total' => $stats->total_amount ?? 0,
                        'orders_count' => $stats->order_count ?? 0,
                        'last_order' => $stats->last_order ?? null
                    ];
                });
            });

            $response = response()->json([
                'status' => 'success',
                'data' => $result
            ]);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

    /**
     * Get a customer's details
     *
     * @param int $id
     * @return mixed
     */
    public function customerDetails($id)
    {
        $customer = User::where('id', $id)
            ->select(['id', 'avatar', 'name', 'email', 'address', 'phone', 'created_at', 'is_active'])
            ->first();

        if (!$customer) {
            return self::notFound('Customer');
        }

        $cacheKey = 'customer_orders_' . $id;
        try {

            $orders = Cache::rememberForever($cacheKey, function () use ($id) {
                return Checkouts::where('user_id', $id)
                    ->select(['id', 'amount_total', 'payment_status', 'metadata', 'created_at'])
                    ->get()
                    ->map(function ($order) {
                        // Handle case where metadata is already an array or a JSON string
                        $metaData = is_array($order->metadata)
                            ? $order->metadata
                            : (json_decode($order->metadata, true) ?? []);

                        // Get cart_items, handling both string and array cases
                        $cartItems = [];
                        if (isset($metaData['cart_items'])) {
                            $cartItems = is_string($metaData['cart_items'])
                                ? (json_decode($metaData['cart_items'], true) ?? [])
                                : (is_array($metaData['cart_items']) ? $metaData['cart_items'] : []);
                        }

                        $order->cart_items_count = is_array($cartItems) ? count($cartItems) : 0;
                        return $order;
                    });
            });

            $customer->orders = $orders;

            $response = response()->json([
                'status' => 'success',
                'data' => $customer
            ]);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

    /**
     * Update a customer
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return mixed
     */
    public function updateCustomer(Request $request, $id)
    {
        try {

            $customer = User::where('id', $id)->first();

            if (!$customer) {
                return self::notFound('Customer');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'email' => 'required|email|max:50',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'role' => 'required|string|max:20|in:customer,admin,super-admin',
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validated = $validator->validated();


            $customer->update($validated);

            $this->forgetCustomersCache();
            $this->forgetCustomerDetailsCache($id);

            $response = response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

    /**
     * Delete a customer
     *
     * @param int $id
     * @return mixed
     */
    public function deleteCustomer($id)
    {
        try {
            $customer = User::where('id', $id)->first();

            if (!$customer) {
                return self::notFound('Customer');
            }

            $customer->delete();

            // delete customer orders
            // Get all checkouts for the customer
            $checkouts = Checkouts::where('user_id', $customer->id)->get();

            // Delete all checkout items for each checkout
            foreach ($checkouts as $checkout) {
                CheckoutItem::where('checkout_id', $checkout->id)->delete();
                $checkout->delete();
            }

            $this->uploadHandler->deletePhoto($customer->avatar_public_id);


            $this->forgetCustomersCache();
            $this->forgetCustomerDetailsCache($id);

            $response = response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully',
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage(), [
                'customer_id' => $id,
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Invalidate the customers cache
     */
    protected function forgetCustomersCache()
    {
        Cache::forget('customers_with_orders');
    }

    /**
     * Invalidate the customer details cache
     */
    protected function forgetCustomerDetailsCache($id)
    {
        $cacheKey = 'customer_orders_' . $id;
        Cache::forget($cacheKey);
    }

}
