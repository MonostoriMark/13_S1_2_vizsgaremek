<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        // HOTELS
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('location');
            $table->text('description')->nullable();
            $table->enum('type', ['hotel', 'apartment', 'villa', 'other']);
            $table->integer('starRating')->nullable();
            $table->timestamp('createdAt')->useCurrent();
        });

        // ROOMS
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotels_id')->constrained('hotels')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('pricePerNight');
            $table->integer('capacity');
            $table->decimal('basePrice', 10, 2);
            $table->timestamp('createdAt')->useCurrent();
        });

        // RFID KEYS
        Schema::create('rfidKeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotels_id')->constrained('hotels')->onDelete('cascade');
            $table->boolean('isUsed')->default(false);
            $table->string('rfidKey')->unique();
        });

        // RFID CONNECTION
        Schema::create('rfidKeyConnection', function (Blueprint $table) {
            $table->id();
            $table->string('rfidKeys_id');
            $table->unsignedBigInteger('rooms_id');

            $table->foreign('rfidKeys_id')->references('rfidKey')->on('rfidKeys')->onDelete('cascade');
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        // BOOKINGS
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hotels_id')->constrained('hotels')->onDelete('cascade');
            $table->date('startDate');
            $table->date('endDate');
            $table->integer('totalPrice');
            $table->string('checkInToken')->nullable();
            $table->enum('checkInstatus', ['checkedOut', 'checkedIn'])->nullable();
            $table->timestamp('checkInTime')->nullable();
            $table->timestamp('checkOutTime')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'finished'])->default('pending');
            $table->timestamp('createdAt')->useCurrent();
        });

        // BOOKINGS RELATION (rooms <-> bookings)
        Schema::create('bookingsRelation', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('rooms_id');
            $table->primary(['booking_id', 'rooms_id']);

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        // REVIEWS
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamp('createdAt')->useCurrent();
        });

        // IMAGES
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('url');
        });

        // IMAGES RELATION (rooms <-> images)
        Schema::create('imagesRelation', function (Blueprint $table) {
            $table->unsignedBigInteger('images_id');
            $table->unsignedBigInteger('rooms_id');
            $table->primary(['images_id', 'rooms_id']);

            $table->foreign('images_id')->references('id')->on('images')->onDelete('cascade');
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        // GUESTS
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('idNumber');
            $table->string('name');
            $table->date('dateOfBirth');
            $table->foreignId('bookings_id')->constrained('bookings')->onDelete('cascade');
            $table->timestamp('createdAt')->useCurrent();
        });

        // SERVICES
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price');
            $table->foreignId('hotels_id')->nullable()->constrained('hotels')->onDelete('cascade');
        });

        // SERVICES RELATION (bookings <-> services)
        Schema::create('servicesRelation', function (Blueprint $table) {
            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('bookings_id');
            $table->primary(['services_id', 'bookings_id']);

            $table->foreign('services_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('bookings_id')->references('id')->on('bookings')->onDelete('cascade');
        });

        // SERVICE TAGS
        Schema::create('serviceTags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        // HOTEL TAG RELATION (hotels <-> tags)
        Schema::create('hotelTagRelation', function (Blueprint $table) {
            $table->unsignedBigInteger('hotels_id');
            $table->unsignedBigInteger('serviceTags_id');
            $table->primary(['hotels_id', 'serviceTags_id']);

            $table->foreign('hotels_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->foreign('serviceTags_id')->references('id')->on('serviceTags')->onDelete('cascade');
        });

        // ROOM TAG RELATION (rooms <-> tags)
        Schema::create('roomTagRelation', function (Blueprint $table) {
            $table->unsignedBigInteger('rooms_id');
            $table->unsignedBigInteger('serviceTags_id');
            $table->primary(['rooms_id', 'serviceTags_id']);
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('serviceTags_id')->references('id')->on('serviceTags')->onDelete('cascade');
        });

        // DEVICES
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotels_id')->nullable()->constrained('hotels')->onDelete('cascade');
            $table->integer('deviceApiValidation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
        Schema::dropIfExists('roomTagRelation');
        Schema::dropIfExists('hotelTagRelation');
        Schema::dropIfExists('serviceTags');
        Schema::dropIfExists('servicesRelation');
        Schema::dropIfExists('services');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('imagesRelation');
        Schema::dropIfExists('images');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('bookingsRelation');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('rfidKeyConnection');
        Schema::dropIfExists('rfidKeys');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('hotels');
        Schema::dropIfExists('users');
    }
};
