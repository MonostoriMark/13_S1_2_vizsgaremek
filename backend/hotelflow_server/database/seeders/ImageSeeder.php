<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Image;
use Faker\Factory as Faker;

class ImageSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $hotels = Hotel::with('rooms')->get();

        foreach ($hotels as $hotel) {
            $rooms = $hotel->rooms;

            if ($rooms->isEmpty()) continue;

            // Minden hotelhez 3–6 kép
            $numImages = rand(3, 6);

            for ($i = 0; $i < $numImages; $i++) {

                // Kép létrehozása
                $image = Image::create([
                    'url' => 'https://picsum.photos/800/600?random=' . rand(1, 10000)
                ]);

                // Véletlenszerűen 1–3 szobához kapcsoljuk
                $numRooms = min(rand(1, 3), $rooms->count());
                $roomIds = $rooms->random($numRooms)->pluck('id')->toArray();

                $image->rooms()->sync($roomIds);
            }
        }

        $this->command->info('Images + relations seeded successfully!');
    }
}
