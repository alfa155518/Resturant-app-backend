<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\NotificationSettings;
use App\Traits\AdminSecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Log;

class NotificationSettingsController extends Controller
{
    use AdminSecurityHeaders;
    /**
     * Get notification settings with caching
     * Cache duration is set to 1 day (1440 minutes)
     * Cache key: 'notification_settings'
     */
    public function getNotificationSettings()
    {
        $cacheKey = 'notification_settings';

        $notificationSettings = Cache::rememberForever($cacheKey, function () {
            return NotificationSettings::first();
        });

        if (!$notificationSettings) {
            return self::notFound('Notification settings');
        }

        $response = response()->json([
            'status' => 'success',
            'data' => $notificationSettings
        ], 200);

        return $this->adminSecurityHeaders($response);
    }


    /**
     * Update notification settings
     * @param Request $request
     * @return mixed
     */
    public function updateNotificationSettings(Request $request)
    {
        $notificationSettings = NotificationSettings::first();

        if (!$notificationSettings) {
            return self::notFound('Notification settings');
        }

        try {
            $validator = Validator::make($request->all(), $notificationSettings::$rules);

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            $notificationSettings->update($validatedData);

            $notificationSettings::clearNotificationSettingsCache();

            $response = response()->json([
                'status' => 'success',
                'message' => 'Notification settings updated successfully',
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            Log::error('Failed to update notification settings: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }
}
