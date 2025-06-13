<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        $json = file_get_contents(database_path('seeders/data/payment_methods.json'));
        $methods = json_decode($json, true);

        foreach ($methods as $method) {
            DB::table('payment_methods')->insert([
                'restaurant_infos_id' => $method['restaurant_infos_id'],
                'credit_card' => $method['credit_card'],
                'debit_card' => $method['debit_card'],
                'cash' => $method['cash'],
                'paypal' => $method['paypal'],
                'apple_pay' => $method['apple_pay'],
                'google_pay' => $method['google_pay'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
