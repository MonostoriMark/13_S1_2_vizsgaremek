<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\Controller;

use App\Models\Room;
use App\Models\Hotel;
use App\Models\RFIDConnection;
use App\Models\RFIDKey;



class RoomController extends Controller
{
    public function getRoomsByHotelId($hotel_id){
        $rooms = Room::where('hotels_id', $hotel_id)->get();
        return response()->json($rooms, 200);
    }

    public function getRoomById($id){
        $room = Room::find($id);
        if(!$room){
            return response()->json(['message' => 'Room not found'], 404);
        }
        return response()->json($room, 200);
    }
    public function createRoom($hotel_id, Request $request){
        // Fetch the hotel
        $hotel = Hotel::find($hotel_id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $validated = $request->validate([
            'hotels_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:100'],
            'pricePerNight' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'basePrice' => ['required', 'numeric', 'min:0']
        ]);

        // Verify that the hotels_id matches the hotel_id from the route
        if ($validated['hotels_id'] != $hotel_id) {
            return response()->json(['error' => 'Hotel ID mismatch'], 400);
        }

        $room = Room::create([
            'hotels_id' => $validated['hotels_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'pricePerNight' => $validated['pricePerNight'],
            'capacity' => $validated['capacity'],
            'basePrice' => $validated['basePrice'],
            'createdAt' => now()
        ]);

        return response()->json($room, 201);
    }
    public function deleteRoom($id){
        $room = Room::find($id);
        if(!$room){
            return response()->json(['message' => 'Room not found'], 404);
        }

        // Fetch the hotel and check if it belongs to the authenticated user
        $hotel = Hotel::find($room->hotels_id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $room->delete();
        return response()->json(['message' => 'Room deleted successfully'], 200);
    }
    public function updateRoom(Request $request, $id){
        $room = Room::find($id);
        if(!$room){
            return response()->json(['message' => 'Room not found'], 404);
        }

        // Fetch the hotel and check if it belongs to the authenticated user
        $hotel = Hotel::find($room->hotels_id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'string', 'max:100'],
            'pricePerNight' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'basePrice' => ['sometimes', 'numeric', 'min:0']
        ]);

        if(isset($validated['name'])){
            $room->name = $validated['name'];
        }
        if(isset($validated['description'])){
            $room->description = $validated['description'];
        }
        if(isset($validated['pricePerNight'])){
            $room->pricePerNight = $validated['pricePerNight'];
        }
        if(isset($validated['capacity'])){
            $room->capacity = $validated['capacity'];
        }
        if(isset($validated['basePrice'])){
            $room->basePrice = $validated['basePrice'];
        }
        $room->save();

        return response()->json($room, 200);
    }


   
}