<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHeaders;
use App\Helpers\ValidateId;
use App\Models\CartItems;
use App\Traits\UserCartItems;
use App\Traits\UserId;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemsController extends Controller
{
    use UserId, UserCartItems;
    public function addProductToCart(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'menu_id' => 'required|exists:menus,id',
                'quantity' => 'required|integer|min:1|max:20',
                'price' => 'required|numeric|min:0',
                'attributes' => 'nullable',
            ]);

            if ($validatedData->fails()) {
                return self::validationFailed($validatedData->errors());
            }

            $product = $validatedData->validate();

            // Process attributes if they're a string
            if (isset($product['attributes']) && is_string($product['attributes'])) {
                try {
                    $product['attributes'] = json_decode($product['attributes'], true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    // If not valid JSON, try to parse as string array
                    $product['attributes'] = array_map('trim', explode(
                        ',',
                        str_replace(['[', ']', "'", '"'], '', $product['attributes'])
                    ));
                }
            }

            // Check if the product already exists in the user's cart
            $existingCartItem = CartItems::where('user_id', $product['user_id'])
                ->where('menu_id', $product['menu_id'])
                ->first();

            if ($existingCartItem) {
                $response = response()->json([
                    'status' => 'error',
                    'message' => 'Product already exists in the cart',
                ], 209);

                return SecurityHeaders::secureHeaders($response);
            }
            // Create a new cart item
            $cartItem = CartItems::create($product);

            // Calculate the total using the new function
            $total = $cartItem->getTotal();

            $cartItem->total = $total;

            $cartItem->save();


            $response = response()->json([
                'message' => 'Product added to cart successfully',
                'cartItems' => $cartItem,
                'total' => $total
            ], 201);

            return SecurityHeaders::secureHeaders($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getCartItems(Request $request)
    {
        try {

            $userId = $this->getUserId($request);

            // Retrieve cart items for the authenticated user
            $cartItems = $this->getUserCartItems($userId);

            $response = response()->json([
                'message' => "Items Receives Successful",
                'cartItems' => $cartItems
            ], 200);

            // cache control headers
            $response->header('Cache-Control', 'private, no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve cart items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addOrMinusQuantity(Request $request, $id)
    {

        $notValidId = ValidateId::validateNumeric($id);

        if ($notValidId) {
            return ValidateId::validateNumeric($id);
        }

        // Only validate what's needed - simplified validation
        $validator = Validator::make(['quantity' => $request->input('quantity')], [
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return self::validationFailed($validator->errors());
        }

        $userId = $this->getUserId($request);

        // Update the cart item
        try {
            $quantity = (int) $request->input('quantity');
            $product = CartItems::where('user_id', $userId)
                ->where('menu_id', $id)
                ->first();

            if (!$product) {
                return self::notFound('Product');
            }

            // Update & save data
            $product->quantity = $quantity;
            $product->total = $product->getTotal();
            $product->save();

            $response = response()->json([
                'status' => "success",
                'message' => "Cart item updated to $quantity  successfully.",
            ], 200);

            return SecurityHeaders::secureHeaders($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function removeProductFromCart(Request $request, $id)
    {
        try {

            // Validate the ID is numeric and valid
            $notValidId = ValidateId::validateNumeric($id);

            if ($notValidId) {
                return $notValidId;
            }

            // Get authenticated user ID
            $userId = $this->getUserId($request);

            // Use transaction for data consistency
            return DB::transaction(function () use ($userId, $id) {
                $product = CartItems::where('user_id', $userId)
                    ->where('menu_id', $id)
                    ->lockForUpdate() // Prevent race conditions
                    ->first();

                if (!$product) {
                    return self::notFound('item');
                }

                // Soft delete item
                $product->delete();

                $response = response()->json([
                    'status' => 'success',
                    'message' => 'Item successfully removed from cart',
                ], 200);

                // Apply security headers
                return SecurityHeaders::secureHeaders($response);

            });

        } catch (\Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
;
