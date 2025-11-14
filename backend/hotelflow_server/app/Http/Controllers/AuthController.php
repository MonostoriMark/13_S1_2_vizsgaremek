<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Hotel;

class AuthController extends Controller
{
    //USER REGISZTRÁCIÓ
    public function registerUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'created_at' => now()
            
]);


        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'token' => $token
        ], 201);
    }

        public function login(Request $request)
    {
        // Validáció
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Felhasználó keresése
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Új token generálása
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at, // már magyar idő
            'token' => $token
        ], 200);
    }
        public function logout(Request $request){
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out'], 200);
        }
        public function testAuth(){
            return response()->json(['message' => 'Authenticated'], 200);
        }
        public function getUserById($id){
            $user = User::find($id);
            if(!$user){
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at
            ], 200);
        }
        // Hotel regisztráció
                    public function registerHotel(Request $request)
                {
                    $validated = $request->validate([
                        'name' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                        'password' => ['required', 'string', 'min:8'],
                        'location' => ['required', 'string', 'max:255'],
                        'type' => ['required', 'in:hotel,apartment,villa,other'],
                        'starRating' => ['nullable', 'integer', 'min:1', 'max:5']

                    ]);
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'role' => 'hotel',
                        'created_at' => now()
                    ]);
                    $hotel = Hotel::create([
                        'user_id' => $user->id,
                        'location' => $validated['location'],
                        'type' => $validated['type'],
                        'starRating' => $validated['starRating'],
                        'created_at' => now()
                    ]);

                    $token = $user->createToken('api_token')->plainTextToken;
                    return response()->json([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at,
                        'token' => $token
                    ], 201);
                }
}
