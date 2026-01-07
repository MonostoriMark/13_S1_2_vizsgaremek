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
use App\Models\RFIDKey;
use App\Models\RFIDConnection;
use App\Models\RFIDAssignment;

class BookingController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'userId' => 'required|exists:users,id',
        'hotelId' => 'required|exists:hotels,id',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'rooms' => 'required|array|min:1',
        'rooms.*.id' => 'required|exists:rooms,id',
        'rooms.*.guests' => 'required|integer|min:1',
        'services' => 'array',
        'services.*' => 'exists:services,id'
    ]);

    if ($request->userId !== auth()->id()) {
        return response()->json(['error' => 'Nincs jogosultságod'], 403);
    }

    if (count($request->rooms) === 0) {
        return response()->json(['error' => 'Legalább egy szobát ki kell választani'], 400);
    }

    // Only validate services if they are provided and not empty
    if ($request->has('services') && is_array($request->services) && count($request->services) === 0) {
        return response()->json(['error' => 'Ha szolgáltatásokat adsz meg, legalább egyet ki kell választani'], 400);
    }

    if (strtotime($request->endDate) < strtotime($request->startDate)) {
        return response()->json(['error' => 'A távozási dátumnak későbbinek kell lennie, mint az érkezési dátum'], 400);
    }

    if (strtotime($request->startDate) < strtotime(date('Y-m-d'))) {
        return response()->json(['error' => 'Az érkezési dátum nem lehet múltbeli'], 400);
    }

    DB::beginTransaction();
    try {
        // -------------------------
        // Foglalás létrehozása ideiglenes ár nélkül
        // -------------------------
        $booking = Booking::create([
            'users_id' => $request->userId,
            'hotels_id' => $request->hotelId,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'checkInToken' => str()->random(),
            'status' => 'pending',
            'totalPrice' => 0,
        ]);

        $totalPrice = 0;
        $roomIds = [];

        // -------------------------
        // Szobák hozzáadása + ár számítása + ellenőrzések
        // -------------------------
        foreach ($request->rooms as $roomData) {
            $room = Room::find($roomData['id']);

            // 1. Ellenőrzés: szoba a hotelhez tartozik?
            if ($room->hotels_id != $request->hotelId) {
                DB::rollBack();
                return response()->json(['error' => "A(z) {$room->name} szoba nem tartozik a kiválasztott hotelhez"], 400);
            }

            // 2. Ellenőrzés: szoba szabad-e a megadott időszakban?
            $overlappingBooking = $room->bookings()
                ->where('status', 'confirmed')
                ->where(function($query) use ($request) {
                    $query->whereBetween('startDate', [$request->startDate, $request->endDate])
                          ->orWhereBetween('endDate', [$request->startDate, $request->endDate])
                          ->orWhere(function($q) use ($request) {
                              $q->where('startDate', '<=', $request->startDate)
                                ->where('endDate', '>=', $request->endDate);
                          });
                })
                ->exists();

            if ($overlappingBooking) {
                DB::rollBack();
                return response()->json(['error' => "A(z) {$room->name} szoba nem elérhető a megadott időszakban"], 400);
            }

            $roomIds[] = $room->id;

            $guestsCount = $roomData['guests'];
            $roomPrice = $room->basePrice + ($room->pricePerNight * $guestsCount);
            $totalPrice += $roomPrice;
        }

        $booking->rooms()->sync($roomIds);

        // -------------------------
        // RFID kulcsok hozzárendelése

        // --------- IDE JÖN AZ ÚJ RFID KÓD ---------
        foreach ($roomIds as $roomId) {
            // Check for available RFID key
            // Try both boolean false and integer 0 to handle database type differences
            $rfidKey = RFIDKey::where('hotels_id', $request->hotelId)
                            ->where(function($query) {
                                $query->where('isUsed', false)
                                      ->orWhere('isUsed', 0);
                            })
                            ->first();

            if (!$rfidKey) {
                DB::rollBack();
                return response()->json([
                    'error' => "Nincs elérhető RFID kulcs a hotelhez. Kérjük, vegye fel a kapcsolatot a szállodával."
                ], 400);
            }

            RFIDConnection::create([
                'rfidKeys_id' => $rfidKey->rfidKey,
                'rooms_id' => $roomId
            ]);

            $rfidKey->isUsed = true;
            $rfidKey->save();
        }


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
    $rooms = $booking->rooms;
        // RFID kulcsok felszabadítása
   
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
     foreach ($rooms as $room) {
        $rfidConnection = RFIDConnection::where('rooms_id', $room->id)->first();
        if ($rfidConnection) {
            $rfidKey = RFIDKey::where('rfidKey', $rfidConnection->rfidKeys_id)->first();
            if ($rfidKey) {
                $rfidKey->isUsed = false; // Use false instead of 0 for consistency
                $rfidKey->save();
            }
            $rfidConnection->delete();
        }
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
function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,confirmed,cancelled,completed'
    ]);

    $booking = Booking::find($id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés 
    /*
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
*/
    $oldStatus = $booking->status;
    $booking->status = $request->status;
    $booking->touch();
    $booking->save();

    // Automatically release RFID keys when booking is completed or cancelled
    if (in_array($request->status, ['completed', 'cancelled'])) {
        $assignments = RFIDAssignment::where('booking_id', $booking->id)
            ->whereNull('released_at')
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->released_at = now();
            $assignment->save();

            $rfidKey = RFIDKey::find($assignment->rfid_key_id);
            if ($rfidKey) {
                $rfidKey->isUsed = false;
                $rfidKey->save();
            }
        }
    }

    return response()->json(['message' => 'Booking status updated successfully'], 200);
}

public function getBookingsByHotelId($hotelId)
{
    // Get the authenticated user
    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Check if user is a hotel admin
    if ($user->role !== 'hotel') {
        return response()->json(['message' => 'Unauthorized - Hotel admin access required'], 403);
    }

    // Verify the hotel belongs to the authenticated user
    $hotel = \App\Models\Hotel::where('id', $hotelId)
        ->where('user_id', $user->id)
        ->first();

    if (!$hotel) {
        return response()->json(['message' => 'Hotel not found or unauthorized'], 404);
    }

    // Get all bookings for this hotel (including pending, confirmed, cancelled, finished)
    $bookings = Booking::where('hotels_id', $hotelId)
        ->with(['user', 'rooms', 'guests', 'services'])
        ->orderBy('createdAt', 'desc')
        ->get();

    return response()->json(['bookings' => $bookings], 200);
}
}