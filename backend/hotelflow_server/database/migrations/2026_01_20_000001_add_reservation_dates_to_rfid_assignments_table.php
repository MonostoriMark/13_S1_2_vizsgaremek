<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfid_assignments', function (Blueprint $table) {
            $table->date('reserved_from')->nullable()->after('room_id');
            $table->date('reserved_to')->nullable()->after('reserved_from');

            // Speed up overlap checks (availability by date range)
            $table->index(['rfid_key_id', 'reserved_from', 'reserved_to']);
        });
    }

    public function down(): void
    {
        Schema::table('rfid_assignments', function (Blueprint $table) {
            $table->dropIndex(['rfid_key_id', 'reserved_from', 'reserved_to']);
            $table->dropColumn(['reserved_from', 'reserved_to']);
        });
    }
};

