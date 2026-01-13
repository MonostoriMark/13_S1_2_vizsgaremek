<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    // 1️⃣ Kép feltöltése + kapcsolás szobához/ szobákhoz
    public function store(Request $request)
    {
        // Get rooms array - handle both array format and FormData format
        $roomsInput = $request->input('rooms');
        
        // If rooms is not an array, try to parse it
        if (!is_array($roomsInput)) {
            // Try to decode if it's JSON
            if (is_string($roomsInput)) {
                $decoded = json_decode($roomsInput, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $roomsInput = $decoded;
                } else {
                    // If single value, make it array
                    $roomsInput = [$roomsInput];
                }
            } else {
                $roomsInput = [];
            }
        }

        // Ensure all values are integers
        $roomsInput = array_map('intval', array_filter($roomsInput, function($val) {
            return is_numeric($val);
        }));

        // Debug: Log the request data
        \Log::info('Image upload request', [
            'has_image' => $request->hasFile('image'),
            'has_image_input' => $request->input('image'),
            'all_files' => $request->allFiles(),
            'rooms_input' => $request->input('rooms'),
            'rooms_parsed' => $roomsInput,
            'rooms_type' => gettype($roomsInput),
            'all_inputs' => array_keys($request->all())
        ]);

        // Check if image file exists
        if (!$request->hasFile('image')) {
            \Log::error('Image file missing in request', [
                'all_files' => $request->allFiles(),
                'all_inputs' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['image' => ['The image field is required.']]
            ], 422);
        }

        try {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:4096', // 4MB, specific mime types
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Image upload validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Validate rooms separately
        if (empty($roomsInput) || count($roomsInput) < 1) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['rooms' => ['At least one room ID is required']]
            ], 422);
        }

        // Validate each room exists
        $validRooms = Room::whereIn('id', $roomsInput)->pluck('id')->toArray();
        if (count($validRooms) !== count($roomsInput)) {
            $invalidRooms = array_diff($roomsInput, $validRooms);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['rooms' => ['Invalid room IDs: ' . implode(', ', $invalidRooms)]]
            ], 422);
        }

        // Check if user has permission to upload images for these rooms
        $rooms = Room::whereIn('id', $validRooms)->get();
        
        if ($rooms->isEmpty()) {
            return response()->json(['message' => 'No valid rooms found'], 404);
        }

        foreach ($rooms as $room) {
            if ($room->hotel->user_id !== auth()->id()) {
                return response()->json(['message' => 'Nincs jogosultság'], 403);
            }
        }

        try {
            // fájl mentése
            $path = $request->file('image')->store('room_images', 'public');

            // Store relative path in database (e.g., /storage/room_images/xxx.jpg)
            // This allows the frontend to construct the full URL as needed
            $relativePath = '/storage/' . $path;

            // új image rekord létrehozása
            $image = Image::create([
                'url' => $relativePath
            ]);

            // kapcsolatok mentése
            $image->rooms()->sync($validRooms);

            return response()->json([
                'message' => 'Kép feltöltve és kapcsolva',
                'image' => $image
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2️⃣ Létező kép hozzárendelése szobákhoz
    public function link(Request $request)
    {
        $request->validate([
            'imageId' => 'required|exists:images,id',
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'exists:rooms,id'
        ]);

        $image = Image::find($request->imageId);
        $image->rooms()->syncWithoutDetaching($request->rooms);

        return response()->json(['message' => 'Kapcsolat létrehozva']);
    }

    // 3️⃣ Kapcsolat eltávolítása (nem törli a képet)
    public function unlink(Request $request)
    {
        $request->validate([
            'imageId' => 'required|exists:images,id',
            'roomId' => 'required|exists:rooms,id'
        ]);

        $image = Image::find($request->imageId);
        $image->rooms()->detach($request->roomId);

        return response()->json(['message' => 'Kapcsolat törölve']);
    }

    // 4️⃣ Kép törlése (file + relations + db)
    public function destroy($id)
    {
        $image = Image::findOrFail($id);

        // Storage-ből törlés
        $filePath = str_replace('/storage/', '', $image->url);
        Storage::disk('public')->delete($filePath);

        // DB törlés
        $image->delete();

        return response()->json(['message' => 'Kép törölve']);
    }

    // 5️⃣ Szoba képei
    public function roomImages($roomId)
    {
        $room = Room::with('images')->findOrFail($roomId);

        return response()->json($room->images);
    }

    // 6️⃣ Egy kép adatai + mely szobákhoz tartozik
    public function show($id)
    {
        $image = Image::with('rooms')->findOrFail($id);
        return response()->json($image);
    }
}
