<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade')->unique();

            // Customer / billing data (captured at booking time)
            $table->string('customer_type')->default('private'); // private | business
            $table->string('full_name');
            $table->string('email');
            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address_line')->nullable();

            $table->text('note')->nullable(); // optional note for invoice

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_invoice_details');
    }
};

