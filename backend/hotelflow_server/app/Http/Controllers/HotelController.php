<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Hotel;

class HotelController extends Controller
{
    public function getHotels(){
        // If user is authenticated and is a hotel admin, only return their hotels (regardless of approval status)
        if (auth()->check() && auth()->user()->role === 'hotel') {
            $hotels = Hotel::with(['tags', 'rooms.images'])
                ->where('user_id', auth()->id())
                ->get();
        } else {
            // For public access, only show approved hotels
            $hotels = Hotel::where('is_approved', true)
                ->with(['tags', 'rooms.images'])
                ->get();
        }
        return response()->json($hotels, 200);
    }

    public function createHotel(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'type' => ['sometimes', 'in:hotel,apartment,villa,other'],
            'starRating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        // Get the authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create hotel linked to the authenticated user
        $hotel = Hotel::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'location' => $validated['location'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'starRating' => $validated['starRating'] ?? null,
            'is_approved' => false, // New hotels need approval
            'created_at' => now()
        ]);

        // Send notification to super admin about new hotel creation
        $superAdmins = \App\Models\User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $superAdmin) {
            try {
                \Illuminate\Support\Facades\Mail::to($superAdmin->email)->send(new \App\Mail\NewHotelRegistrationMail($hotel, $user));
            } catch (\Exception $e) {
                \Log::error('Failed to send new hotel notification to super admin: ' . $e->getMessage());
            }
        }

        // Send hotel creation notification to hotel owner
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\HotelCreationNotificationMail($hotel, $user));
        } catch (\Exception $e) {
            \Log::error('Failed to send hotel creation notification: ' . $e->getMessage());
        }

        // Load tags relationship
        $hotel->load('tags');
        
        return response()->json($hotel, 201);
    }
    public function upgradeHotel(Request $request, $id){
        $hotel = Hotel::find($id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $validated = $request->validate([
            'location' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'type' => ['sometimes', 'in:hotel,apartment,villa,other'],
            'starRating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);
         // -------------------------
    // Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
    // -------------------------
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        else{
            if(isset($validated['location'])){
                $hotel->location = $validated['location'];
            }
            if(isset($validated['description'])){
                $hotel->description = $validated['description'];
            }
            if(isset($validated['type'])){
                $hotel->type = $validated['type'];
            }
            if(isset($validated['starRating'])){
                $hotel->starRating = $validated['starRating'];
            }
            if(isset($validated['name'])){
                $hotel->name = $validated['name'];
            }
            $hotel->save();
        }
        
        // Reload hotel with tags relationship
        $hotel->load('tags');
        return response()->json($hotel, 200);
        
    }

    public function uploadCoverImage(Request $request, $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check permission
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $validated = $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:51200', // 50MB (will be automatically resized)
        ]);

        try {
            // Increase memory limit for image processing
            ini_set('memory_limit', '256M');
            
            // Delete old cover image if exists
            if ($hotel->cover_image) {
                $oldPath = str_replace('/storage/', '', $hotel->cover_image);
                Storage::disk('public')->delete($oldPath);
            }

            // Resize and save image
            $relativePath = \App\Helpers\ImageResizer::resizeAndStore(
                $request->file('cover_image'),
                'hotel_images',
                'cover'
            );
            
            $hotel->cover_image = $relativePath;
            $hotel->save();

            return response()->json([
                'message' => 'Cover image uploaded successfully',
                'cover_image' => $relativePath
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Hotel cover image upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload cover image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCoverImage($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check permission
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        try {
            // Delete cover image file if exists
            if ($hotel->cover_image) {
                $oldPath = str_replace('/storage/', '', $hotel->cover_image);
                Storage::disk('public')->delete($oldPath);
            }

            // Clear cover_image field
            $hotel->cover_image = null;
            $hotel->save();

            return response()->json([
                'message' => 'Cover image deleted successfully',
                'cover_image' => null
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Hotel cover image delete error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete cover image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHotelById($id){
        $hotel = Hotel::with(['tags', 'rooms.images'])->find($id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        return response()->json($hotel, 200);
    }
    public function getRecentActivities(Request $request, $hotelId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify the user owns the hotel
        $hotel = Hotel::where('id', $hotelId)
                     ->where('user_id', $user->id)
                     ->first();
        
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found or you do not have permission'], 403);
        }

        $limit = $request->query('limit', 15);
        $activities = [];

        // Get bookings
        $bookings = \App\Models\Booking::where('hotels_id', $hotelId)
            ->with('user')
            ->orderBy('createdAt', 'desc')
            ->limit(20)
            ->get();

        foreach ($bookings as $booking) {
            $statusText = [
                'pending' => 'Új foglalási kérelem',
                'confirmed' => 'Foglalás megerősítve',
                'cancelled' => 'Foglalás törölve',
                'finished' => 'Foglalás befejezve'
            ][$booking->status] ?? 'Foglalás';

            $activities[] = [
                'id' => 'booking-' . $booking->id,
                'type' => 'booking',
                'icon' => $booking->status === 'pending' ? '⏳' : ($booking->status === 'confirmed' ? '✅' : ($booking->status === 'cancelled' ? '❌' : '✓')),
                'text' => $statusText . ' - ' . ($booking->user->name ?? 'Vendég'),
                'time' => $booking->createdAt ? \Carbon\Carbon::parse($booking->createdAt)->toIso8601String() : null,
                'timestamp' => $booking->createdAt ? \Carbon\Carbon::parse($booking->createdAt)->timestamp : null
            ];
        }

        // Get RFID assignments (assigned)
        $rfidAssignments = \App\Models\RFIDAssignment::whereHas('rfidKey', function($q) use ($hotelId) {
                $q->where('hotels_id', $hotelId);
            })
            ->whereNotNull('assigned_at')
            ->with(['rfidKey', 'room', 'booking.user'])
            ->orderBy('assigned_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($rfidAssignments as $assignment) {
            $keyName = $assignment->rfidKey ? ($assignment->rfidKey->name ?: $assignment->rfidKey->rfidKey) : 'Ismeretlen kulcs';
            $roomName = $assignment->room ? $assignment->room->name : 'Ismeretlen szoba';
            $guestName = $assignment->booking && $assignment->booking->user ? $assignment->booking->user->name : 'Vendég';

            $activities[] = [
                'id' => 'rfid-assign-' . $assignment->id,
                'type' => 'rfid_assignment',
                'icon' => '🔑',
                'text' => 'RFID kulcs hozzárendelve: ' . $keyName . ' → ' . $roomName . ' (' . $guestName . ')',
                'time' => $assignment->assigned_at ? $assignment->assigned_at->toIso8601String() : null,
                'timestamp' => $assignment->assigned_at ? $assignment->assigned_at->timestamp : null
            ];
        }

        // Get RFID assignments (released)
        $rfidReleases = \App\Models\RFIDAssignment::whereHas('rfidKey', function($q) use ($hotelId) {
                $q->where('hotels_id', $hotelId);
            })
            ->whereNotNull('released_at')
            ->with(['rfidKey', 'room', 'booking.user'])
            ->orderBy('released_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($rfidReleases as $assignment) {
            $keyName = $assignment->rfidKey ? ($assignment->rfidKey->name ?: $assignment->rfidKey->rfidKey) : 'Ismeretlen kulcs';
            $roomName = $assignment->room ? $assignment->room->name : 'Ismeretlen szoba';

            $activities[] = [
                'id' => 'rfid-release-' . $assignment->id,
                'type' => 'rfid_release',
                'icon' => '🔓',
                'text' => 'RFID kulcs feloldva: ' . $keyName . ' → ' . $roomName,
                'time' => $assignment->released_at ? $assignment->released_at->toIso8601String() : null,
                'timestamp' => $assignment->released_at ? $assignment->released_at->timestamp : null
            ];
        }

        // Get rooms (using createdAt if available, otherwise skip)
        $rooms = \App\Models\Room::where('hotels_id', $hotelId)
            ->whereNotNull('createdAt')
            ->orderBy('createdAt', 'desc')
            ->limit(10)
            ->get();

        foreach ($rooms as $room) {
            $activities[] = [
                'id' => 'room-' . $room->id,
                'type' => 'room',
                'icon' => '🛏️',
                'text' => 'Szoba létrehozva: ' . ($room->name ?: 'Szoba #' . $room->id),
                'time' => $room->createdAt ? \Carbon\Carbon::parse($room->createdAt)->toIso8601String() : null,
                'timestamp' => $room->createdAt ? \Carbon\Carbon::parse($room->createdAt)->timestamp : null
            ];
        }

        // Get services (check if createdAt column exists)
        try {
            $services = \App\Models\Service::where('hotels_id', $hotelId)
                ->when(\Schema::hasColumn('services', 'createdAt'), function($query) {
                    $query->whereNotNull('createdAt')->orderBy('createdAt', 'desc');
                })
                ->limit(10)
                ->get();

            foreach ($services as $service) {
                if (isset($service->createdAt) && $service->createdAt) {
                    $activities[] = [
                        'id' => 'service-' . $service->id,
                        'type' => 'service',
                        'icon' => '✨',
                        'text' => 'Szolgáltatás létrehozva: ' . ($service->name ?: 'Szolgáltatás #' . $service->id),
                        'time' => \Carbon\Carbon::parse($service->createdAt)->toIso8601String(),
                        'timestamp' => \Carbon\Carbon::parse($service->createdAt)->timestamp
                    ];
                }
            }
        } catch (\Exception $e) {
            // Service table might not have createdAt, skip
        }

        // Sort all activities by timestamp (most recent first)
        $activities = array_filter($activities, function($a) {
            return $a['timestamp'] !== null;
        });

        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        // Take only the requested limit
        $activities = array_slice($activities, 0, $limit);

        return response()->json(['activities' => $activities], 200);
    }

    public function getHotelBillingInfo($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $hotel = Hotel::find($id);
        
        if (!$hotel) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }

        // Check if user owns this hotel - use loose comparison to handle type mismatches
        $userId = (int) $user->id;
        $hotelUserId = (int) $hotel->user_id;
        
        if ($userId !== $hotelUserId) {
            return response()->json(['error' => 'Unauthorized - You do not own this hotel'], 403);
        }

        return response()->json([
            'tax_number' => $hotel->tax_number,
            'bank_account' => $hotel->bank_account,
            'eu_tax_number' => $hotel->eu_tax_number
        ], 200);
    }

    public function updateHotelBillingInfo(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Convert ID to integer to handle string IDs from routes
        $hotelId = (int) $id;
        $hotel = Hotel::find($hotelId);
        
        if (!$hotel) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }

        // Check if hotel has a user_id
        if (!$hotel->user_id) {
            \Log::warning('Hotel has no user_id', [
                'hotel_id' => $hotel->id,
                'user_id' => $user->id
            ]);
            return response()->json([
                'error' => 'Hotel configuration error',
                'message' => 'A szállodának nincs hozzárendelt tulajdonosa.'
            ], 500);
        }

        // Check if user owns this hotel - convert both to integers for comparison
        $userId = (int) $user->id;
        $hotelUserId = (int) $hotel->user_id;
        
        if ($userId !== $hotelUserId) {
            \Log::warning('Hotel billing update unauthorized', [
                'user_id' => $user->id,
                'user_id_type' => gettype($user->id),
                'hotel_id' => $hotel->id,
                'hotel_user_id' => $hotel->user_id,
                'hotel_user_id_type' => gettype($hotel->user_id),
                'comparison' => $userId . ' !== ' . $hotelUserId,
                'request_id' => $id,
                'converted_hotel_id' => $hotelId
            ]);
            return response()->json([
                'error' => 'Unauthorized - You do not own this hotel',
                'message' => 'Csak a saját szállodái számlázási adatait módosíthatja.'
            ], 403);
        }

        $validated = $request->validate([
            'tax_number' => ['required', 'string', 'max:255'],
            'bank_account' => ['required', 'string', 'max:255'],
            'eu_tax_number' => ['required', 'string', 'max:255'],
        ]);

        // Validate tax number format
        $taxNumber = trim(str_replace(' ', '', $validated['tax_number']));
        $hungarianTaxPattern = '/^\d{8}$/';
        $euVatPattern = '/^[A-Z]{2}[A-Z0-9]{2,12}$/';
        
        if (!preg_match($hungarianTaxPattern, $taxNumber) && !preg_match($euVatPattern, $taxNumber)) {
            return response()->json([
                'error' => 'Érvénytelen adószám formátum. Használjon 8 számjegyű magyar adószámot vagy EU ÁFA számot (pl. HU12345678)'
            ], 422);
        }

        $hotel->tax_number = $validated['tax_number'];
        $hotel->bank_account = $validated['bank_account'];
        $hotel->eu_tax_number = $validated['eu_tax_number'];
        $hotel->save();

        return response()->json([
            'message' => 'Hotel billing information updated successfully',
            'hotel' => $hotel
        ], 200);
    }

    public function deleteHotel($id){
        $hotel = Hotel::find($id);
         // -------------------------
    // Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
    // -------------------------
        if ($hotel->user_id !== auth()->id()) {
        return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        $user = User::find($hotel->user_id);
        if($user){
            $user->delete();
        }
        $hotel->delete();
        return response()->json(['message' => 'Hotel deleted'], 200);
    }


}