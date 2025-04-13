<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/team.json');
        
        // Check if file exists
        if (!File::exists($filePath)) {
            throw new \Exception("Team members data file not found at: " . $filePath);
        }

        // Read and decode JSON file
        $jsonData = File::get($filePath);
        $teamMembers = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error decoding JSON: " . json_last_error_msg());
        }

        // Insert each team member into the database
        foreach ($teamMembers as $member) {
            Team::create([
                'name' => $member['name'],
                'role' => $member['role'],
                'hire_date' => Carbon::parse($member['hire_date']),
                'salary' => $member['salary'],
                'email' => $member['email'],
                'image' => $member['image'],
                'bio' => $member['bio'],
                'is_active' => $member['is_active'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
