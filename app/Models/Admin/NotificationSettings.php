<?php

namespace App\Models\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    protected $fillable = [
        'restaurant_id',
        'new_order',
        'order_status',
        'new_reservation',
        'reservation_reminder',
        'new_review',
        'low_inventory',
        'daily_summary',
        'weekly_summary',
        'marketing_emails',
    ];

    protected $casts = [
        'new_order' => 'boolean',
        'order_status' => 'boolean',
        'new_reservation' => 'boolean',
        'reservation_reminder' => 'boolean',
        'new_review' => 'boolean',
        'low_inventory' => 'boolean',
        'daily_summary' => 'boolean',
        'weekly_summary' => 'boolean',
        'marketing_emails' => 'boolean',
    ];

    public static $rules = [
        'new_order' => 'required|boolean',
        'order_status' => 'required|boolean',
        'new_reservation' => 'required|boolean',
        'reservation_reminder' => 'required|boolean',
        'new_review' => 'required|boolean',
        'low_inventory' => 'required|boolean',
        'daily_summary' => 'required|boolean',
        'weekly_summary' => 'required|boolean',
        'marketing_emails' => 'required|boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(RestaurantInfo::class);
    }

    /**
     * Clear the notification settings cache
     * Call this method whenever the notification settings are updated
     */
    public static function clearNotificationSettingsCache()
    {
        Cache::forget('notification_settings');
    }
}
