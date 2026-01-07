<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use Providers\AppServiceProvider;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HotelTagController;
use App\Http\Controllers\RoomTagController;
use App\Http\Controllers\ServiceController;

use Illuminate\Support\Facades\Http;

Route::get('/ping', function () {
    $alma = HTTP::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    return response()->json(['message' => 'pong', $alma], 200);
});

//USER VÉGPONTOK

Route::post('/auth/register-user', [AuthController::class, 'registerUser']);
Route::post('/auth/register-hotel', [AuthController::class, 'registerHotel']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
//Route::get('/auth/test', [AuthController::class, 'testAuth'])->middleware('auth:sanctum');
Route::get('/auth/user/{id}', [AuthController::class, 'getUserById'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/auth/updateuser/{id}', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('/auth/deleteuser/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');

//HOTEL VÉGPONTOK
Route::get('/hotels', [HotelController::class, 'getHotels']);
Route::get('/hotels/{id}', [HotelController::class, 'getHotelById']);
Route::put('/hotels/upgrade/{id}', [HotelController::class, 'upgradeHotel'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/hotels/delete/{id}', [HotelController::class, 'deleteHotel'])->middleware('auth:sanctum', 'role:hotel');

//ROOM VÉGPONTOK
Route::get('/rooms/hotel/{hotel_id}', [RoomController::class, 'getRoomsByHotelId']);
Route::get('/rooms/{id}', [RoomController::class, 'getRoomById']);
Route::post('/rooms/create/{hotel_id}', [RoomController::class, 'createRoom'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/rooms/delete/{id}', [RoomController::class, 'deleteRoom'])->middleware('auth:sanctum', 'role:hotel');
Route::put('/rooms/update/{id}', [RoomController::class, 'updateRoom'])->middleware('auth:sanctum', 'role:hotel');

//SERVICE VÉGPONTOK
Route::get('/services/hotel/{hotel_id}', [ServiceController::class, 'getServicesByHotelId']);
Route::get('/services/{id}', [ServiceController::class, 'getServiceById']);
Route::post('/services', [ServiceController::class, 'createService'])->middleware('auth:sanctum', 'role:hotel');
Route::put('/services/{id}', [ServiceController::class, 'updateService'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/services/{id}', [ServiceController::class, 'deleteService'])->middleware('auth:sanctum', 'role:hotel');

//IDE MÉG JÖN EGY ELÉRHETŐSÉG ELLENŐRZÉS VÉGPONT

//FOGLALÁS VÉGPONTOK

Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth:sanctum');
Route::get('/bookings/user/{userId}', [BookingController::class, 'getBookingsByUserId'])->middleware('auth:sanctum');
Route::delete('/bookings/delete/{id}', [BookingController::class, 'deleteBooking'])->middleware('auth:sanctum');
Route::put('/bookings/update-status/{id}', [BookingController::class, 'updateStatus']);//->middleware('auth:sanctum');

//GUEST VÉGPONTOK
Route::post('/bookings/add-guest/{bookingId}', [BookingController::class, 'addGuests'])->middleware('auth:sanctum');
Route::get('/guests/booking/{bookingId}', [BookingController::class, 'getGuestsByBookingId'])->middleware('auth:sanctum');
Route::put('/guests/update/{id}', [GuestController::class, 'updateGuest'])->middleware('auth:sanctum');
Route::delete('/guests/delete/{id}', [GuestController::class, 'deleteGuest'])->middleware('auth:sanctum');

//DEVICE VÉGPONTOK
Route::get('/devices/bookings/{hotelId}', [DeviceController::class, 'getBookings']);
Route::put('/devices/update-booking/{bookingId}', [DeviceController::class, 'updateData']);

//IMAGE VÉGPONTOK

Route::post('/images', [ImageController::class, 'store'])->middleware('auth:sanctum', 'role:hotel');
Route::post('/images/link', [ImageController::class, 'link'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/images/unlink', [ImageController::class, 'unlink'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/images/{id}', [ImageController::class, 'destroy'])->middleware('auth:sanctum', 'role:hotel');

Route::get('/rooms/{roomId}/images', [ImageController::class, 'roomImages']);
Route::get('/images/{id}', [ImageController::class, 'show']);

//SEARCH VÉGPONTOK
Route::get('/search', [SearchController::class, 'searchWithPlans']);

//TAG-ek hozzáfűzése

Route::middleware('auth:sanctum')->group(function () {
    // HOTEL TAGS
    Route::post('/hotels/{hotel}/tags', [HotelTagController::class, 'store']);
    Route::delete('/hotels/{hotel}/tags/{tag}', [HotelTagController::class, 'destroy']);

    // ROOM TAGS
    Route::post('/rooms/{room}/tags', [RoomTagController::class, 'store']);
    Route::delete('/rooms/{room}/tags/{tag}', [RoomTagController::class, 'destroy']);
});
