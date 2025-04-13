<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateId;
use App\Helpers\SecurityHeaders;

use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewsController extends Controller
{
    public function getAllReviews()
    {
        try {
            // Validate pagination parameters
            $page = request()->input('page', 1);
            $notValidId = ValidateId::validateNumeric($page);
            if ($notValidId) {
                return $notValidId;
            }

            // Retrieve reviews with user information
            $reviews = Reviews::query()
                ->select(['id', 'user_id', 'client_name', 'rating', 'review', 'created_at'])
                ->with([
                    'user' => function ($query) {
                        $query->select(['id', 'name', 'avatar']);
                    }
                ])
                ->paginate(11);

            // Return reviews data
            $response = response()->json([
                'status' => 'success',
                'data' => [
                    'current_page' => $reviews->currentPage(),
                    'total' => $reviews->total(),
                    'reviews' => $reviews->items(),
                    'per_page' => $reviews->perPage(),
                    'last_page' => $reviews->lastPage(),
                ]
            ], 200)->header('Cache-Control', 'public, max-age=300');
              // Apply security headers
            return SecurityHeaders::secureHeaders($response);

        } catch (\Exception $e) {
            Log::error('Reviews retrieval error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve reviews'
            ], 500);
        }
    }
}
