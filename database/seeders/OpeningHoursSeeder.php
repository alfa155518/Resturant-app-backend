<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpeningHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $hours = [
            ['day' => 'Monday', 'open' => '10:00', 'close' => '22:00', 'closed' => false],
            ['day' => 'Tuesday', 'open' => '10:00', 'close' => '22:00', 'closed' => false],
            ['day' => 'Wednesday', 'open' => '10:00', 'close' => '22:00', 'closed' => false],
            ['day' => 'Thursday', 'open' => '10:00', 'close' => '23:00', 'closed' => false],
            ['day' => 'Friday', 'open' => '10:00', 'close' => '23:00', 'closed' => false],
            ['day' => 'Saturday', 'open' => '11:00', 'close' => '23:00', 'closed' => false],
            ['day' => 'Sunday', 'open' => '11:00', 'close' => '22:00', 'closed' => false],
        ];

        DB::table('opening_hours')->insert($hours);

    }
}
