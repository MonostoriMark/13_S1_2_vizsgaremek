<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\BookingRoomRelation;
use App\Models\RFIDKey;

class DeviceController extends Controller
{
public function getBookings($hotelId)
{
    // Foglalások a kapcsolódó szobákkal
    $bookings = Booking::where('hotels_id', $hotelId)
        ->where('status', 'confirmed')
        ->select('id','users_id','startDate','endDate','checkInToken','checkInstatus','checkInTime','checkOutTime','status')
        ->get();

    if ($bookings->isEmpty()) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // Hotel összes szobája
    $rooms = Room::where('hotels_id', $hotelId)
        ->select('id','name')
        ->get();

    // Pivot relációk lekérése
    $relations = \DB::table('bookingsRelation')
        ->whereIn('booking_id', $bookings->pluck('id'))
        ->get();

    return response()->json([
        'bookings' => $bookings,
        'rooms' => $rooms,
        'relations' => $relations,
        'rfidKeys' => RFIDKey::where('hotels_id', $hotelId)->get()
        
    ], 200);
}
public function updateData(Request $request, $bookingId)
{
    $booking = Booking::find($bookingId);

    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // Csak a megengedett mezőket frissítjük
    $allowedFields = ['checkInstatus', 'checkInTime', 'checkOutTime'];
    foreach ($allowedFields as $field) {
        if ($request->has($field)) {
            $booking->$field = $request->input($field);
        }
    }

    $booking->save();

    return response()->json(['message' => 'Booking updated successfully', 'booking' => $booking], 200);



        

}

}

