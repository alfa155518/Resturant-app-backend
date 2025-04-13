<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
class TablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     // Get the JSON file path
     $jsonPath = database_path('seeders/data/tables.json');
        
     // Check if file exists
     if (!File::exists($jsonPath)) {
         throw new \Exception('Tables JSON file not found at: ' . $jsonPath);
     }

     // Read and decode JSON file
     $jsonData = File::get($jsonPath);
     $tables = json_decode($jsonData, true);

     if (json_last_error() !== JSON_ERROR_NONE) {
         throw new \Exception('Error decoding JSON: ' . json_last_error_msg());
     }

     // Seed each table
     foreach ($tables as $tableData) {
        Table::create($tableData);
     }
    }
}
