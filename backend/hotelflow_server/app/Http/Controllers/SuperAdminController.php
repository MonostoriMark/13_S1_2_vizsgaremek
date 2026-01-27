<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\RFIDKey;
use App\Models\RFIDAssignment;
use App\Models\RFIDConnection;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use App\Models\BookingPayment;
use App\Models\BookingInvoiceDetail;

class SuperAdminController extends Controller
{
    // ========== USERS MANAGEMENT ==========
    public function getAllUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'isVerified', 'tax_number', 'bank_account', 'eu_tax_number', 'two_factor_enabled', 'created_at', 'updated_at')
            ->get();
        return response()->json($users, 200);
    }

    public function getUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:user,hotel,super_admin'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'eu_tax_number' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'isVerified' => $validated['role'] === 'super_admin' ? true : false,
            'tax_number' => $validated['tax_number'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'eu_tax_number' => $validated['eu_tax_number'] ?? null,
        ]);

        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'in:user,hotel,super_admin'],
            'isVerified' => ['sometimes', 'boolean'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'eu_tax_number' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['password'])) $user->password = Hash::make($validated['password']);
        if (isset($validated['role'])) $user->role = $validated['role'];
        if (isset($validated['isVerified'])) $user->isVerified = $validated['isVerified'];
        if (isset($validated['tax_number'])) $user->tax_number = $validated['tax_number'];
        if (isset($validated['bank_account'])) $user->bank_account = $validated['bank_account'];
        if (isset($validated['eu_tax_number'])) $user->eu_tax_number = $validated['eu_tax_number'];

        $user->save();
        return response()->json($user, 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }

    // ========== HOTELS MANAGEMENT ==========
    public function getAllHotels()
    {
        $hotels = Hotel::with(['user', 'tags'])->get();
        return response()->json($hotels, 200);
    }

    public function getHotel($id)
    {
        $hotel = Hotel::with(['user', 'tags', 'rooms', 'services'])->find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        return response()->json($hotel, 200);
    }

    public function createHotel(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:hotel,apartment,villa,other'],
            'starRating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $hotel = Hotel::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'location' => $validated['location'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'starRating' => $validated['starRating'] ?? null,
            'createdAt' => now()
        ]);

        $hotel->load(['user', 'tags']);
        return response()->json($hotel, 201);
    }

    public function updateHotel(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => ['sometimes', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:hotel,apartment,villa,other'],
            'starRating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        if (isset($validated['user_id'])) $hotel->user_id = $validated['user_id'];
        if (isset($validated['name'])) $hotel->name = $validated['name'];
        if (isset($validated['location'])) $hotel->location = $validated['location'];
        if (isset($validated['description'])) $hotel->description = $validated['description'];
        if (isset($validated['type'])) $hotel->type = $validated['type'];
        if (isset($validated['starRating'])) $hotel->starRating = $validated['starRating'];

        $hotel->save();
        $hotel->load(['user', 'tags']);
        return response()->json($hotel, 200);
    }

    public function deleteHotel($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        $hotel->delete();
        return response()->json(['message' => 'Hotel deleted'], 200);
    }

    // ========== ROOMS MANAGEMENT ==========
    public function getAllRooms()
    {
        $rooms = Room::with(['hotel', 'tags'])->get();
        return response()->json($rooms, 200);
    }

    public function getRoom($id)
    {
        $room = Room::with(['hotel', 'tags', 'images'])->find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        return response()->json($room, 200);
    }

    public function createRoom(Request $request)
    {
        $validated = $request->validate([
            'hotels_id' => ['required', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pricePerNight' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'basePrice' => ['required', 'numeric', 'min:0'],
        ]);

        $room = Room::create([
            'hotels_id' => $validated['hotels_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'pricePerNight' => $validated['pricePerNight'],
            'capacity' => $validated['capacity'],
            'basePrice' => $validated['basePrice'],
            'createdAt' => now()
        ]);

        $room->load(['hotel', 'tags']);
        return response()->json($room, 201);
    }

    public function updateRoom(Request $request, $id)
    {
        $validated = $request->validate([
            'hotels_id' => ['sometimes', 'exists:hotels,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pricePerNight' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'basePrice' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        if (isset($validated['hotels_id'])) $room->hotels_id = $validated['hotels_id'];
        if (isset($validated['name'])) $room->name = $validated['name'];
        if (isset($validated['description'])) $room->description = $validated['description'];
        if (isset($validated['pricePerNight'])) $room->pricePerNight = $validated['pricePerNight'];
        if (isset($validated['capacity'])) $room->capacity = $validated['capacity'];
        if (isset($validated['basePrice'])) $room->basePrice = $validated['basePrice'];

        $room->save();
        $room->load(['hotel', 'tags']);
        return response()->json($room, 200);
    }

    public function deleteRoom($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        $room->delete();
        return response()->json(['message' => 'Room deleted'], 200);
    }

    // ========== SERVICES MANAGEMENT ==========
    public function getAllServices()
    {
        $services = Service::with('hotel')->get();
        return response()->json($services, 200);
    }

    public function getService($id)
    {
        $service = Service::with('hotel')->find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json($service, 200);
    }

    public function createService(Request $request)
    {
        $validated = $request->validate([
            'hotels_id' => ['required', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $service = Service::create([
            'hotels_id' => $validated['hotels_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
        ]);

        $service->load('hotel');
        return response()->json($service, 201);
    }

    public function updateService(Request $request, $id)
    {
        $validated = $request->validate([
            'hotels_id' => ['sometimes', 'exists:hotels,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        if (isset($validated['hotels_id'])) $service->hotels_id = $validated['hotels_id'];
        if (isset($validated['name'])) $service->name = $validated['name'];
        if (isset($validated['description'])) $service->description = $validated['description'];
        if (isset($validated['price'])) $service->price = $validated['price'];

        $service->save();
        $service->load('hotel');
        return response()->json($service, 200);
    }

    public function deleteService($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        $service->delete();
        return response()->json(['message' => 'Service deleted'], 200);
    }

    // ========== BOOKINGS MANAGEMENT ==========
    public function getAllBookings()
    {
        $bookings = Booking::with(['user', 'hotel', 'rooms', 'services', 'guests', 'invoice'])->get();
        return response()->json($bookings, 200);
    }

    public function getBooking($id)
    {
        $booking = Booking::with(['user', 'hotel', 'rooms', 'services', 'guests', 'invoice'])->find($id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        return response()->json($booking, 200);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'users_id' => ['required', 'exists:users,id'],
            'hotels_id' => ['required', 'exists:hotels,id'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.id' => ['required', 'exists:rooms,id'],
            'rooms.*.guests' => ['required', 'integer', 'min:1'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'status' => ['sometimes', 'in:pending,confirmed,cancelled,finished'],
            'payment_method' => ['sometimes', 'in:bank_transfer'],
            'invoice_details' => ['sometimes', 'array'],
            'invoice_details.customer_type' => ['sometimes', 'in:private,business'],
            'invoice_details.full_name' => ['sometimes', 'string', 'max:255'],
            'invoice_details.email' => ['sometimes', 'email', 'max:255'],
            'invoice_details.company_name' => ['nullable', 'string', 'max:255'],
            'invoice_details.tax_number' => ['nullable', 'string', 'max:255'],
            'invoice_details.country' => ['nullable', 'string', 'max:255'],
            'invoice_details.city' => ['nullable', 'string', 'max:255'],
            'invoice_details.postal_code' => ['nullable', 'string', 'max:50'],
            'invoice_details.address_line' => ['nullable', 'string', 'max:255'],
            'invoice_details.note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();
        try {
            // Create booking
            $booking = Booking::create([
                'users_id' => $validated['users_id'],
                'hotels_id' => $validated['hotels_id'],
                'startDate' => $validated['startDate'],
                'endDate' => $validated['endDate'],
                'checkInToken' => str()->random(32),
                'status' => $validated['status'] ?? 'pending',
                'totalPrice' => 0,
            ]);

            // Load rooms and validate
            $requestedRoomIds = collect($validated['rooms'])->pluck('id')->toArray();
            $rooms = Room::whereIn('id', $requestedRoomIds)
                ->where('hotels_id', $validated['hotels_id'])
                ->get()
                ->keyBy('id');

            if ($rooms->count() !== count($requestedRoomIds)) {
                DB::rollBack();
                return response()->json(['message' => 'One or more rooms not found or do not belong to the selected hotel'], 400);
            }

            // Check availability
            $nights = \Carbon\Carbon::parse($validated['startDate'])->diffInDays($validated['endDate']);
            $overlappingRooms = DB::table('bookingsRelation')
                ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
                ->whereIn('bookingsRelation.rooms_id', $requestedRoomIds)
                ->whereIn('bookings.status', ['pending', 'confirmed'])
                ->where('bookings.startDate', '<', $validated['endDate'])
                ->where('bookings.endDate', '>', $validated['startDate'])
                ->distinct()
                ->pluck('bookingsRelation.rooms_id')
                ->toArray();

            if (!empty($overlappingRooms)) {
                DB::rollBack();
                return response()->json(['message' => 'One or more rooms are not available for the selected dates'], 400);
            }

            // Calculate total price and sync rooms
            $totalPrice = 0;
            $roomIds = [];

            foreach ($validated['rooms'] as $roomData) {
                $room = $rooms->get($roomData['id']);
                if (!$room) {
                    DB::rollBack();
                    return response()->json(['message' => "Room not found: ID {$roomData['id']}"], 400);
                }

                $roomIds[] = $room->id;
                $roomPrice = ($room->basePrice ?? 0) + ($room->pricePerNight * $nights);
                $totalPrice += $roomPrice;
            }

            $booking->rooms()->sync($roomIds);

            // Assign RFID keys to rooms (if available)
            $requestedStart = \Carbon\Carbon::parse($validated['startDate'])->toDateString();
            $requestedEnd = \Carbon\Carbon::parse($validated['endDate'])->toDateString();

            // Find available RFID keys (keys that don't have overlapping assignments)
            // This allows reuse of keys outside their booking dates (past or future)
            $availableRfidKeys = RFIDKey::where('hotels_id', $validated['hotels_id'])
                ->whereDoesntHave('assignments', function ($q) use ($requestedStart, $requestedEnd) {
                    $q->whereNull('released_at')
                        ->whereNotNull('reserved_from')
                        ->whereNotNull('reserved_to')
                        ->where('reserved_from', '<', $requestedEnd)
                        ->where('reserved_to', '>', $requestedStart);
                })
                ->take(count($roomIds))
                ->get();

            // Only assign if we have enough keys (don't fail if we don't have enough)
            if ($availableRfidKeys->count() >= count($roomIds)) {
                foreach ($roomIds as $index => $roomId) {
                    $rfidKey = $availableRfidKeys->get($index);
                    
                    if ($rfidKey) {
                        // Create RFID assignment
                        RFIDAssignment::create([
                            'rfid_key_id' => $rfidKey->id,
                            'booking_id' => $booking->id,
                            'room_id' => $roomId,
                            'reserved_from' => $requestedStart,
                            'reserved_to' => $requestedEnd,
                            'assigned_at' => now(),
                            'released_at' => null,
                        ]);

                        // Create or update RFID connection (links RFID key to room)
                        // This is what the device endpoint uses to know which key opens which room
                        RFIDConnection::updateOrCreate(
                            [
                                'rfidKeys_id' => $rfidKey->rfidKey,
                                'rooms_id' => $roomId
                            ],
                            [
                                'rfidKeys_id' => $rfidKey->rfidKey,
                                'rooms_id' => $roomId
                            ]
                        );
                    }
                }
            }

            // Add services if provided
            if (!empty($validated['services'])) {
                $validServices = Service::whereIn('id', $validated['services'])
                    ->where('hotels_id', $validated['hotels_id'])
                    ->pluck('id')
                    ->toArray();

                if (count($validServices) !== count($validated['services'])) {
                    DB::rollBack();
                    return response()->json(['message' => 'One or more services do not belong to the selected hotel'], 400);
                }

                $booking->services()->sync($validServices);
                $servicesPrice = Service::whereIn('id', $validServices)->sum('price');
                $totalPrice += $servicesPrice;
            }

            // Update total price
            $booking->totalPrice = $totalPrice;
            $booking->save();

            // Store payment and invoice details if provided
            if (isset($validated['payment_method'])) {
                BookingPayment::firstOrCreate(
                    ['booking_id' => $booking->id],
                    ['method' => $validated['payment_method'], 'status' => 'pending']
                );
            }

            if (isset($validated['invoice_details'])) {
                BookingInvoiceDetail::firstOrCreate(
                    ['booking_id' => $booking->id],
                    $validated['invoice_details']
                );
            }

            DB::commit();

            $booking->load(['user', 'hotel', 'rooms', 'services', 'guests', 'invoice']);
            return response()->json($booking, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create booking: ' . $e->getMessage()], 500);
        }
    }

    public function updateBooking(Request $request, $id)
    {
        $validated = $request->validate([
            'users_id' => ['sometimes', 'exists:users,id'],
            'hotels_id' => ['sometimes', 'exists:hotels,id'],
            'startDate' => ['sometimes', 'date'],
            'endDate' => ['sometimes', 'date', 'after:startDate'],
            'totalPrice' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if (isset($validated['users_id'])) $booking->users_id = $validated['users_id'];
        if (isset($validated['hotels_id'])) $booking->hotels_id = $validated['hotels_id'];
        if (isset($validated['startDate'])) $booking->startDate = $validated['startDate'];
        if (isset($validated['endDate'])) $booking->endDate = $validated['endDate'];
        if (isset($validated['totalPrice'])) $booking->totalPrice = $validated['totalPrice'];
        if (isset($validated['status'])) $booking->status = $validated['status'];

        $booking->save();
        $booking->load(['user', 'hotel', 'rooms', 'services', 'guests', 'invoice']);
        return response()->json($booking, 200);
    }

    public function deleteBooking($id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        $booking->delete();
        return response()->json(['message' => 'Booking deleted'], 200);
    }

    // ========== INVOICES MANAGEMENT ==========
    public function getAllInvoices()
    {
        $invoices = Invoice::with(['booking.user', 'booking.hotel'])->get();
        return response()->json($invoices, 200);
    }

    public function getInvoice($id)
    {
        $invoice = Invoice::with(['booking.user', 'booking.hotel', 'booking.rooms', 'booking.services'])->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        return response()->json($invoice, 200);
    }

    public function updateInvoice(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:draft,approved,sent'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'tax_amount' => ['sometimes', 'numeric', 'min:0'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if (isset($validated['status'])) $invoice->status = $validated['status'];
        if (isset($validated['subtotal'])) $invoice->subtotal = $validated['subtotal'];
        if (isset($validated['tax_amount'])) $invoice->tax_amount = $validated['tax_amount'];
        if (isset($validated['total_amount'])) $invoice->total_amount = $validated['total_amount'];

        $invoice->save();
        $invoice->load(['booking.user', 'booking.hotel']);
        return response()->json($invoice, 200);
    }

    public function deleteInvoice($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted'], 200);
    }

    // ========== RFID KEYS MANAGEMENT ==========
    public function getAllRFIDKeys()
    {
        $keys = RFIDKey::with(['hotel', 'assignments.booking', 'assignments.room'])->get();
        return response()->json($keys, 200);
    }

    public function getRFIDKey($id)
    {
        $key = RFIDKey::with(['hotel', 'assignments.booking', 'assignments.room'])->find($id);
        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }
        return response()->json($key, 200);
    }

    public function createRFIDKey(Request $request)
    {
        $validated = $request->validate([
            'hotels_id' => ['required', 'exists:hotels,id'],
            'rfidKey' => ['required', 'string', 'max:255', 'unique:rfidKeys,rfidKey'],
        ]);

        $key = RFIDKey::create([
            'hotels_id' => $validated['hotels_id'],
            'rfidKey' => $validated['rfidKey'],
            'isUsed' => false
        ]);

        $key->load(['hotel']);
        return response()->json($key, 201);
    }

    public function updateRFIDKey(Request $request, $id)
    {
        $validated = $request->validate([
            'hotels_id' => ['sometimes', 'exists:hotels,id'],
            'rfidKey' => ['sometimes', 'string', 'max:255', 'unique:rfidKeys,rfidKey,' . $id],
            'isUsed' => ['sometimes', 'boolean'],
        ]);

        $key = RFIDKey::find($id);
        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }

        if (isset($validated['hotels_id'])) $key->hotels_id = $validated['hotels_id'];
        if (isset($validated['rfidKey'])) $key->rfidKey = $validated['rfidKey'];
        if (isset($validated['isUsed'])) $key->isUsed = $validated['isUsed'];

        $key->save();
        $key->load(['hotel', 'assignments.booking', 'assignments.room']);
        return response()->json($key, 200);
    }

    public function deleteRFIDKey($id)
    {
        $key = RFIDKey::find($id);
        if (!$key) {
            return response()->json(['message' => 'RFID key not found'], 404);
        }
        $key->delete();
        return response()->json(['message' => 'RFID key deleted'], 200);
    }

    // ========== DEVICES MANAGEMENT ==========
    public function getAllDevices()
    {
        $devices = Device::with('hotel')->get();
        return response()->json($devices, 200);
    }

    public function getDevice($id)
    {
        $device = Device::with('hotel')->find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }
        return response()->json($device, 200);
    }

    public function createDevice(Request $request)
    {
        $validated = $request->validate([
            'hotels_id' => ['required', 'exists:hotels,id', 'unique:devices,hotels_id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        // Check if hotel already has a device
        $existingDevice = Device::where('hotels_id', $validated['hotels_id'])->first();
        if ($existingDevice) {
            return response()->json(['message' => 'Hotel already has a registered device'], 409);
        }

        // Generate lifetime token
        $token = Device::generateToken();

        $device = Device::create([
            'hotels_id' => $validated['hotels_id'],
            'name' => $validated['name'],
            'token' => $token,
            'is_active' => false, // Requires admin review
        ]);

        $device->load('hotel');
        
        // Return device with token (only shown once during creation)
        return response()->json([
            'device' => $device,
            'token' => $token, // Show token only on creation
            'message' => 'Device registered successfully. Please save the token securely.'
        ], 201);
    }

    public function updateDevice(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'hotels_id' => ['sometimes', 'exists:hotels,id', 'unique:devices,hotels_id,' . $id],
        ]);

        $device = Device::find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        if (isset($validated['name'])) $device->name = $validated['name'];
        if (isset($validated['is_active'])) $device->is_active = $validated['is_active'];
        if (isset($validated['hotels_id'])) {
            // Check if new hotel already has a device
            $existingDevice = Device::where('hotels_id', $validated['hotels_id'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingDevice) {
                return response()->json(['message' => 'Hotel already has a registered device'], 409);
            }
            $device->hotels_id = $validated['hotels_id'];
        }

        $device->save();
        $device->load('hotel');
        return response()->json($device, 200);
    }

    public function regenerateToken($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        // Generate new token
        $token = Device::generateToken();
        $device->token = $token;
        $device->save();

        return response()->json([
            'device' => $device,
            'token' => $token, // Show token only on regeneration
            'message' => 'Token regenerated successfully. Please save the new token securely.'
        ], 200);
    }

    public function deleteDevice($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }
        $device->delete();
        return response()->json(['message' => 'Device deleted'], 200);
    }

    // ========== DASHBOARD STATS ==========
    public function getDashboardStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_hotels' => Hotel::count(),
            'total_rooms' => Room::count(),
            'total_services' => Service::count(),
            'total_bookings' => Booking::count(),
            'total_invoices' => Invoice::count(),
            'total_rfid_keys' => RFIDKey::count(),
            'total_devices' => Device::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'recent_bookings' => Booking::with(['user', 'hotel'])
                ->orderBy('startDate', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats, 200);
    }
}
