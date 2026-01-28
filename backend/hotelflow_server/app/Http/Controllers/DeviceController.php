<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\BookingRoomRelation;
use App\Models\RFIDKey;
use App\Models\RFIDConnection;
use App\Models\Device;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    /**
     * Authenticate device from request token
     * Returns device object or null, and optionally validates hotel match
     * 
     * Usage examples:
     * 
     * // Basic authentication (no hotel validation)
     * $auth = $this->authenticateDevice($request);
     * if ($auth['error']) return $auth['error'];
     * $device = $auth['device'];
     * 
     * // With hotel ID validation
     * $auth = $this->authenticateDevice($request, $hotelId);
     * if ($auth['error']) return $auth['error'];
     * $device = $auth['device'];
     * 
     * @param Request $request
     * @param int|null $requiredHotelId Optional hotel ID to validate against
     * @return array ['device' => Device|null, 'error' => Response|null]
     */
    protected function authenticateDevice(Request $request, $requiredHotelId = null)
    {
        // Get token from header or query parameter
        $token = null;
        
        // Try Authorization header first (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
        
        // Fallback to query parameter
        if (!$token) {
            $token = $request->query('token');
        }
        
        if (!$token) {
            return [
                'device' => null,
                'error' => response()->json(['message' => 'Device token is required'], 401)
            ];
        }
        
        // Find device by token
        $device = Device::where('token', $token)->first();
        
        if (!$device) {
            return [
                'device' => null,
                'error' => response()->json(['message' => 'Invalid device token'], 401)
            ];
        }
        
        // Check if device is active
        if (!$device->is_active) {
            return [
                'device' => null,
                'error' => response()->json(['message' => 'Device is not active. Please contact administrator.'], 403)
            ];
        }
        
        // Verify hotel match if required
        if ($requiredHotelId !== null && $device->hotels_id != $requiredHotelId) {
            return [
                'device' => null,
                'error' => response()->json(['message' => 'Device token does not match the requested hotel'], 403)
            ];
        }
        
        return [
            'device' => $device,
            'error' => null
        ];
    }

    /**
     * Get bookings for a hotel - secured with device token
     * Token can be sent via:
     * 1. Authorization header: Bearer {token}
     * 2. Query parameter: ?token={token}
     */
    public function getBookings(Request $request, $hotelId)
    {
        // Authenticate device and validate hotel match
        $auth = $this->authenticateDevice($request, $hotelId);
        if ($auth['error']) {
            return $auth['error'];
        }
        
        // Proceed with original logic
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

        // Get RFID connections - which RFID key is linked to which room
        $rfidConnections = RFIDConnection::join('rfidKeys', 'rfidKeyConnection.rfidKeys_id', '=', 'rfidKeys.rfidKey')
            ->join('rooms', 'rfidKeyConnection.rooms_id', '=', 'rooms.id')
            ->where('rooms.hotels_id', $hotelId)
            ->select(
                'rfidKeys.rfidKey as key',  // The RFID key code
                'rooms.id as roomId',        // Room ID
                'rooms.name as roomName'     // Room name
            )
            ->get();

        return response()->json([
            'bookings' => $bookings,
            'rooms' => $rooms,
            'relations' => $relations,
            'rfidKeys' => RFIDKey::where('hotels_id', $hotelId)
                ->select('id', 'hotels_id', 'isUsed', 'rfidKey')
                ->get(),
            'rfidConnections' => $rfidConnections
        ], 200);
    }
    /**
     * Update booking data - secured with device token
     * Token can be sent via:
     * 1. Authorization header: Bearer {token}
     * 2. Query parameter: ?token={token}
     */
    public function updateData(Request $request, $bookingId)
    {
        // Authenticate device (hotel validation will be done after loading booking)
        $auth = $this->authenticateDevice($request);
        if ($auth['error']) {
            return $auth['error'];
        }
        
        $device = $auth['device'];
        
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        
        // Verify that the booking's hotel matches the device's hotel
        if ($booking->hotels_id != $device->hotels_id) {
            return response()->json(['message' => 'Device token does not match the booking\'s hotel'], 403);
        }

    // Csak a megengedett mezőket frissítjük
    $allowedFields = ['checkInstatus', 'checkInTime','status', 'checkOutTime'];
    foreach ($allowedFields as $field) {
        if ($request->has($field)) {
            $booking->$field = $request->input($field);
        }
    }

    // Automatikusan állítsuk be a státuszt "finished"-re, ha a checkInstatus "checkedOut"
    if ($request->has('checkInstatus') && $request->input('checkInstatus') === 'checkedOut') {
        $booking->status = 'finished';
    }

    $booking->save();

    return response()->json(['message' => 'Booking updated successfully', 'booking' => $booking], 200);



        

}
}
