<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RestaurantNotificationSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(database_path('seeders/data/restaurant_notifications.json'));
        $notifications = json_decode($json, true);

        foreach ($notifications as $notify) {
            DB::table('notification_settings')->insert([
                'restaurant_infos_id' => $notify['restaurant_infos_id'],
                'new_order' => $notify['new_order'],
                'order_status' => $notify['order_status'],
                'new_reservation' => $notify['new_reservation'],
                'reservation_reminder' => $notify['reservation_reminder'],
                'new_review' => $notify['new_review'],
                'low_inventory' => $notify['low_inventory'],
                'daily_summary' => $notify['daily_summary'],
                'weekly_summary' => $notify['weekly_summary'],
                'marketing_emails' => $notify['marketing_emails'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
