<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $qrBase64;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;

        // QR generálás base64-ben
        $this->qrBase64 = base64_encode(
            QrCode::format('png')->size(300)->generate($booking->checkInToken)
        );
    }

    public function build()
    {
        return $this->subject('Foglalás visszaigazolás')
                    ->view('emails.booking_confirmation');
    }
}
