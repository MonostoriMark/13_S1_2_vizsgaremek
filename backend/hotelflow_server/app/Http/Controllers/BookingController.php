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
    if($request->userId !== auth()->id()){
        return response()->json(['error' => 'Nincs jogosultságod'], 403);
    }
    if (count($request->rooms) === 0) {
        return response()->json(['error' => 'Legalább egy szobát ki kell választani'], 400);
    }
    if ($request->has('services') && count($request->services) === 0) {
        return response()->json(['error' => 'Ha szolgáltatásokat adsz meg, legalább egyet ki kell választani'], 400);
    }
    if (strtotime($request->endDate) < strtotime($request->startDate)) {
        return response()->json(['error' => 'A távozási dátumnak későbbinek kell lennie, mint az érkezési dátum'], 400);
    }
    if(strtotime($request->startDate) < strtotime(date('Y-m-d'))){
        return response()->json(['error' => 'Az érkezési dátum nem lehet múltbeli'], 400);
    }
    
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
public function deleteBooking($id)
{
    $booking = Booking::find($id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking->delete();

    return response()->json(['message' => 'Booking deleted successfully'], 200);

}
public function getBookingsByUserId($userId)
{
    // 🔥 Jogosultság ellenőrzés
    if ($userId != auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $bookings = Booking::where('users_id', $userId)
        ->with(['rooms', 'guests', 'services'])
        ->get();

    return response()->json(['bookings' => $bookings], 200);
}
public function getGuestsByBookingId($bookingId)
{
    $booking = Booking::find($bookingId);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $guests = Guest::where('bookings_id', $bookingId)->get();
    return response()->json(['guests' => $guests], 200);
}
}