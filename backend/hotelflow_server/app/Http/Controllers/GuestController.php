<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmationMail;
use App\Models\Guest;

class GuestController extends Controller
{
    public function updateGuest(Request $request, $id)
{
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'idNumber' => 'sometimes|string|max:255',
        'dateOfBirth' => 'sometimes|date|max:20',
    ]);

    // Vendég lekérése
    $guest = Guest::find($id);
    if (!$guest) {
        return response()->json(['message' => 'Guest not found'], 404);
    }

    // Foglalás lekérése a vendég alapján (with hotel relationship)
    $booking = Booking::with('hotel')->find($guest->bookings_id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    // Allow if user is the booking owner OR hotel admin for this booking
    $user = auth()->user();
    $isBookingOwner = $booking->users_id === $user->id;
    $isHotelAdmin = $user->role === 'hotel' && $booking->hotel->user_id === $user->id;
    
    if (!$isBookingOwner && !$isHotelAdmin) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Vendég módosítása
    if ($request->has('name')) {
        $guest->name = $request->name;
    }
    if ($request->has('idNumber')) {
        $guest->idNumber = $request->idNumber;
    }
    if ($request->has('dateOfBirth')) {
        $guest->dateOfBirth = $request->dateOfBirth;
    }
    $guest->save();

    return response()->json([
        'message' => 'Guest updated successfully',
        'guest' => $guest
    ], 200);
}
public function deleteGuest($id)
{
    // Vendég lekérése
    $guest = Guest::find($id);
    if (!$guest) {
        return response()->json(['message' => 'Guest not found'], 404);
    }

    // Foglalás lekérése a vendég alapján (with hotel relationship)
    $booking = Booking::with('hotel')->find($guest->bookings_id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    // Allow if user is the booking owner OR hotel admin for this booking
    $user = auth()->user();
    $isBookingOwner = $booking->users_id === $user->id;
    $isHotelAdmin = $user->role === 'hotel' && $booking->hotel->user_id === $user->id;
    
    if (!$isBookingOwner && !$isHotelAdmin) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Vendég törlése
    $guest->delete();

    return response()->json([
        'message' => 'Guest deleted successfully'
    ], 200);
}

}
