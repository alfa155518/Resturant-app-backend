<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
    // Path to the JSON file
        // Check if the file exists in the storage/app directory
        $jsonPath = storage_path('app/menue.json');
        
        // If the file doesn't exist in storage/app, try the database/seeders/data directory
        if (!File::exists($jsonPath)) {
            $jsonPath = database_path('seeders/data/menue.json');
        }
        
        // If the file still doesn't exist, try the public directory
        if (!File::exists($jsonPath)) {
            $jsonPath = public_path('data/menue.json');
        }
        
        // Verify the file exists before attempting to read it
        if (!File::exists($jsonPath)) {
            $this->command->error('Menu JSON file not found. Please place menu.json in storage/app, database/seeders/data, or public/data directory.');
            return;
        }
        
        $json = File::get($jsonPath);
        $menuItems = json_decode($json, true);

        foreach ($menuItems as $item) {
            Menu::updateOrCreate(
                [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'category' => $item['category'],
                    'popular' => $item['popular'],
                    'rating' => $item['rating'],
                    'prepTime' => $item['prepTime'],
                    'calories' => $item['calories'],
                    'dietary' => $item['dietary'],
                    'ingredients' => $item['ingredients'],
                    'stock' => $item['stock'],
                ]
            );
        }
    }
}
