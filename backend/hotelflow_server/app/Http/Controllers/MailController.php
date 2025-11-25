<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailController extends Controller
{
    public function sendBookingEmailWithQr(Booking $booking)
{
    // 1) Készítsd el a URL-t / payloadot a QR-nek
    $payload = route('booking.show', ['id' => $booking->id, 'token' => $booking->checkInToken]);

    // 2) Generálj PNG bináris adatot, majd base64-eld
    $pngData = QrCode::format('png')->size(400)->generate($payload);
    $qrBase64 = base64_encode($pngData);

    // 3) Küldd az e-mailt (Mailable-nek átadjuk a base64 stringet)
    Mail::to($booking->user->email)
        ->send(new BookingConfirmation($booking, $qrBase64));

    return response()->json(['sent' => true]);
}
}
