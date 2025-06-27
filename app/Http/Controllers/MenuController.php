<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateId;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    // Get menu Dishes
    public function menu(Request $request)
    {
        try {
            // Get query parameters with defaults
            $perPage = $request->query('per_page', 20);
            $query = Menu::query();
            $menuDishes = $query->paginate($perPage);
            return response()->json([
                'message' => 'success',
                'data' => [
                    'current_page' => $menuDishes->currentPage(),
                    'dishes' => $menuDishes->items(),
                    'per_page' => $menuDishes->perPage(),
                    'total' => $menuDishes->total(),
                    'last_page' => $menuDishes->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get menu',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get menu Dish by id
    public function dish($id)
    {
        try {
            // Validate ID is numeric to prevent injection attacks
            $notValidId = ValidateId::validateNumeric($id);
            if ($notValidId) {
                return $notValidId;
            }
            // Retrieve the dish
            $dish = Menu::findOrFail((int) $id);
            // $dish = Cache::remember('dish_'.$id, 3600, function() use ($id) {
            //     return Menu::findOrFail((int)$id);
            // });
            return response()->json([
                'status' => 'success',
                'dish' => $dish,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dish not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving dish: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the dish',
            ], 500);
        }
    }
}
