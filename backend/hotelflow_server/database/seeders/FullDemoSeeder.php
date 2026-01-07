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
use App\Models\RFIDKey;
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

            // Kézi felhasználók
            User::create([
                'name' => 'Monostori Márk',
                'email' => 'monostorimark05@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]);

            $user = User::create([
                'name' => 'Hotel Admin',
                'email' => 'hoteladmin@example.com',
                'password' => bcrypt('password'),
                'role' => 'hotel',
            ]);

            // -------------------------
            // 2️⃣ Hotel létrehozása ehhez a userhez
            // -------------------------
            $mainHotel = Hotel::create([
                'user_id' => $user->id,
                'location' => 'Budapest, Hungary',
                'description' => 'Egy szép hotel a város szívében.',
                'type' => 'hotel',
                'starRating' => 4,
            ]);

            // Szobák létrehozása a fő hotelhez
            $rooms = [
                [
                    'hotels_id' => $mainHotel->id,
                    'name' => 'Standard Room',
                    'description' => 'Kényelmes, klasszikus szoba két fő részére.',
                    'pricePerNight' => 10000,
                    'capacity' => 2,
                    'basePrice' => 10000,
                ],
                [
                    'hotels_id' => $mainHotel->id,
                    'name' => 'Deluxe Room',
                    'description' => 'Nagyobb szoba, extra kényelmi szolgáltatásokkal.',
                    'pricePerNight' => 15000,
                    'capacity' => 3,
                    'basePrice' => 15000,
                ],
                [
                    'hotels_id' => $mainHotel->id,
                    'name' => 'Suite',
                    'description' => 'Luxus lakosztály a pihenésre.',
                    'pricePerNight' => 25000,
                    'capacity' => 4,
                    'basePrice' => 25000,
                ],
            ];

            foreach ($rooms as $roomData) {
                Room::create($roomData);
            }

            // -------------------------
            // 3️⃣ Többi hotel létrehozása minden hotel userhez
            // -------------------------
            $allHotels = [$mainHotel]; // kezdjük a fő hotellel

            foreach ($hotelUsers as $host) {
                $numHotels = rand(1, 3);
                for ($j = 0; $j < $numHotels; $j++) {
                    $hotel = Hotel::create([
                        'user_id' => $host->id,
                        'location' => $faker->city,
                        'description' => $faker->sentence(),
                        'type' => $faker->randomElement(['hotel','apartment','villa','other']),
                        'starRating' => $faker->numberBetween(1,5),
                    ]);
                    $allHotels[] = $hotel;

                    // Szobák
                    $numRooms = rand(3,5);
                    for ($k = 0; $k < $numRooms; $k++) {
                        Room::create([
                            'hotels_id' => $hotel->id,
                            'name' => 'Room ' . $faker->unique()->numberBetween(100,999),
                            'description' => $faker->sentence(),
                            'pricePerNight' => $faker->numberBetween(50,300),
                            'capacity' => $faker->numberBetween(1,4),
                            'basePrice' => $faker->randomFloat(2,50,300),
                        ]);
                    }

                    // Szolgáltatások
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
            // 4️⃣ RFID kulcsok minden hotelhez
            // -------------------------
            foreach ($allHotels as $hotel) {
                // 5–10 RFID kulcs hotelenként
                $numKeys = rand(5, 10);
                for ($k = 0; $k < $numKeys; $k++) {
                    RFIDKey::create([
                        'hotels_id' => $hotel->id,
                        'isUsed' => false,
                        'rfidKey' => strtoupper($faker->bothify('########'))
                    ]);
                }
            }

            // -------------------------
            // 5️⃣ Bookings, Guests, Reviews
            // -------------------------
            $allUsers = array_merge($users, $hotelUsers);

            foreach ($allUsers as $user) {
                $numBookings = rand(1,3);
                for ($b = 0; $b < $numBookings; $b++) {
                    $hotel = $faker->randomElement($allHotels);
                    $rooms = $hotel->rooms()->inRandomOrder()->take(rand(1,2))->get();
                    if ($rooms->isEmpty()) continue;

                    $booking = Booking::create([
                        'hotels_id' => $hotel->id,
                        'users_id' => $user->id,
                        'startDate' => $faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
                        'endDate' => $faker->dateTimeBetween('+1 day', '+2 weeks')->format('Y-m-d'),
                        'totalPrice' => $rooms->sum('pricePerNight') * rand(1,5),
                        'status' => $faker->randomElement(['pending','confirmed','cancelled']),
                    ]);

                    $booking->rooms()->sync($rooms->pluck('id')->toArray());

                    // Vendégek a szobák kapacitása alapján
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
