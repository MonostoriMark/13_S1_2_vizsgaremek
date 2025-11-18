<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\Controller;
use App\Models\Room;



class RoomController extends Controller
{
    public function getRoomsByHotelId($hotel_id){
        $rooms = Room::where('hotel_id', $hotel_id)->get();
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
        $validated = $request->validate([
            'hotels_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:100'],
            'pricePerNight' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'basePrice' => ['required', 'numeric', 'min:0']
        ]);

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
}