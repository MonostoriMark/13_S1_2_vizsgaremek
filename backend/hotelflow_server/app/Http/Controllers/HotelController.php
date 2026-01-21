<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Hotel;

class HotelController extends Controller
{
    public function getHotels(){
        $hotels = Hotel::with(['tags', 'rooms.images'])->get();
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
            'created_at' => now()
        ]);

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
    public function getHotelById($id){
        $hotel = Hotel::with(['tags', 'rooms.images'])->find($id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        return response()->json($hotel, 200);
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