<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Hotel;
use App\Mail\EmailVerificationMail;
use App\Mail\PasswordResetMail;
use App\Helpers\TOTP;
use Endroid\QrCode\Builder\Builder;

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
        $frontendUrl = config('app.frontend_url');
        $verificationUrl = $frontendUrl . '/verify-email/' . $verificationToken;
        Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

        // DO NOT create token - user must verify email first
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'isVerified' => false,
            'created_at' => $user->created_at,
            'message' => 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet a bejelentkezés előtt. Ellenőrizd az e-mail fiókodat.'
        ], 201);
    }

        public function login(Request $request)
    {
        // Validáció
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'two_factor_code' => ['nullable', 'string', 'size:6'],
        ]);

        // Felhasználó keresése
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if email is verified (except for super_admin)
        if (!$user->isVerified && $user->role !== 'super_admin') {
            return response()->json([
                'message' => 'Kérjük, erősítsd meg az e-mail címedet a bejelentkezés előtt. Ellenőrizd az e-mail fiókodat.',
                'email_verified' => false
            ], 403);
        }

        // Super admin requires 2FA (mandatory)
        if ($user->role === 'super_admin') {
            if (!$user->two_factor_enabled) {
                // First time setup - generate secret
                $secret = TOTP::generateSecret();
                $user->two_factor_secret = $secret;
                $user->two_factor_enabled = true;
                $user->save();

                $qrCodeUrl = TOTP::getQRCodeUrl($user->email, $secret);
                
                // Generate QR code image using Builder
                $qrResult = Builder::create()
                    ->data($qrCodeUrl)
                    ->size(300)
                    ->build();
                $qrCodeBase64 = base64_encode($qrResult->getString());

                return response()->json([
                    'requires_2fa_setup' => true,
                    'two_factor_secret' => $secret,
                    'qr_code' => 'data:image/png;base64,' . $qrCodeBase64,
                    'message' => 'Please scan the QR code with your authenticator app and enter the code to complete setup.'
                ], 200);
            }

            // 2FA is enabled, verify code
            if (!isset($validated['two_factor_code'])) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required'
                ], 200);
            }

            if (!TOTP::verify($user->two_factor_secret, $validated['two_factor_code'])) {
                return response()->json([
                    'message' => 'Invalid two-factor authentication code'
                ], 401);
            }
        }

        // For regular users and hotel admins: 2FA is optional
        // If 2FA is enabled, verify the code
        if ($user->two_factor_enabled && $user->role !== 'super_admin') {
            if (!isset($validated['two_factor_code'])) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required'
                ], 200);
            }

            if (!TOTP::verify($user->two_factor_secret, $validated['two_factor_code'])) {
                return response()->json([
                    'message' => 'Invalid two-factor authentication code'
                ], 401);
            }
        }

        // Új token generálása
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'two_factor_enabled' => $user->two_factor_enabled ?? false,
            'created_at' => $user->created_at,
            'token' => $token,
            'show_2fa_prompt' => !$user->two_factor_enabled && $user->role !== 'super_admin'
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

            // Generate verification token
            $verificationToken = Str::random(64);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'hotel',
                'isVerified' => false,
                'email_verification_token' => $verificationToken,
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

            // Send verification email
            $frontendUrl = config('app.frontend_url');
            $verificationUrl = $frontendUrl . '/verify-email/' . $verificationToken;
            Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

            // DO NOT create token - user must verify email first
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'isVerified' => false,
                'created_at' => $user->created_at,
                'message' => 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet a bejelentkezés előtt. Ellenőrizd az e-mail fiókodat.'
            ], 201);
        }
                public function me(Request $request){
                        $user = $request->user();
                        return response()->json([
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'tax_number' => $user->tax_number,
                            'bank_account' => $user->bank_account,
                            'eu_tax_number' => $user->eu_tax_number,
                            'two_factor_enabled' => $user->two_factor_enabled ?? false,
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
                            'two_factor_enabled' => $user->two_factor_enabled ?? false,
                            'created_at' => $user->created_at
                        ], 200);
                    }
                
                public function deleteUser(Request $request, $id){
                    $user = User::find($id);
                    
                    // Check if user exists
                    if(!$user){
                        return response()->json(['message' => 'User not found'], 404);
                    }
                    
                    // Check if user is trying to delete themselves
                    if ($user->id !== auth()->id()) {
                        return response()->json(['error' => 'Nincs jogosultságod'], 403);
                    }
                    
                    // Prevent super admin from deleting themselves
                    if ($user->role === 'super_admin') {
                        return response()->json(['error' => 'Super admin accounts cannot be deleted'], 403);
                    }
                    
                    // Verify password before deletion
                    $validated = $request->validate([
                        'password' => ['required', 'string'],
                    ]);
                    
                    if (!Hash::check($validated['password'], $user->password)) {
                        return response()->json([
                            'message' => 'Invalid password'
                        ], 401);
                    }
                    
                    // Delete user (cascade will handle related data)
                    $user->delete();
                    return response()->json(['message' => 'Account deleted successfully'], 200);
                }

                // Admin endpoint to update their own user data (including invoice fields)
                public function updateUserAdmin(Request $request)
                {
                    // Only hotel admins can access this
                    if (auth()->user()->role !== 'hotel') {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }

                    $user = auth()->user();
                    if (!$user) {
                        return response()->json(['message' => 'User not found'], 404);
                    }

                    $validated = $request->validate([
                        'name' => ['sometimes', 'string', 'max:255'],
                        'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                        'password' => ['sometimes', 'string', 'min:8'],
                        'tax_number' => ['nullable', 'string', 'max:255'],
                        'bank_account' => ['nullable', 'string', 'max:255'],
                        'eu_tax_number' => ['nullable', 'string', 'max:255'],
                    ]);

                    // Update fields
                    if (isset($validated['name'])) {
                        $user->name = $validated['name'];
                    }

                    if (isset($validated['email'])) {
                        $user->email = $validated['email'];
                    }

                    if (isset($validated['password'])) {
                        $user->password = Hash::make($validated['password']);
                    }

                    if (isset($validated['tax_number'])) {
                        $user->tax_number = $validated['tax_number'];
                    }

                    if (isset($validated['bank_account'])) {
                        $user->bank_account = $validated['bank_account'];
                    }

                    if (isset($validated['eu_tax_number'])) {
                        $user->eu_tax_number = $validated['eu_tax_number'];
                    }

                    $user->save();

                    return response()->json([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'tax_number' => $user->tax_number,
                        'bank_account' => $user->bank_account,
                        'eu_tax_number' => $user->eu_tax_number,
                        'two_factor_enabled' => $user->two_factor_enabled ?? false,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ], 200);
                }

                // Enable 2FA for any user
                public function enable2FA(Request $request)
                {
                    $user = auth()->user();
                    if (!$user) {
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }

                    // Generate secret if not exists
                    if (!$user->two_factor_secret) {
                        $secret = TOTP::generateSecret();
                        $user->two_factor_secret = $secret;
                        $user->save();
                    } else {
                        $secret = $user->two_factor_secret;
                    }

                    $qrCodeUrl = TOTP::getQRCodeUrl($user->email, $secret);
                    
                    // Generate QR code image
                    $qrResult = Builder::create()
                        ->data($qrCodeUrl)
                        ->size(300)
                        ->build();
                    $qrCodeBase64 = base64_encode($qrResult->getString());

                    return response()->json([
                        'two_factor_secret' => $secret,
                        'qr_code' => 'data:image/png;base64,' . $qrCodeBase64,
                        'message' => 'Scan the QR code with your authenticator app'
                    ], 200);
                }

                // Verify and enable 2FA
                public function verifyAndEnable2FA(Request $request)
                {
                    $validated = $request->validate([
                        'code' => ['required', 'string', 'size:6'],
                    ]);

                    $user = auth()->user();
                    if (!$user) {
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }

                    if (!$user->two_factor_secret) {
                        return response()->json(['error' => '2FA secret not found. Please enable 2FA first.'], 400);
                    }

                    if (!TOTP::verify($user->two_factor_secret, $validated['code'])) {
                        return response()->json([
                            'message' => 'Invalid code'
                        ], 401);
                    }

                    // Enable 2FA
                    $user->two_factor_enabled = true;
                    $user->save();

                    return response()->json([
                        'message' => '2FA enabled successfully',
                        'two_factor_enabled' => true
                    ], 200);
                }

                // Disable 2FA
                public function disable2FA(Request $request)
                {
                    $user = auth()->user();
                    if (!$user) {
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }

                    // Super admin and hotel admins cannot disable 2FA
                    if ($user->role === 'super_admin') {
                        return response()->json(['error' => 'Super admin must have 2FA enabled'], 403);
                    }

                    if ($user->role === 'hotel') {
                        return response()->json(['error' => 'Hotel administrators must have 2FA enabled'], 403);
                    }

                    $validated = $request->validate([
                        'password' => ['required', 'string'],
                    ]);

                    // Verify password before disabling
                    if (!Hash::check($validated['password'], $user->password)) {
                        return response()->json([
                            'message' => 'Invalid password'
                        ], 401);
                    }

                    $user->two_factor_enabled = false;
                    $user->two_factor_secret = null;
                    $user->save();

                    return response()->json([
                        'message' => '2FA disabled successfully',
                        'two_factor_enabled' => false
                    ], 200);
                }

                // Verify 2FA code (for super admin setup)
                public function verify2FA(Request $request)
                {
                    $validated = $request->validate([
                        'code' => ['required', 'string', 'size:6'],
                    ]);

                    $user = auth()->user();
                    if (!$user) {
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }

                    if (!$user->two_factor_secret) {
                        return response()->json(['error' => '2FA not set up'], 400);
                    }

                    if (!TOTP::verify($user->two_factor_secret, $validated['code'])) {
                        return response()->json([
                            'message' => 'Invalid code'
                        ], 401);
                    }

                    return response()->json([
                        'message' => '2FA verified successfully'
                    ], 200);
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
                    $frontendUrl = config('app.frontend_url');
                    $frontendUrl = config('app.frontend_url');
                    $verificationUrl = $frontendUrl . '/verify-email/' . $verificationToken;
                    Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

                    return response()->json([
                        'message' => 'Megerősítő e-mail újraküldve. Kérjük, ellenőrizd az e-mail fiókodat.'
                    ], 200);
                }

                // Request password reset
                public function forgotPassword(Request $request)
                {
                    $validated = $request->validate([
                        'email' => ['required', 'string', 'email'],
                    ]);

                    $user = User::where('email', $validated['email'])->first();

                    // Always return success message for security (don't reveal if email exists)
                    if (!$user) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Ha ez az e-mail cím regisztrálva van, akkor elküldtük a jelszó visszaállítási linket.'
                        ], 200);
                    }

                    // Delete any existing reset tokens for this user
                    DB::table('password_reset_tokens')
                        ->where('user_id', $user->id)
                        ->delete();

                    // Generate new reset token
                    $resetToken = Str::random(64);
                    $expiresAt = now()->addMinutes(60); // Token expires in 60 minutes

                    // Store reset token
                    DB::table('password_reset_tokens')->insert([
                        'user_id' => $user->id,
                        'token' => $resetToken,
                        'expires_at' => $expiresAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Send password reset email
                    // Get frontend URL from config or use a default
                    $frontendUrl = config('app.frontend_url');
                    $resetUrl = $frontendUrl . '/reset-password/' . $resetToken;
                    
                    Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

                    return response()->json([
                        'success' => true,
                        'message' => 'Ha ez az e-mail cím regisztrálva van, akkor elküldtük a jelszó visszaállítási linket. Kérjük, ellenőrizd az e-mail fiókodat.'
                    ], 200);
                }

                // Reset password with token
                public function resetPassword(Request $request)
                {
                    $validated = $request->validate([
                        'token' => ['required', 'string'],
                        'password' => ['required', 'string', 'min:8'],
                    ]);

                    // Find valid reset token
                    $resetToken = DB::table('password_reset_tokens')
                        ->where('token', $validated['token'])
                        ->where('expires_at', '>', now())
                        ->first();

                    if (!$resetToken) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Érvénytelen vagy lejárt jelszó visszaállítási link. Kérjük, kérj új jelszó visszaállítási linket.'
                        ], 400);
                    }

                    // Find user
                    $user = User::find($resetToken->user_id);
                    if (!$user) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Felhasználó nem található.'
                        ], 404);
                    }

                    // Update password
                    $user->password = Hash::make($validated['password']);
                    $user->save();

                    // Delete used reset token
                    DB::table('password_reset_tokens')
                        ->where('token', $validated['token'])
                        ->delete();

                    return response()->json([
                        'success' => true,
                        'message' => 'Jelszavad sikeresen visszaállítva! Most már bejelentkezhetsz az új jelszavaddal.'
                    ], 200);
                }
}
