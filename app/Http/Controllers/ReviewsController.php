<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateId;
use App\Helpers\SecurityHeaders;
use App\Traits\UserId;
use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{

    use UserId;

    /**
     * Get all reviews.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
            $reviews = Reviews::query()->where('status', 'Published')
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

    /**
     * Add a new review.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addReview(Request $request)
    {
        $userId = $this->getUserId($request);
        try {

            // Check if user has already submitted a review
            $existingReview = Reviews::where('user_id', $userId)->first();
            if ($existingReview) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already submitted a review.'
                ], 422);
            }

            $validateReview = Validator::make($request->all(), Reviews::rules(), Reviews::messages());
            if ($validateReview->fails()) {
                return self::validationFailed($validateReview->errors()->first());
            }

            $validatedData = $validateReview->validated();

            Reviews::firstOrCreate([
                'user_id' => $userId,
                'client_name' => $validatedData['client_name'],
                'client_email' => $validatedData['client_email'],
                'rating' => $validatedData['rating'],
                'review' => $validatedData['review'],
            ]);

            Cache::forget('admin_reviews');

            $response = response()->json([
                'status' => 'success',
                'message' => 'thanks For Review'
            ], 200)->header('Cache-Control', 'public, max-age=300');
            // Apply security headers
            return SecurityHeaders::secureHeaders($response);

        } catch (\Exception $e) {
            Log::error('Reviews retrieval error: ' . $e->getMessage());
            return self::serverError();
        }
    }
}
