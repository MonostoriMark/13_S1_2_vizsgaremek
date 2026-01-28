<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RFIDKey;
use App\Models\RFIDAssignment;
use App\Models\RFIDConnection;
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
            
            // Get hotel_id from request or from user's first hotel
            $hotelId = $request->query('hotel_id');
            
            if ($hotelId) {
                // Verify the user owns the requested hotel
                $hotel = Hotel::where('id', $hotelId)
                             ->where('user_id', $user->id)
                             ->first();
                
                if (!$hotel) {
                    return response()->json(['message' => 'Hotel not found or you do not have permission'], 403);
                }
            } else {
                // Fallback: Get first hotel belonging to the user
                $hotel = Hotel::where('user_id', $user->id)->first();
                
                if (!$hotel) {
                    return response()->json(['message' => 'Hotel not found'], 404);
                }
                $hotelId = $hotel->id;
            }

        $query = RFIDKey::where('hotels_id', $hotelId);

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
                'type' => $key->type ?? 'guest',
                'name' => $key->name,
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
     * Get RFID key assignments for calendar view for the authenticated hotel admin.
     * Returns all assignments (past, current, future) for the hotel's RFID keys.
     */
    public function calendarAssignments(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Determine hotel for this admin
            $hotel = Hotel::where('user_id', $user->id)->first();
            if (!$hotel) {
                return response()->json(['message' => 'Hotel not found'], 404);
            }

            // Optional date range filters
            $start = $request->query('start_date');
            $end = $request->query('end_date');

            $query = RFIDAssignment::whereHas('rfidKey', function ($q) use ($hotel) {
                $q->where('hotels_id', $hotel->id);
            })->with(['rfidKey', 'booking.user', 'room']);

            if ($start) {
                $query->whereDate('reserved_to', '>=', $start);
            }
            if ($end) {
                $query->whereDate('reserved_from', '<=', $end);
            }

            // Only include assignments where the underlying RFID key is a guest card
            $assignments = $query->whereHas('rfidKey', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('type')->orWhere('type', 'guest');
                });
            })->orderBy('reserved_from')->get();

            $events = $assignments->map(function ($assignment) {
                $booking = $assignment->booking;
                $user = $booking ? $booking->user : null;
                $room = $assignment->room;
                $key = $assignment->rfidKey;

                return [
                    'id' => $assignment->id,
                    'rfid_key_id' => $assignment->rfid_key_id,
                    'rfid_uid' => $key ? $key->rfidKey : null,
                    'type' => $key ? ($key->type ?? 'guest') : 'guest',
                    'booking_id' => $assignment->booking_id,
                    'room_id' => $assignment->room_id,
                    'room_name' => $room ? $room->name : null,
                    'guest_name' => $user ? $user->name : null,
                    'guest_email' => $user ? $user->email : null,
                    'status' => $booking ? $booking->status : null,
                    'reserved_from' => optional($assignment->reserved_from)->toDateString(),
                    'reserved_to' => optional($assignment->reserved_to)->toDateString(),
                    'assigned_at' => optional($assignment->assigned_at)->toDateTimeString(),
                    'released_at' => optional($assignment->released_at)->toDateTimeString(),
                ];
            });

            return response()->json(['events' => $events], 200);
        } catch (\Exception $e) {
            \Log::error('RFID calendar error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error loading RFID calendar data',
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
        
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|max:255|unique:rfidKeys,rfidKey',
            'hotel_id' => 'required|integer|exists:hotels,id',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:guest,crew',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify the user owns the hotel
        $hotel = Hotel::where('id', $request->hotel_id)
                     ->where('user_id', $user->id)
                     ->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found or you do not have permission'], 403);
        }

        // Only use columns that exist: hotels_id, isUsed, rfidKey
        $key = RFIDKey::create([
            'hotels_id' => $hotel->id,
            'rfidKey' => $request->uid,
            'name' => $request->name,
            'type' => $request->input('type', 'guest'),
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
            'status' => 'sometimes|in:available,assigned',
            'name' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|in:guest,crew'
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

        if ($request->has('name')) {
            $key->name = $request->name;
        }

        if ($request->has('type')) {
            $key->type = $request->type;
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
            // Get booking dates for reservation period
            $booking = Booking::find($request->booking_id);
            $reservedFrom = $booking ? \Carbon\Carbon::parse($booking->startDate)->toDateString() : null;
            $reservedTo = $booking ? \Carbon\Carbon::parse($booking->endDate)->toDateString() : null;

            // Create assignment
            $assignment = RFIDAssignment::create([
                'rfid_key_id' => $key->id,
                'booking_id' => $request->booking_id,
                'room_id' => $request->room_id,
                'reserved_from' => $reservedFrom,
                'reserved_to' => $reservedTo,
                'assigned_at' => now()
            ]);

            // Create or update RFID connection (links RFID key to room)
            RFIDConnection::updateOrCreate(
                [
                    'rfidKeys_id' => $key->rfidKey,
                    'rooms_id' => $request->room_id
                ],
                [
                    'rfidKeys_id' => $key->rfidKey,
                    'rooms_id' => $request->room_id
                ]
            );

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
     * Manually assign RFID key to a room for a custom period (or lifetime),
     * without linking to a specific booking.
     */
    public function assignToRoom(Request $request, $id)
    {
        $user = auth()->user();
        $hotel = Hotel::where('user_id', $user->id)->first();

        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_ids'   => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'lifetime'   => 'sometimes|boolean',
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

        // Only crew cards can be manually assigned to rooms
        if (($key->type ?? 'guest') !== 'crew') {
            return response()->json([
                'error' => 'Csak személyzeti kártyák rendelhetők kézzel szobákhoz'
            ], 400);
        }

        if ($key->isUsed || $key->activeAssignment) {
            return response()->json([
                'error' => 'RFID key is not available for assignment'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $reservedFrom = \Carbon\Carbon::parse($request->start_date)->toDateString();

            // Lifetime assignment: extend far into the future
            if ($request->boolean('lifetime')) {
                $reservedTo = \Carbon\Carbon::create(2099, 12, 31)->toDateString();
            } else {
                $reservedTo = $request->end_date
                    ? \Carbon\Carbon::parse($request->end_date)->toDateString()
                    : $reservedFrom;
            }

            foreach ($request->room_ids as $roomId) {
                $room = Room::find($roomId);
                if (!$room || $room->hotels_id !== $hotel->id) {
                    continue; // Skip invalid rooms silently
                }

                $assignment = RFIDAssignment::create([
                    'rfid_key_id'   => $key->id,
                    'booking_id'    => null,
                    'room_id'       => $room->id,
                    'reserved_from' => $reservedFrom,
                    'reserved_to'   => $reservedTo,
                    'assigned_at'   => now(),
                ]);

                // Link key UID to room for device mapping
                RFIDConnection::updateOrCreate(
                    [
                        'rfidKeys_id' => $key->rfidKey,
                        'rooms_id'    => $room->id,
                    ],
                    [
                        'rfidKeys_id' => $key->rfidKey,
                        'rooms_id'    => $room->id,
                    ]
                );
            }

            $key->isUsed = true;
            $key->save();

            DB::commit();

            return response()->json([
                'message' => 'RFID key assigned to room successfully',
                'assignment' => $assignment->load(['room'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to assign RFID key to room'], 500);
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
