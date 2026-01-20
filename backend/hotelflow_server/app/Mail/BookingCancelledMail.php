<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $reason;

    public function __construct(Booking $booking, string $reason = '')
    {
        $this->booking = $booking;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Foglalás törölve')
            ->view('emails.booking_cancelled')
            ->with([
                'booking' => $this->booking,
                'reason' => $this->reason,
            ]);
    }
}

