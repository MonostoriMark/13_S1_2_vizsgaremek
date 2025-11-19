<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Endroid\QrCode\Builder\Builder;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $qrBase64;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;

        // QR generálás PNG-ben és base64-elve
        $qrResult = Builder::create()
            ->data($booking->checkInToken)
            ->size(300)
            ->build();

        $this->qrBase64 = base64_encode($qrResult->getString());
    }

    public function build()
    {
        return $this->subject('Foglalás visszaigazolás')
                    ->view('emails.booking_confirmation');
    }
}
