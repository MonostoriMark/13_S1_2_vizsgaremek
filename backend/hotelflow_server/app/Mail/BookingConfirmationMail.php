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
    public $qrPath; // ideiglenes fájl elérési útja

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;

        // QR generálás
        $qrResult = Builder::create()
            ->data($booking->checkInToken)
            ->size(300)
            ->build();

        // Ideiglenes mentés a storage/app/public/qr-kodok mappába
        $qrDir = storage_path('app/public/qr-codes');
        if (!file_exists($qrDir)) {
            mkdir($qrDir, 0777, true);
        }

        $this->qrPath = $qrDir . '/qr_' . $booking->id . '.png';
        file_put_contents($this->qrPath, $qrResult->getString());
    }

    public function build()
    {
        return $this->subject('Foglalás visszaigazolás')
                    ->view('emails.booking_confirmation');
    }
}
