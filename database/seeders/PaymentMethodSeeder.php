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
                'name' => $method['name'],
                'enabled' => $method['enabled'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
