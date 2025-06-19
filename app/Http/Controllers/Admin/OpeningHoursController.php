<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\OpeningHours;
use App\Traits\AdminSecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Log;

class OpeningHoursController extends Controller
{
    use AdminSecurityHeaders;

    /**
     * Get all opening hours.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOpeningHours()
    {
        try {
            $openingHours = Cache::rememberForever('openingHours', function () {
                return OpeningHours::all();
            });
            $response = response()->json([
                'status' => 'success',
                'data' => $openingHours,
            ]);
            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

    /**
     * Update opening hours.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOpeningHours(Request $request)
    {
        try {
            // Validate the incoming request
            $validation = Validator::make($request->all(), [
                'hours' => 'required|array',
                'hours.*.id' => 'required|exists:opening_hours,id',
                'hours.*.open' => 'required_if:hours.*.closed,false|date_format:H:i:s',
                'hours.*.close' => 'required_if:hours.*.closed,false|date_format:H:i:s',
                'hours.*.closed' => 'required|boolean',
            ]);

            if ($validation->fails()) {
                return self::validationFailed($validation->errors()->first());
            }

            $validated = $validation->validated();

            // Update each day's hours
            foreach ($validated['hours'] as $hourData) {
                $openingHours = OpeningHours::find($hourData['id']);

                if (!$openingHours) {
                    return self::notFound('hour');
                }

                $openingHours->update([
                    'open' => $hourData['open'],
                    'close' => $hourData['close'],
                    'closed' => $hourData['closed'],
                    'updated_at' => now()
                ]);
            }

            // Clear cache
            OpeningHours::forgetOpeningHoursCache();

            // Fetch updated hours
            $updatedHours = OpeningHours::all();

            $response = response()->json([
                'status' => 'success',
                'message' => 'Opening hours updated successfully',
                'data' => $updatedHours
            ]);
            return $this->adminSecurityHeaders($response);

        } catch (\Exception $e) {
            Log::error('Failed to update opening hours: ' . $e->getMessage());
            return self::serverError();
        }
    }
}
