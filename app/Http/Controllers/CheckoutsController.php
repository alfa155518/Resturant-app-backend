<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Models\CheckoutItem;
use App\Models\Checkouts;
use App\Models\Menu;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CheckoutsController extends Controller
{
    use UserId;
    // get user Checkouts
    public function userCheckouts(Request $request)
    {
        try {
            $userId = $this->getUserId($request);

            if (empty($userId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: You must be Logged in'
                ], 401);
            }

            // Pagination parameters with defaults
            $perPage = (int) $request->query('per_page', 20);
            $page = (int) $request->query('page', 1);

            // Generate cache key based on user and pagination parameters
            $cacheKey = "user_checkouts_{$userId}_{$page}_{$perPage}";
            // Try to get data from cache first (with a 5-minute expiration)
            return Cache::remember($cacheKey, 300, function () use ($userId, $perPage) {
                // Use more efficient query with indexing hint
                $query = DB::table('checkouts')
                    ->where('user_id', $userId)
                    ->select([
                        'id',
                        'amount_total',
                        'currency',
                        'payment_status',
                        'created_at',
                        'payment_date',
                        'payment_method',

                    ])
                    ->orderBy('created_at', 'desc');

                $userCheckouts = $query->paginate($perPage);

                $response = response()->json([
                    'current_page' => $userCheckouts->currentPage(),
                    'checkouts' => $userCheckouts->items(),
                    'per_page' => $userCheckouts->perPage(),
                    'total' => $userCheckouts->total(),
                    'last_page' => $userCheckouts->lastPage(),
                ], 200);

                return SecurityHeaders::secureHeaders($response);
            });

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving checkout history:' . $e->getMessage()
            ], 500);
        }
    }

    // get user checkout products
    public function userCheckoutProducts(Request $request)
    {
        $userId = $this->getUserId($request);
        $items = CheckoutItem::getCheckoutsByUserId($userId);
        $meta_data = Checkouts::where('user_id', '=', $userId)->select('metadata')->get();

        $product_ids = [];
        $cart_items = [];

        foreach ($meta_data as $data) {
            $metadata = is_string($data->metadata) ? json_decode($data->metadata, true) : $data->metadata;
            if (isset($metadata['cart_items'])) {
                $cart_items = is_string($metadata['cart_items'])
                    ? json_decode($metadata['cart_items'], true)
                    : $metadata['cart_items'];
                $product_ids = array_column($cart_items, 'product_ids');
            }
        }

        $menu_items = Menu::whereIn('id', $product_ids)->get()->keyBy('id');
        $cart_items_by_name = collect($cart_items)->keyBy('name');

        $data = [
            'items' => $items->map(function ($item) use ($menu_items, $cart_items_by_name) {
                $cart_item = $cart_items_by_name->get($item->product_name);
                $product_id = $cart_item['product_ids'] ?? $item->product_ids;
                return [
                    'id' => $item->id,
                    'checkout_id' => $item->checkout_id,
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'product_ids' => $product_id,
                    'image' => $product_id && isset($menu_items[$product_id]) ? $menu_items[$product_id]->image : null,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'checkout' => $item->checkout,
                ];
            })->toArray(),
        ];

        $response = response()->json($data);
        return SecurityHeaders::secureHeaders($response);
    }
}
;



