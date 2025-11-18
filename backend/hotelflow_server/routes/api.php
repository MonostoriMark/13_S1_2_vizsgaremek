<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;

//USER VÉGPONTOK

Route::post('/auth/register-user', [AuthController::class, 'registerUser']);
Route::post('/auth/register-hotel', [AuthController::class, 'registerHotel']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/test', [AuthController::class, 'testAuth'])->middleware('auth:sanctum');
Route::get('/auth/user/{id}', [AuthController::class, 'getUserById'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/auth/updateuser/{id}', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('/auth/deleteuser/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');

//HOTEL VÉGPONTOK
Route::get('/hotels', [HotelController::class, 'getHotels']);
Route::get('/hotels/{id}', [HotelController::class, 'getHotelById']);
Route::put('/hotels/upgrade/{id}', [HotelController::class, 'upgradeHotel'])->middleware('auth:sanctum');
Route::delete('/hotels/delete/{id}', [HotelController::class, 'deleteHotel'])->middleware('auth:sanctum');

//ROOM VÉGPONTOK
Route::get('/rooms/hotel/{hotel_id}', [RoomController::class, 'getRoomsByHotelId']);
Route::get('/rooms/{id}', [RoomController::class, 'getRoomById']);
Route::post('/rooms/create/{hotel_id}', [RoomController::class, 'createRoom']);