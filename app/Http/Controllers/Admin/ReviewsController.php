<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{
    public function reviews()
    {
        try {
            // Retrieve reviews with user information
            $reviews = Reviews::get();

            return response()->json([
                'status' => 'success',
                'reviews' => $reviews,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching reviews: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch reviews',
            ], 500);
        }
    }

    /**
     * Update the specified review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateReview(Request $request, $id)
    {
        try {
            // Find the review
            $review = Reviews::findOrFail($id);

            // Validate request data
            $validated = Validator::make($request->all(), [
                'reply' => 'nullable|string|max:1000',
                'status' => 'string|in:Published,Hidden',
            ]);

            if ($validated->fails()) {
                return self::validationFailed($validated->errors()->first());
            }


            // Update only the allowed fields
            $review->update([
                'reply' => $request->reply ?? null,
                'status' => $request->status ?? $review->status,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Review updated successfully',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::notFound('Review');

        } catch (\Exception $e) {
            Log::error('Error updating review: ' . $e->getMessage());
            return self::serverError();
        }
    }

    /**
     * Delete the specified review.
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteReview($id)
    {
        try {

            // Find the review or fail
            $review = Reviews::findOrFail($id);

            // Perform the deletion
            $deleted = $review->delete();

            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete review. Please try again later.'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::notFound('Review');

        } catch (\Exception $e) {
            Log::error('Error deleting review: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete review. Please try again later.'
            ], 500);
        }
    }
}