<?php

namespace Database\Seeders;

use App\Models\Reviews;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
class ReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get the reviews data from the JSON file
        $reviewsData = json_decode(File::get(database_path('seeders/data/reviews.json')), true);

        // Get all existing user IDs
        $existingUserIds = User::pluck('id')->toArray();

        foreach ($reviewsData as $reviewData) {
            // Check if user_id exists and is valid
            if (
                isset($reviewData['user_id']) &&
                $reviewData['user_id'] !== null &&
                !in_array($reviewData['user_id'], $existingUserIds)
            ) {
                // Set to null if user doesn't exist
                $reviewData['user_id'] = null;
            }

            // Create the review
            Reviews::create($reviewData);
        }
    }
}
