<?php

namespace App\Helpers;

class TOTP
{
    /**
     * Generate a TOTP secret
     */
    public static function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * Generate TOTP code from secret
     */
    public static function generateCode(string $secret, int $timeStep = 30): string
    {
        $time = floor(time() / $timeStep);
        return self::generateHOTP($secret, $time);
    }

    /**
     * Verify TOTP code
     */
    public static function verify(string $secret, string $code, int $timeStep = 30, int $window = 1): bool
    {
        $time = floor(time() / $timeStep);
        
        // Check current time step and adjacent steps (for clock skew)
        for ($i = -$window; $i <= $window; $i++) {
            $expectedCode = self::generateHOTP($secret, $time + $i);
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate HOTP (HMAC-based One-Time Password)
     */
    private static function generateHOTP(string $secret, int $counter): string
    {
        $secret = self::base32Decode($secret);
        $counter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $counter, $secret, true);
        
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Base32 decode
     */
    private static function base32Decode(string $input): string
    {
        $input = strtoupper($input);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $val = strpos($chars, $char);
            if ($val === false) continue;
            
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xff);
                $bitsLeft -= 8;
            }
        }
        
        return $output;
    }

    /**
     * Generate QR code data URL for Google Authenticator
     */
    public static function getQRCodeUrl(string $email, string $secret, string $issuer = 'HotelFlow'): string
    {
        $label = urlencode($email);
        $issuerEncoded = urlencode($issuer);
        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuerEncoded}";
    }
}
