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

class BookingController extends Controller
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

    // Foglalás lekérése a vendég alapján
    $booking = Booking::find($guest->bookings_id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Vendég módosítása
    $guest->name = $request->name;
    $guest->idNumber = $request->idNumber;
    $guest->dateOfBirth = $request->dateOfBirth;
    $guest->save();

    return response()->json([
        'message' => 'Guest updated successfully',
        'guest' => $guest
    ], 200);
}


}
