<?php

namespace App\Http\Controllers\Admin;

use App\Traits\AdminSecurityHeaders;
use App\Http\Controllers\Controller;

use App\Models\Checkouts;
use App\Models\Menu;
use App\Models\Reviews;
use Illuminate\Http\Request;

class RecentController extends Controller
{

    use AdminSecurityHeaders;


    public function RecentItems()
    {
        try {
            // Get 8 popular menu items
            $popularItems = Menu::where('popular', 1)->select([
                'name',
                'image',
                'price',
                'rating',
            ])
                ->take(8)
                ->get();

            // Get 6 most recent reviews with user and menu item details
            $recentReviews = Reviews::latest()
                ->take(6)->select([
                        'id',
                        'client_name',
                        'rating',
                        'review',
                        'created_at',
                    ])
                ->get();

            $recentOrders = Checkouts::latest()->select([
                'id',
                'customer_name',
                'metadata',
                'payment_date',
                'payment_status',
            ])->take(10)->get();

            $response = response()->json([
                'status' => 'success',
                'data' => [
                    'popularItems' => $popularItems,
                    'recentReviews' => $recentReviews,
                    'recentOrders' => $recentOrders
                ]
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

}
