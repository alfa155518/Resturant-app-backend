<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\handelUploadPhoto;
use App\Helpers\ValidateId;
use App\Http\Controllers\Controller;
use App\Models\Admin\MenuItems;
use App\Traits\ConvertToJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuItemsController extends Controller
{
    use ConvertToJson;
    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }
    /**
     * Get paginated menu items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuItems(Request $request)
    {
        try {
            // Get query parameters with defaults
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 20);

            // Get paginated menu items with caching
            $data = MenuItems::getPaginatedMenuItems($page, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching menu items: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Update a menu item
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenuItem(Request $request, $id)
    {
        try {
            $menuItem = MenuItems::find($id);

            if (!$menuItem) {
                return self::notFound('Menu item');
            }

            $updateData = [];

            // Handle image upload if present
            if ($request->hasFile('image')) {
                MenuItems::handleImageUpdate($request, $menuItem, $this->uploadHandler, $updateData);
            }

            // Get all request data except image and _method
            $requestData = $request->except(['image', '_method']);

            // Filter out undefined/null values and prepare data for update
            $updateData = array_merge($updateData, array_filter($requestData, function ($value) {
                return $value !== 'undefined' && $value !== null;
            }));

            // Convert boolean string values to integers
            $booleanFields = ['available', 'popular', 'featured'];
            foreach ($booleanFields as $field) {
                if (isset($updateData[$field])) {
                    $updateData[$field] = filter_var($updateData[$field], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                }
            }

            // Cast other numeric fields to appropriate types
            $numericFields = [
                'price' => 'float',
                'calories' => 'int',
                'rating' => 'float',
                'stock' => 'int',
            ];

            foreach ($numericFields as $field => $type) {
                if (isset($updateData[$field])) {
                    if ($type === 'float') {
                        $updateData[$field] = (float) $updateData[$field];
                    } elseif ($type === 'int') {
                        $updateData[$field] = (int) $updateData[$field];
                    }
                }
            }

            // Update the menu item
            $menuItem->update($updateData);

            // Invalidate cache
            MenuItems::invalidateMenuCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu item updated successfully',
                'data' => $menuItem->fresh()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::notFound('Menu item');
        } catch (\Exception $e) {
            Log::error('Error updating menu item: ' . $e->getMessage(), [
                'menu_item_id' => $id,
                'exception' => $e
            ]);
            return self::serverError();
        }
    }


    /**
     * Create a new menu item
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMenuItem(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), MenuItems::$rules, MenuItems::$messages);

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Convert boolean string values to integers
            $booleanFields = ['available', 'popular', 'featured'];
            foreach ($booleanFields as $field) {
                if (isset($validatedData[$field])) {
                    $validatedData[$field] = filter_var($validatedData[$field], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                }
            }

            // Process dietary and ingredients convert to json
            if (isset($validatedData['dietary'])) {
                $this->convertToJson($validatedData, 'dietary');
            }
            if (isset($validatedData['ingredients'])) {
                $this->convertToJson($validatedData, 'ingredients');
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                MenuItems::handleUploadItemImage($request, $this->uploadHandler, $validatedData);
            }

            // Create the menu item
            $menuItem = MenuItems::create($validatedData);

            // Invalidate cache
            MenuItems::invalidateMenuCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu item created successfully',
                'data' => $menuItem
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating menu item: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Delete a menu item by ID
     *
     * @param int|string $id The ID of the menu item to delete
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Delete a menu item
     *
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMenuItem($id)
    {
        try {
            // Validate ID is numeric and positive
            $validateId = ValidateId::validateNumeric($id);

            if ($validateId) {
                return $validateId;
            }

            $menuItem = MenuItems::find($id);

            if (!$menuItem) {
                return self::notFound('Menu item');
            }

            // Store references to image data before deletion if needed for cleanup
            $imagePublicId = $menuItem->image_public_id;

            // Delete the menu item
            if (!$menuItem->delete()) {
                throw new \RuntimeException('Failed to delete menu item');
            }

            // Delete image from storage
            if ($imagePublicId) {
                MenuItems::deleteItemImage($imagePublicId, $this->uploadHandler, $id);
            }

            // Invalidate cache after successful deletion
            MenuItems::invalidateMenuCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu item deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting menu item: ' . $e->getMessage(), [
                'menu_item_id' => $id,
                'exception' => $e
            ]);
            return self::serverError();
        }
    }
}