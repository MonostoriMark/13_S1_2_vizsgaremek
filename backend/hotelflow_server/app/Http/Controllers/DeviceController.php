<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\BookingRoomRelation;

class DeviceController extends Controller
{
public function getBookings($hotelId)
{
    // Foglalások a kapcsolódó szobákkal
    $bookings = Booking::with('rooms:id,name') 
        ->where('hotels_id', $hotelId)
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
        'relations' => $relations
    ], 200);
}



        
}

