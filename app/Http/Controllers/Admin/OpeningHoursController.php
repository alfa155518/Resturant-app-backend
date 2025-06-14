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
            $dayId = $request->input('dayId');

            $openingHours = OpeningHours::find($dayId);

            if (!$openingHours) {
                return self::notFound('Opening');
            }

            $validation = Validator::make($request->all(), [
                'dayId' => 'required|exists:opening_hours,id',
                'open' => 'required|date_format:H:i',
                'close' => 'required|date_format:H:i',
                'closed' => 'required|boolean',
            ]);

            if ($validation->fails()) {
                return self::validationFailed($validation->errors()->first());
            }

            $validated = $validation->validated();

            $openingHours->update($validated);

            OpeningHours::forgetOpeningHoursCache();

            $response = response()->json([
                'status' => 'success',
                'data' => $openingHours,
            ]);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return self::serverError();
        }
    }
}
