<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Kézzel pár fix user
        User::create([
            'name' => 'Kiss József',
            'email' => 'jozsef.kiss@example.com',
            'password' => Hash::make('Password123'),
        ]);

        User::create([
            'name' => 'Nagy Lilla',
            'email' => 'lilla.nagy@example.com',
            'password' => Hash::make('Password123'),
        ]);

        // + 5 faker user
        User::factory()->count(5)->create();
    }
}
