<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Review;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class FullDemoSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        DB::beginTransaction();
        try {
            // -------------------------
            // 1️⃣ Users
            // -------------------------
            $users = []; // vendégek
            for ($i = 0; $i < 50; $i++) {
                $users[] = User::create([
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => bcrypt('password'),
                    'role' => 'user',
                ]);
            }

            $hotelUsers = []; // szállodatulajok
            for ($i = 0; $i < 20; $i++) {
                $hotelUsers[] = User::create([
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => bcrypt('password'),
                    'role' => 'hotel',
                ]);
            }
            User::create([
                'name' => 'AdminUser',
                'email' => 'alma@alma.hu',
                'password' => bcrypt('password'),
                'role' => 'hotel',
            ]);

            // -------------------------
            // 2️⃣ Hotels, Rooms, Services
            // -------------------------
            $allHotels = [];
            foreach ($hotelUsers as $host) {
                $numHotels = rand(1, 3);
                for ($j = 0; $j < $numHotels; $j++) {
                    $hotel = Hotel::create([
                        'user_id' => $host->id, // csak hotel userhez
                        'location' => $faker->city,
                        'description' => $faker->sentence(),
                        'type' => $faker->randomElement(['hotel','apartment','villa','other']),
                        'starRating' => $faker->numberBetween(1,5),
                        
                    ]);
                    $allHotels[] = $hotel;

                    // Szobák 3-5/db
                    $numRooms = rand(3,5);
                    $rooms = [];
                    for ($k = 0; $k < $numRooms; $k++) {
                        $rooms[] = Room::create([
                            'hotels_id' => $hotel->id,
                            'name' => 'Room ' . $faker->unique()->numberBetween(100,999),
                            'description' => $faker->sentence(),
                            'pricePerNight' => $faker->numberBetween(50,300),
                            'capacity' => $faker->numberBetween(1,4),
                            'basePrice' => $faker->randomFloat(2,50,300),
                            
                        ]);
                    }

                    // Szolgáltatások 2-4/db
                    $numServices = rand(2,4);
                    for ($s = 0; $s < $numServices; $s++) {
                        Service::create([
                            'hotels_id' => $hotel->id,
                            'name' => $faker->words(2, true),
                            'description' => $faker->sentence(),
                            'price' => $faker->numberBetween(10,100),
                            
                        ]);
                    }
                }
            }

            // -------------------------
            // 3️⃣ Bookings, Guests, Reviews
            // -------------------------
            $allUsers = array_merge($users, $hotelUsers); // bárki foglalhat

            foreach ($allUsers as $user) {
                $numBookings = rand(1,3);
                for ($b = 0; $b < $numBookings; $b++) {
                    $hotel = $faker->randomElement($allHotels);
                    $rooms = $hotel->rooms()->inRandomOrder()->take(rand(1,2))->get();
                    if ($rooms->isEmpty()) continue;

                    $booking = Booking::create([
                        'users_id' => $user->id,
                        'startDate' => $faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
                        'endDate' => $faker->dateTimeBetween('+1 day', '+2 weeks')->format('Y-m-d'),
                        'totalPrice' => $rooms->sum('pricePerNight') * rand(1,5),
                        'status' => $faker->randomElement(['pending','confirmed','cancelled']),
                        
                    ]);

                    $booking->rooms()->sync($rooms->pluck('id')->toArray());

                    // Vendégek: szobák összkapacitása alapján
                    $totalCapacity = $rooms->sum('capacity');
                    $numGuests = rand(1, $totalCapacity);
                    for ($g = 0; $g < $numGuests; $g++) {
                        Guest::create([
                            'bookings_id' => $booking->id,
                            'name' => $faker->name,
                            'idNumber' => strtoupper($faker->bothify('??######')),
                            'dateOfBirth' => $faker->date('Y-m-d', '-18 years'),
                            
                        ]);
                    }

                    
                }
            }

            DB::commit();
            $this->command->info('Full demo data seeded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeder failed: ' . $e->getMessage());
        }
    }
}
