<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $companies = json_decode(File::get(database_path('seeders/data/company.json')), true);

        foreach ($companies as $company) {
            DB::table('restaurant_infos')->insert([
                'name' => $company['name'],
                'address' => $company['address'],
                'phone' => $company['phone'],
                'email' => $company['email'],
                'website' => $company['website'],
                'logo' => $company['logo'],
                'message' => $company['message'],
                'description' => $company['description'],
                'timezone' => $company['timezone'] ?? 'UTC',
                'is_active' => $company['is_active'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
