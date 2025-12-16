<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceTag;

class tagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceTag::create(['name' => 'WiFi']);
        ServiceTag::create(['name' => 'TV']);
        ServiceTag::create(['name' => 'Légkondicionáló']);
        ServiceTag::create(['name' => 'Büfé']);
        ServiceTag::create(['name' => 'Parkoló']);
    }
}
