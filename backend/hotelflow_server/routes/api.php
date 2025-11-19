<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use Providers\AppServiceProvider;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuestController;


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
//IDE MÉG JÖN EGY ELÉRHETŐSÉG ELLENŐRZÉS VÉGPONT

//FOGLALÁS VÉGPONTOK

Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth:sanctum');
Route::post('/bookings/add-guest/{bookingId}', [BookingController::class, 'addGuests'])->middleware('auth:sanctum');

//GUEST VÉGPONTOK
Route::put('/guests/update/{id}', [GuestController::class, 'updateGuest'])->middleware('auth:sanctum');
