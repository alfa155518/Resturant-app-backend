<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Models\FavoriteProducts;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FavoriteProductsController extends Controller
{
    use UserId;

    /**
     * Add a product to user's favorites
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addFavoriteProduct(Request $request): JsonResponse
    {
        try {
            // Get authenticated user ID from token - this is more secure than passing user_id in request
            $userId = $this->getUserId($request);

            // Validate only what's needed - removed user_id from validation as we get it from token
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:menus,id',
            ]);

            $productId = $validated['product_id'];

            // Use database transaction for data integrity
            return DB::transaction(function () use ($userId, $productId) {
                // Business logic moved to model
                $result = FavoriteProducts::addToFavorites($userId, $productId);
                
                if ($result['created']) {
                    $response = response()->json([
                        'status' => 'success',
                        'message' => 'Product added to favorites'
                    ], 201);
                } else {
                    $response = response()->json([
                        'status' => 'info',
                        'message' => 'Product already in favorites'
                    ], 409);
                }
                
                return SecurityHeaders::secureHeaders($response);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the exception for monitoring but don't expose details to client
            Log::error('Failed to add favorite product', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add product to favorites'
            ], 500);
        }
    }

    /**
     * Remove a product from user's favorites
     *
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     */
    public function removeFavoriteProduct(Request $request, int $productId): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            // Business logic moved to model
            $deleted = FavoriteProducts::removeFromFavorites($userId, $productId);

            if ($deleted) {
                $response = response()->json([
                    'status' => 'success',
                    'message' => 'Product removed from favorites'
                ]);
            } else {
                $response = response()->json([
                    'status' => 'info',
                    'message' => 'Product was not in favorites'
                ], 404);
            }
            
            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            Log::error('Failed to remove favorite product', [
                'user_id' => $userId ?? null,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove product from favorites'
            ], 500);
        }
    }

    /**
     * Get user's favorite products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserFavorites(Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            
            // Business logic moved to model
            $favorites = FavoriteProducts::getUserFavorites($userId);

            $response = response()->json([
                'status' => 'success',
                'data' => $favorites
            ]);

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve favorite products', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve favorite products'
            ], 500);
        }
    }
}
