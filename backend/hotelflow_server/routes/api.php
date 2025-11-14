<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;


Route::post('/auth/register-user', [AuthController::class, 'registerUser']);
Route::post('/auth/register-hotel', [AuthController::class, 'registerHotel']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/test', [AuthController::class, 'testAuth'])->middleware('auth:sanctum');
Route::get('/auth/user/{id}', [AuthController::class, 'getUserById'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/auth/updateuser/{id}', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('/auth/deleteuser/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');


Route::get('/hotels', [HotelController::class, 'getHotels']);
