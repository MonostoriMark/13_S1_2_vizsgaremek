<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make booking_id nullable so we can support manual room assignments
        // without linking to a specific booking.
        if (Schema::hasTable('rfid_assignments')) {
            DB::statement('ALTER TABLE rfid_assignments MODIFY booking_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Revert booking_id to NOT NULL (may fail if null rows exist)
        if (Schema::hasTable('rfid_assignments')) {
            DB::statement('ALTER TABLE rfid_assignments MODIFY booking_id BIGINT UNSIGNED NOT NULL');
        }
    }
};

