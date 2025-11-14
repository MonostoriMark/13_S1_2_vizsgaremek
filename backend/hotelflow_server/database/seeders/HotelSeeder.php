<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\User;
use Faker\Factory as Faker;

class HotelSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $types = ['hotel', 'apartment', 'villa', 'other'];

        $users = User::all(); // hotel tulajdonosok (van user_id field)

        foreach (range(1, 12) as $i) {
            Hotel::create([
                'user_id' => $users->random()->id,
                'location' => $faker->city(),
                'description' => $faker->paragraph(3),
                'type' => $faker->randomElement($types),
                'starRating' => $faker->numberBetween(1, 5),
                'createdAt' => now(),
            ]);
        }
    }
}
