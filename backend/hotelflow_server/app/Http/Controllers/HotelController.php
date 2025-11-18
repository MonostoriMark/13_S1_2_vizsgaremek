<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Hotel;

class HotelController extends Controller
{
    public function getHotels(){
        $hotels = Hotel::all();
        return response()->json($hotels, 200);
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
            'type' => ['sometimes', 'string', 'max:100'],
            'starRating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);

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
                $user = User::find($hotel->user_id);
                if($user){
                    $user->name = $validated['name'];
                    $user->save();
                }
            }
            $hotel->save();
        }       
        return response()->json($hotel, $user,200);
        
    }
    public function getHotelById($id){
        $hotel = Hotel::find($id);
        if(!$hotel){
            return response()->json(['message' => 'Hotel not found'], 404);
        }
        return response()->json($hotel, 200);
    }
    public function deleteHotel($id){
        $hotel = Hotel::find($id);
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