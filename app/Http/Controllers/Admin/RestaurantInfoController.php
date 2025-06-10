<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\handelUploadPhoto;
use App\Http\Controllers\Controller;
use App\Models\Admin\RestaurantInfo;
use App\Traits\AdminSecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Log;


class RestaurantInfoController extends Controller
{
    use AdminSecurityHeaders;
    protected $cacheKey = 'restaurant_info';

    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Get restaurant info
     * 
     * @return mixed
     */
    public function getRestaurantInfo()
    {
        // Try to get from cache first
        $restaurantInfo = Cache::rememberForever($this->cacheKey, function () {
            return RestaurantInfo::first();
        });

        if (!$restaurantInfo) {
            return self::notFound('Restaurant info');
        }

        $response = response()->json([
            'status' => 'success',
            'data' => $restaurantInfo
        ], 200);

        return $this->adminSecurityHeaders($response);

    }

    /**
     * Update restaurant info
     * 
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function updateInfo(Request $request, $id)
    {
        try {
            $restaurantInfo = RestaurantInfo::find($id);

            if (!$restaurantInfo) {
                return self::notFound('Restaurant info');
            }

            $validated = Validator::make($request->all(), RestaurantInfo::$rules);

            if ($validated->fails()) {
                return self::validationFailed($validated->errors()->first());
            }

            $validatedData = $validated->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $uploadResult = RestaurantInfo::handleUploadLogo($request, $this->uploadHandler, $restaurantInfo);
                $validatedData = array_merge($validatedData, $uploadResult);
            }

            // Update the restaurant info
            $restaurantInfo->update($validatedData);

            // Clear the cache after update
            RestaurantInfo::clearCache($this->cacheKey);

            $response = response()->json([
                'status' => 'success',
                'message' => 'Restaurant info updated successfully',
                // 'data' => $restaurantInfo->fresh()
            ], 200);

            return $this->adminSecurityHeaders($response);


        } catch (\Exception $e) {
            Log::error('Failed to update restaurant info: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }
}