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
    // Foglalás létrehozása
    public function store(Request $request)
{
    $request->validate([
        'userId' => 'required|exists:users,id',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'rooms' => 'required|array|min:1',
        'rooms.*.id' => 'required|exists:rooms,id',
        'rooms.*.guests' => 'required|integer|min:1',
        'services' => 'array',
        'services.*' => 'exists:services,id'
    ]);

    DB::beginTransaction();
    try {
        // -------------------------
        // Foglalás létrehozása ideiglenes ár nélkül
        // -------------------------
        $booking = Booking::create([
            'users_id' => $request->userId,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'checkInToken' => str()->random(),
            'status' => 'pending',
            'totalPrice' => 0,
        ]);

        $totalPrice = 0;

        // -------------------------
        // Szobák hozzáadása + ár számítása
        // -------------------------
        $roomIds = [];
        foreach ($request->rooms as $roomData) {
            $room = Room::find($roomData['id']);
            $roomIds[] = $room->id;

            $guestsCount = $roomData['guests'];
            $roomPrice = $room->basePrice + ($room->pricePerNight * $guestsCount);
            $totalPrice += $roomPrice;
        }
        $booking->rooms()->sync($roomIds);

        // -------------------------
        // Szolgáltatások hozzáadása + ár számítása
        // -------------------------
        if ($request->has('services')) {
            $booking->services()->sync($request->services);
            $servicesPrice = \App\Models\Service::whereIn('id', $request->services)->sum('price');
            $totalPrice += $servicesPrice;
        }

        // -------------------------
        // Végső ár mentése
        // -------------------------
        $booking->totalPrice = $totalPrice;
        $booking->save();

        DB::commit();

        // -------------------------
        // Mail küldés
        // -------------------------
        try {
            Mail::to($booking->user->email)
                ->send(new BookingConfirmationMail($booking));
        } catch (\Exception $mailEx) {
            \Log::error('Mail küldési hiba: ' . $mailEx->getMessage());
        }

        return response()->json(['bookingId' => $booking->id, 'totalPrice' => $totalPrice], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Hiba a foglalás létrehozásakor',
            'message' => $e->getMessage()
        ], 500);
    }
}
    public function addGuests(Request $request, $bookingId)
{
    $request->validate([
        'guests' => 'required|array|min:1',
        'guests.*.name' => 'required|string|max:255',
        'guests.*.idNumber' => 'required|string|max:255',
        'guests.*.dateOfBirth' => 'required|date|max:20',
    ]);

    $booking = Booking::find($bookingId);
    if (!$booking) {
        return response()->json(['error' => 'Foglalás nem található'], 404);
    }

    // -------------------------
    // Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
    // -------------------------
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['error' => 'Nincs jogosultságod'], 403);
    }

    $guestData = [];
    foreach ($request->guests as $guest) {
        $guestData[] = [
            'bookings_id' => $bookingId, // figyelj, a tábla a bookings_id mezőt használja
            'name' => $guest['name'],
            'idNumber' => $guest['idNumber'], // Ha nincs külön ID, ide mehet pl az email, vagy új mezőt csinálni
            'dateOfBirth' => $guest['dateOfBirth'], // Ha szükséges, később a valós dátum

        ];
    }

    DB::table('guests')->insert($guestData);

    return response()->json(['message' => 'Vendégek sikeresen hozzáadva'], 201);
}


}
