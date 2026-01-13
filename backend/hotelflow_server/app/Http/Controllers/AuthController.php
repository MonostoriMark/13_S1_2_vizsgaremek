<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Hotel;
use App\Mail\EmailVerificationMail;

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

        // Generate verification token
        $verificationToken = Str::random(64);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'isVerified' => false,
            'email_verification_token' => $verificationToken,
            'created_at' => now()
        ]);

        // Send verification email
        $verificationUrl = config('app.url') . '/api/auth/verify-email/' . $verificationToken;
        Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'isVerified' => false,
            'created_at' => $user->created_at,
            'token' => $token,
            'message' => 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet.'
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

        // Check if email is verified
        if (!$user->isVerified) {
            return response()->json([
                'message' => 'Kérjük, erősítsd meg az e-mail címedet a bejelentkezés előtt. Ellenőrizd az e-mail fiókodat.',
                'email_verified' => false
            ], 403);
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
                        'hotelName' => ['required', 'string', 'max:255'],
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
                        'name' => $validated['hotelName'],
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
                public function me(Request $request){
                        $user = $request->user();
                        return response()->json([
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'created_at' => $user->created_at
                        ], 200);
                    }
                public function updateUser(Request $request, $id)
                    {
                        $validated = $request->validate([
                            'name' => ['sometimes', 'string', 'max:255'],
                            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                            'password' => ['sometimes', 'string', 'min:8']
                        ]);

                        $user = User::find($id);
                         // -------------------------
    // Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
    // -------------------------
                        if ($user->id !== auth()->id()) {
                            return response()->json(['error' => 'Nincs jogosultságod'], 403);
                        }
                        if (!$user) {
                            return response()->json(['message' => 'User not found'], 404);
                        }

                        // Csak azt frissítsd, amit kaptál
                        if (isset($validated['name'])) {
                            $user->name = $validated['name'];
                        }

                        if (isset($validated['email'])) {
                            $user->email = $validated['email'];
                        }

                        if (isset($validated['password'])) {
                            $user->password = Hash::make($validated['password']);
                        }

                        $user->save();

                        return response()->json([
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'created_at' => $user->created_at
                        ], 200);
                    }
                
                public function deleteUser($id){
                    $user = User::find($id);
                     // -------------------------
    // Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
    // -------------------------
                    if ($user->id !== auth()->id()) {
                        return response()->json(['error' => 'Nincs jogosultságod'], 403);
                    }
                    if(!$user){
                        return response()->json(['message' => 'User not found'], 404);
                    }
                    $user->delete();
                    return response()->json(['message' => 'User deleted'], 200);
                }

                // Email verification endpoint
                public function verifyEmail($token)
                {
                    $user = User::where('email_verification_token', $token)->first();

                    if (!$user) {
                        return response()->json([
                            'message' => 'Érvénytelen vagy lejárt megerősítési link.'
                        ], 404);
                    }

                    if ($user->isVerified) {
                        return response()->json([
                            'message' => 'Az e-mail cím már megerősítve van.'
                        ], 400);
                    }

                    // Verify the user
                    $user->isVerified = true;
                    $user->email_verified_at = now();
                    $user->email_verification_token = null;
                    $user->save();

                    return response()->json([
                        'message' => 'E-mail cím sikeresen megerősítve! Most már bejelentkezhetsz.'
                    ], 200);
                }

                // Resend verification email
                public function resendVerificationEmail(Request $request)
                {
                    $validated = $request->validate([
                        'email' => ['required', 'string', 'email'],
                    ]);

                    $user = User::where('email', $validated['email'])->first();

                    if (!$user) {
                        return response()->json([
                            'message' => 'Felhasználó nem található ezzel az e-mail címmel.'
                        ], 404);
                    }

                    if ($user->isVerified) {
                        return response()->json([
                            'message' => 'Az e-mail cím már megerősítve van.'
                        ], 400);
                    }

                    // Generate new verification token
                    $verificationToken = Str::random(64);
                    $user->email_verification_token = $verificationToken;
                    $user->save();

                    // Send verification email
                    $verificationUrl = config('app.url') . '/api/auth/verify-email/' . $verificationToken;
                    Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

                    return response()->json([
                        'message' => 'Megerősítő e-mail újraküldve. Kérjük, ellenőrizd az e-mail fiókodat.'
                    ], 200);
                }
}
