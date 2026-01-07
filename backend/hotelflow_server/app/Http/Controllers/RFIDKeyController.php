<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RFIDKey;
use App\Models\RFIDAssignment;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RFIDKeyController extends Controller
{
    /**
     * Get all RFID keys for the authenticated hotel admin
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            
            // Get hotel belonging to the user
            $hotel = Hotel::where('user_id', $user->id)->first();
            
            if (!$hotel) {
                return response()->json(['message' => 'Hotel not found'], 404);
            }

        $query = RFIDKey::where('hotels_id', $hotel->id);

        // Filter by status (map to isUsed)
        if ($request->has('status')) {
            if ($request->status === 'available') {
                $query->where('isUsed', false);
            } elseif ($request->status === 'assigned') {
                $query->where('isUsed', true);
            }
        }

        // Search by UID
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('rfidKey', 'like', "%{$search}%");
        }

        $keys = $query->orderBy('id', 'desc')->get();

        // Format response - simplified to avoid hangs
        $formattedKeys = $keys->map(function($key) {
            // Map isUsed to status
            $status = $key->isUsed ? 'assigned' : 'available';
            
            // Don't try to load assignments for now - this can cause hangs
            // We'll add assignment info later if needed
            $bookingInfo = null;
            
            return [
                'id' => $key->id,
                'uid' => $key->rfidKey,
                'label' => null, // Label column doesn't exist
                'status' => $status,
                'isUsed' => $key->isUsed,
                'hotel_id' => $key->hotels_id,
                'current_booking' => $bookingInfo,
                'created_at' => null, // No timestamps
                'updated_at' => null
            ];
        });

            return response()->json(['keys' => $formattedKeys], 200);
        } catch (\Exception $e) {
            \Log::error('RFID Key index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error loading RFID keys',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific RFID key
     */
    public function show($id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $key = RFIDKey::where('id', $id)
            ->where('hotels_id', $hotel->id)
            ->with(['activeAssignment.booking', 'activeAssignment.room', 'assignments.booking'])
            ->first();

        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        return response()->json(['key' => $key], 200);
    }

    /**
     * Create a new RFID key
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|max:255|unique:rfidKeys,rfidKey'
            // Note: status and label removed - columns don't exist
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Only use columns that exist: hotels_id, isUsed, rfidKey
        $key = RFIDKey::create([
            'hotels_id' => $hotel->id,
            'rfidKey' => $request->uid,
            'isUsed' => false
        ]);

        return response()->json(['key' => $key], 201);
    }

    /**
     * Update an RFID key
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $key = RFIDKey::where('id', $id)
            ->where('hotels_id', $hotel->id)
            ->first();

        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'uid' => 'sometimes|string|max:255|unique:rfidKeys,rfidKey,' . $id,
            'status' => 'sometimes|in:available,assigned'
            // Note: lost and disabled not supported without status column
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Map status to isUsed
        if ($request->has('status')) {
            if ($request->status === 'available' && $key->activeAssignment) {
                return response()->json([
                    'error' => 'Cannot set status to available while key is assigned to a booking'
                ], 400);
            }
            $key->isUsed = ($request->status === 'assigned');
        }

        if ($request->has('uid')) {
            $key->rfidKey = $request->uid;
        }

        $key->save();

        return response()->json(['key' => $key], 200);
    }

    /**
     * Delete an RFID key
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $key = RFIDKey::where('id', $id)
            ->where('hotels_id', $hotel->id)
            ->first();

        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        // Check if key is currently assigned (isUsed = true)
        if ($key->isUsed || $key->activeAssignment) {
            return response()->json([
                'error' => 'Cannot delete RFID key that is currently assigned to a booking'
            ], 400);
        }

        $key->delete();

        return response()->json(['message' => 'RFID key deleted successfully'], 200);
    }

    /**
     * Assign RFID key to a booking
     */
    public function assign(Request $request, $id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'room_id' => 'required|exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $key = RFIDKey::where('id', $id)
            ->where('hotels_id', $hotel->id)
            ->first();

        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        $booking = Booking::find($request->booking_id);
        $room = Room::find($request->room_id);

        // Validate booking belongs to hotel
        if ($booking->hotels_id !== $hotel->id) {
            return response()->json(['error' => 'Booking does not belong to this hotel'], 403);
        }

        // Validate room belongs to hotel
        if ($room->hotels_id !== $hotel->id) {
            return response()->json(['error' => 'Room does not belong to this hotel'], 403);
        }

        // Business rules validation
        if ($booking->status !== 'confirmed' && $booking->status !== 'active') {
            return response()->json([
                'error' => 'Can only assign RFID key to confirmed or active bookings'
            ], 400);
        }

        if ($key->isUsed) {
            return response()->json([
                'error' => 'RFID key is not available for assignment'
            ], 400);
        }

        if ($key->activeAssignment) {
            return response()->json([
                'error' => 'RFID key is already assigned to another booking'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create assignment
            $assignment = RFIDAssignment::create([
                'rfid_key_id' => $key->id,
                'booking_id' => $request->booking_id,
                'room_id' => $request->room_id,
                'assigned_at' => now()
            ]);

            // Update key isUsed flag
            $key->isUsed = true;
            $key->save();

            DB::commit();

            return response()->json([
                'message' => 'RFID key assigned successfully',
                'assignment' => $assignment->load(['booking', 'room'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to assign RFID key'], 500);
        }
    }

    /**
     * Release RFID key from assignment
     */
    public function release($id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $key = RFIDKey::where('id', $id)
            ->where('hotels_id', $hotel->id)
            ->first();

        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        $assignment = $key->activeAssignment;

        if (!$assignment) {
            return response()->json([
                'error' => 'RFID key is not currently assigned'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Release assignment
            $assignment->released_at = now();
            $assignment->save();

            // Update key isUsed flag
            $key->isUsed = false;
            $key->save();

            DB::commit();

            return response()->json([
                'message' => 'RFID key released successfully',
                'key' => $key
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to release RFID key'], 500);
        }
    }

    /**
     * Get active bookings for assignment (only confirmed/active bookings)
     */
    public function getAvailableBookings()
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $bookings = Booking::where('hotels_id', $hotel->id)
            ->whereIn('status', ['confirmed', 'active'])
            ->with(['user', 'rooms'])
            ->orderBy('startDate', 'desc')
            ->get();

        $formattedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'guest_name' => $booking->user->name ?? 'Unknown',
                'guest_email' => $booking->user->email ?? '',
                'check_in' => $booking->startDate,
                'check_out' => $booking->endDate,
                'status' => $booking->status,
                'rooms' => $booking->rooms->map(function($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name
                    ];
                })
            ];
        });

        return response()->json(['bookings' => $formattedBookings], 200);
    }
}
