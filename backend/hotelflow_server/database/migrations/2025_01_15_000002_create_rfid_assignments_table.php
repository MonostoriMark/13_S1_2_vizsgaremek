<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfid_key_id')->constrained('rfidKeys')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            // Ensure one key can only be assigned to one active booking at a time
            $table->index(['rfid_key_id', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_assignments');
    }
};
