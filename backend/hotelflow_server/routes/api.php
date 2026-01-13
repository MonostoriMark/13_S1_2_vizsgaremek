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
use App\Http\Controllers\ServiceTagController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RFIDKeyController;
use App\Http\Controllers\InvoiceController;

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
Route::get('/auth/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
//Route::get('/auth/test', [AuthController::class, 'testAuth'])->middleware('auth:sanctum');
Route::get('/auth/user/{id}', [AuthController::class, 'getUserById'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/auth/updateuser/{id}', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::delete('/auth/deleteuser/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');
// Admin endpoint to update their own user data (including invoice fields)
Route::put('/auth/admin/profile', [AuthController::class, 'updateUserAdmin'])->middleware('auth:sanctum', 'role:hotel');

//HOTEL VÉGPONTOK
Route::get('/hotels', [HotelController::class, 'getHotels']);
Route::get('/hotels/{id}', [HotelController::class, 'getHotelById']);
Route::post('/hotels', [HotelController::class, 'createHotel'])->middleware('auth:sanctum', 'role:hotel');
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

//RFID KEY VÉGPONTOK
Route::middleware('auth:sanctum', 'role:hotel')->group(function () {
    Route::get('/rfid-keys', [RFIDKeyController::class, 'index']);
    Route::get('/rfid-keys/bookings', [RFIDKeyController::class, 'getAvailableBookings']);
    Route::get('/rfid-keys/{id}', [RFIDKeyController::class, 'show']);
    Route::post('/rfid-keys', [RFIDKeyController::class, 'store']);
    Route::patch('/rfid-keys/{id}', [RFIDKeyController::class, 'update']);
    Route::delete('/rfid-keys/{id}', [RFIDKeyController::class, 'destroy']);
    Route::post('/rfid-keys/{id}/assign', [RFIDKeyController::class, 'assign']);
    Route::post('/rfid-keys/{id}/release', [RFIDKeyController::class, 'release']);
});

//IDE MÉG JÖN EGY ELÉRHETŐSÉG ELLENŐRZÉS VÉGPONT

//FOGLALÁS VÉGPONTOK

Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth:sanctum');
Route::get('/bookings/user/{userId}', [BookingController::class, 'getBookingsByUserId'])->middleware('auth:sanctum');
Route::get('/bookings/hotel/{hotelId}', [BookingController::class, 'getBookingsByHotelId'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/bookings/delete/{id}', [BookingController::class, 'deleteBooking'])->middleware('auth:sanctum');
Route::put('/bookings/update-status/{id}', [BookingController::class, 'updateStatus']);//->middleware('auth:sanctum');

//INVOICE VÉGPONTOK
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/invoices/booking/{bookingId}/preview', [InvoiceController::class, 'generatePreview']);
    Route::get('/invoices/booking/{bookingId}', [InvoiceController::class, 'getByBooking']);
    Route::post('/invoices/{invoiceId}/approve', [InvoiceController::class, 'approve']);
    Route::post('/invoices/{invoiceId}/send', [InvoiceController::class, 'send']);
    Route::get('/invoices/{invoiceId}/download', [InvoiceController::class, 'download']);
});

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
Route::get('/search/locations', [SearchController::class, 'getLocations']);
Route::get('/search', [SearchController::class, 'searchWithPlans']);

//TAG VÉGPONTOK
Route::get('/tags', [ServiceTagController::class, 'index']);
Route::get('/tags/usage', [ServiceTagController::class, 'getUsage']);
Route::post('/tags', [ServiceTagController::class, 'store'])->middleware('auth:sanctum', 'role:hotel');
Route::put('/tags/{id}', [ServiceTagController::class, 'update'])->middleware('auth:sanctum', 'role:hotel');
Route::delete('/tags/{id}', [ServiceTagController::class, 'destroy'])->middleware('auth:sanctum', 'role:hotel');

//TAG-ek hozzáfűzése

Route::middleware('auth:sanctum')->group(function () {
    // HOTEL TAGS
    Route::post('/hotels/{hotel}/tags', [HotelTagController::class, 'store']);
    Route::delete('/hotels/{hotel}/tags/{tag}', [HotelTagController::class, 'destroy']);

    // ROOM TAGS
    Route::post('/rooms/{room}/tags', [RoomTagController::class, 'store']);
    Route::delete('/rooms/{room}/tags/{tag}', [RoomTagController::class, 'destroy']);
});
