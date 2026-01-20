<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmationMail;
use App\Mail\NewBookingNotificationMail;
use App\Mail\BookingRequestNotificationMail;
use App\Mail\BookingConfirmedPendingPaymentMail;
use App\Models\Invoice;
use App\Models\Guest;
use App\Models\RFIDKey;
use App\Models\RFIDConnection;
use App\Models\RFIDAssignment;
use App\Models\Hotel;
use App\Models\BookingPayment;
use App\Models\BookingInvoiceDetail;

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
        'services.*' => 'exists:services,id',

        // Payment + invoice details (captured before sending booking request)
        'payment_method' => 'sometimes|in:bank_transfer',
        'invoice_details' => 'sometimes|array',
        'invoice_details.customer_type' => 'sometimes|in:private,business',
        'invoice_details.full_name' => 'sometimes|string|max:255',
        'invoice_details.email' => 'sometimes|email|max:255',
        'invoice_details.company_name' => 'nullable|string|max:255',
        'invoice_details.tax_number' => 'nullable|string|max:255',
        'invoice_details.country' => 'nullable|string|max:255',
        'invoice_details.city' => 'nullable|string|max:255',
        'invoice_details.postal_code' => 'nullable|string|max:50',
        'invoice_details.address_line' => 'nullable|string|max:255',
        'invoice_details.note' => 'nullable|string|max:2000',
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

        // OPTIMIZATION: Batch load all rooms at once to avoid N+1 queries
        $requestedRoomIds = collect($request->rooms)->pluck('id')->toArray();
        $rooms = Room::whereIn('id', $requestedRoomIds)
            ->where('hotels_id', $request->hotelId)
            ->get()
            ->keyBy('id');

        // Validate all rooms exist and belong to the hotel
        if ($rooms->count() !== count($requestedRoomIds)) {
            DB::rollBack();
            $missingIds = array_diff($requestedRoomIds, $rooms->pluck('id')->toArray());
            return response()->json([
                'error' => 'Egy vagy több szoba nem található vagy nem tartozik a kiválasztott hotelhez',
                'missing_room_ids' => array_values($missingIds)
            ], 400);
        }

        // OPTIMIZATION: Batch check room availability for all rooms at once
        $nights = \Carbon\Carbon::parse($request->startDate)->diffInDays($request->endDate);
        $overlappingRooms = DB::table('bookingsRelation')
            ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
            ->whereIn('bookingsRelation.rooms_id', $requestedRoomIds)
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->where('bookings.startDate', '<', $request->endDate)
            ->where('bookings.endDate', '>', $request->startDate)
            ->distinct()
            ->pluck('bookingsRelation.rooms_id')
            ->toArray();

        if (!empty($overlappingRooms)) {
            DB::rollBack();
            $unavailableRoomNames = $rooms->whereIn('id', $overlappingRooms)->pluck('name')->toArray();
            return response()->json([
                'error' => 'Egy vagy több szoba nem elérhető a megadott időszakban',
                'unavailable_rooms' => $unavailableRoomNames
            ], 400);
        }

        // Calculate total price and prepare room IDs
        $totalPrice = 0;
        $roomIds = [];

        foreach ($request->rooms as $roomData) {
            $room = $rooms->get($roomData['id']);
            
            if (!$room) {
                DB::rollBack();
                return response()->json(['error' => "Szoba nem található: ID {$roomData['id']}"], 400);
            }

            $roomIds[] = $room->id;

            // Calculate room price: pricePerNight * nights
            // basePrice is added once per room if it exists
            $roomPrice = ($room->basePrice ?? 0) + ($room->pricePerNight * $nights);
            $totalPrice += $roomPrice;
        }

        $booking->rooms()->sync($roomIds);

        // -------------------------
        // Store payment method (pending) + invoice details snapshot
        // -------------------------
        $paymentMethod = $request->input('payment_method', 'bank_transfer');
        BookingPayment::firstOrCreate(
            ['booking_id' => $booking->id],
            ['method' => $paymentMethod, 'status' => 'pending']
        );

        $details = $request->input('invoice_details');
        if (is_array($details)) {
            BookingInvoiceDetail::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'customer_type' => $details['customer_type'] ?? 'private',
                    'full_name' => $details['full_name'] ?? ($booking->user->name ?? 'N/A'),
                    'email' => $details['email'] ?? ($booking->user->email ?? 'N/A'),
                    'company_name' => $details['company_name'] ?? null,
                    'tax_number' => $details['tax_number'] ?? null,
                    'country' => $details['country'] ?? null,
                    'city' => $details['city'] ?? null,
                    'postal_code' => $details['postal_code'] ?? null,
                    'address_line' => $details['address_line'] ?? null,
                    'note' => $details['note'] ?? null,
                ]
            );
        }

        // -------------------------
        // RFID kulcsok lefoglalása a foglalás időtartamára (date-range aware)
        // -------------------------
        //
        // IMPORTANT:
        // - We reserve keys for the booking date range (reserved_from/reserved_to)
        // - Availability is determined by overlap with other non-released assignments
        // - This supports far-future bookings (e.g. January holiday season)

        $requestedStart = \Carbon\Carbon::parse($request->startDate)->toDateString();
        $requestedEnd = \Carbon\Carbon::parse($request->endDate)->toDateString();

        $availableRfidKeys = RFIDKey::where('hotels_id', $request->hotelId)
            ->whereDoesntHave('assignments', function ($q) use ($requestedStart, $requestedEnd) {
                $q->whereNull('released_at')
                    ->whereNotNull('reserved_from')
                    ->whereNotNull('reserved_to')
                    ->where('reserved_from', '<', $requestedEnd)
                    ->where('reserved_to', '>', $requestedStart);
            })
            ->take(count($roomIds))
            ->get();

        if ($availableRfidKeys->count() < count($roomIds)) {
            DB::rollBack();
            return response()->json([
                'error' => "Nincs elég elérhető RFID kulcs a hotelhez a megadott időszakban. Szükséges: " . count($roomIds) . ", Elérhető: {$availableRfidKeys->count()}."
            ], 400);
        }

        foreach ($roomIds as $index => $roomId) {
            $rfidKey = $availableRfidKeys->get($index);

            RFIDAssignment::create([
                'rfid_key_id' => $rfidKey->id,
                'booking_id' => $booking->id,
                'room_id' => $roomId,
                'reserved_from' => $requestedStart,
                'reserved_to' => $requestedEnd,
                'assigned_at' => now(),
                'released_at' => null,
            ]);
        }


        // -------------------------
        // Szolgáltatások hozzáadása + ár számítása
        // -------------------------
        if ($request->has('services') && !empty($request->services)) {
            // OPTIMIZATION: Validate services belong to the hotel before syncing
            $validServices = Service::whereIn('id', $request->services)
                ->where('hotels_id', $request->hotelId)
                ->pluck('id')
                ->toArray();

            if (count($validServices) !== count($request->services)) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Egy vagy több szolgáltatás nem tartozik a kiválasztott hotelhez'
                ], 400);
            }

            $booking->services()->sync($validServices);
            $servicesPrice = Service::whereIn('id', $validServices)->sum('price');
            $totalPrice += $servicesPrice;
        }

        // -------------------------
        // Végső ár mentése
        // -------------------------
        $booking->totalPrice = $totalPrice;
        $booking->save();

        DB::commit();

        // -------------------------
        // Mail küldés - Vendég értesítés (foglalási kérés elküldve)
        // -------------------------
        try {
            // Reload booking with relationships for email
            $booking->load(['hotel.user', 'user', 'rooms', 'services']);
            
            // Send initial notification (without QR code)
            Mail::to($booking->user->email)
                ->send(new BookingRequestNotificationMail($booking));
        } catch (\Exception $mailEx) {
            \Log::error('Mail küldési hiba (vendég értesítés): ' . $mailEx->getMessage());
        }

        // -------------------------
        // Értesítés küldése a szálloda adminisztrátorának
        // -------------------------
        try {
            // Reload booking with hotel and user relationships
            $booking->load(['hotel.user', 'user']);
            
            if ($booking->hotel && $booking->hotel->user) {
                // Get frontend URL from config
                $frontendUrl = config('app.frontend_url');
                $bookingsUrl = $frontendUrl . '/admin/bookings';
                
                // Send notification email to hotel admin
                Mail::to($booking->hotel->user->email)
                    ->send(new NewBookingNotificationMail($booking, $bookingsUrl));
            }
        } catch (\Exception $notificationEx) {
            \Log::error('Értesítés küldési hiba (szálloda admin): ' . $notificationEx->getMessage());
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
    // Allow adding guests even after booking is confirmed
    // Only block if booking is cancelled or completed
    // -------------------------
    if ($booking->status === 'cancelled' || $booking->status === 'finished') {
        return response()->json(['error' => 'Nem lehet vendéget hozzáadni törölt vagy befejezett foglaláshoz'], 400);
    }

    // -------------------------
    // Jogosultság ellenőrzés
    // Allow if user is the booking owner OR hotel admin for this booking
    // -------------------------
    $user = auth()->user();
    $booking->load('hotel');
    $isBookingOwner = $booking->users_id === $user->id;
    $isHotelAdmin = $user->role === 'hotel' && $booking->hotel->user_id === $user->id;
    
    if (!$isBookingOwner && !$isHotelAdmin) {
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
        if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // 🔥 Jogosultság ellenőrzés
    if ($booking->users_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    // Release reserved RFID keys (new reservation-based system)
    $assignments = RFIDAssignment::where('booking_id', $booking->id)
        ->whereNull('released_at')
        ->get();

    foreach ($assignments as $assignment) {
        $assignment->released_at = now();
        $assignment->save();
    }

    // Backward-compat: release legacy room<->key connections if any exist
    foreach ($rooms as $room) {
        $rfidConnection = RFIDConnection::where('rooms_id', $room->id)->first();
        if ($rfidConnection) {
            $rfidKey = RFIDKey::where('rfidKey', $rfidConnection->rfidKeys_id)->first();
            if ($rfidKey) {
                $rfidKey->isUsed = false; // legacy cleanup only
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

    // -------------------------
    // Booking confirmation email (PAYMENT GATED)
    // - When hotel confirms a booking, DO NOT send check-in QR yet
    // - Create/ensure payment record exists (pending)
    // - Notify guest: invoice will be sent shortly, QR comes after payment confirmation
    // -------------------------
    if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
        try {
            // Reload booking with all relationships for email
            $booking->load(['hotel', 'user', 'rooms.hotel', 'services']);
            
            // Ensure payment status exists and is pending
            BookingPayment::firstOrCreate(
                ['booking_id' => $booking->id],
                ['method' => 'bank_transfer', 'status' => 'pending']
            );

            // Notify guest (no QR yet)
            Mail::to($booking->user->email)
                ->send(new BookingConfirmedPendingPaymentMail($booking));

            // -------------------------
            // Generate invoice preview (draft)
            // -------------------------
            try {
                $invoice = Invoice::where('booking_id', $booking->id)->first();
                
                if (!$invoice) {
                    // Create draft invoice
                    $subtotal = $booking->totalPrice;
                    $taxRate = 27;
                    $taxAmount = round($subtotal * ($taxRate / 100), 2);
                    $totalAmount = $subtotal + $taxAmount;
                    $invoiceNumber = 'SZ' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . '/' . date('Y');
                    
                    $invoice = Invoice::create([
                        'booking_id' => $booking->id,
                        'invoice_number' => $invoiceNumber,
                        'status' => 'draft',
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'tax_rate' => $taxRate,
                        'issue_date' => now()->toDateString(),
                        'due_date' => now()->addDays(8)->toDateString(),
                    ]);
                }
            } catch (\Exception $invoiceEx) {
                \Log::error('Számla előnézet generálási hiba: ' . $invoiceEx->getMessage());
            }
        } catch (\Exception $mailEx) {
            \Log::error('QR kód email küldési hiba: ' . $mailEx->getMessage());
        }
    }

    // Automatically release RFID keys when booking is completed or cancelled
    if (in_array($request->status, ['completed', 'cancelled'])) {
        // Reservation-based system: mark all assignments as released
        $assignments = RFIDAssignment::where('booking_id', $booking->id)
            ->whereNull('released_at')
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->released_at = now();
            $assignment->save();
        }

        // Backward-compat: release keys from legacy RFIDConnection system if any exist
        $booking->load('rooms');
        foreach ($booking->rooms as $room) {
            $rfidConnection = RFIDConnection::where('rooms_id', $room->id)->first();
            if ($rfidConnection) {
                $rfidKey = RFIDKey::where('rfidKey', $rfidConnection->rfidKeys_id)->first();
                if ($rfidKey) {
                    $rfidKey->isUsed = false; // legacy cleanup only
                    $rfidKey->save();
                }
                $rfidConnection->delete();
            }
        }
    }

    return response()->json(['message' => 'Booking status updated successfully'], 200);
}

    /**
     * Update booking details (hotel admin only)
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::with(['hotel', 'rooms', 'services'])->find($id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Check if user is hotel admin for this booking
        if ($booking->hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after_or_equal:startDate',
            'totalPrice' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'rooms' => 'sometimes|array',
            'rooms.*' => 'exists:rooms,id',
            'services' => 'sometimes|array',
            'services.*' => 'exists:services,id',
        ]);

        DB::beginTransaction();
        try {
            // Update dates
            if ($request->has('startDate')) {
                $booking->startDate = $request->startDate;
            }
            if ($request->has('endDate')) {
                $booking->endDate = $request->endDate;
            }

            // Update status if provided
            if ($request->has('status')) {
                $booking->status = $request->status;
            }

            // Update rooms if provided
            if ($request->has('rooms')) {
                // Validate all rooms belong to the hotel
                $roomIds = $request->rooms;
                $validRooms = Room::whereIn('id', $roomIds)
                    ->where('hotels_id', $booking->hotels_id)
                    ->pluck('id')
                    ->toArray();

                if (count($validRooms) !== count($roomIds)) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'One or more rooms do not belong to this hotel'
                    ], 400);
                }

                $booking->rooms()->sync($validRooms);
            }

            // Update services if provided
            if ($request->has('services')) {
                // Validate all services belong to the hotel
                $serviceIds = $request->services;
                $validServices = Service::whereIn('id', $serviceIds)
                    ->where('hotels_id', $booking->hotels_id)
                    ->pluck('id')
                    ->toArray();

                if (count($validServices) !== count($serviceIds)) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'One or more services do not belong to this hotel'
                    ], 400);
                }

                $booking->services()->sync($validServices);
            }

            // Update total price (manual override)
            if ($request->has('totalPrice')) {
                $booking->totalPrice = $request->totalPrice;
                
                // Update invoice if exists and is draft
                $invoice = Invoice::where('booking_id', $booking->id)->first();
                if ($invoice && $invoice->status === 'draft') {
                    $invoice->subtotal = $request->totalPrice;
                    $invoice->tax_amount = round($invoice->subtotal * ($invoice->tax_rate / 100), 2);
                    $invoice->total_amount = $invoice->subtotal + $invoice->tax_amount;
                    $invoice->save();
                }
            }

            $booking->save();
            DB::commit();

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking' => $booking->fresh(['user', 'rooms', 'services', 'guests', 'payment'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update booking',
                'message' => $e->getMessage()
            ], 500);
        }
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
        ->with(['user', 'rooms', 'guests', 'services', 'payment'])
        ->orderBy('createdAt', 'desc')
        ->get();

    return response()->json(['bookings' => $bookings], 200);
}

public function confirmPayment($id)
{
    $user = auth()->user();
    if (!$user || $user->role !== 'hotel') {
        return response()->json(['message' => 'Unauthorized - Hotel admin access required'], 403);
    }

    $booking = Booking::with(['hotel', 'user', 'rooms.hotel', 'services', 'payment'])->find($id);
    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // Verify booking belongs to this hotel admin
    $hotel = Hotel::where('id', $booking->hotels_id)->where('user_id', $user->id)->first();
    if (!$hotel) {
        return response()->json(['message' => 'Hotel not found or unauthorized'], 404);
    }

    if ($booking->status !== 'confirmed') {
        return response()->json(['error' => 'Payment can only be confirmed for confirmed bookings'], 400);
    }

    $payment = BookingPayment::firstOrCreate(
        ['booking_id' => $booking->id],
        ['method' => 'bank_transfer', 'status' => 'pending']
    );

    if ($payment->status === 'paid') {
        return response()->json(['message' => 'Payment already confirmed'], 200);
    }

    $payment->status = 'paid';
    $payment->confirmed_at = now();
    $payment->confirmed_by_user_id = $user->id;
    $payment->save();

    // Now send the QR code email used for check-in
    try {
        Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking));
    } catch (\Exception $mailEx) {
        \Log::error('QR kód email küldési hiba (payment confirmed): ' . $mailEx->getMessage());
        return response()->json(['error' => 'Payment confirmed, but failed to send QR email'], 500);
    }

    return response()->json([
        'message' => 'Payment confirmed and QR code sent to guest',
        'payment' => $payment
    ], 200);
}
}