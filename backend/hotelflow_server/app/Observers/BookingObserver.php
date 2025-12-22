<?php

namespace App\Observers;

use App\Models\Booking;
use App\Jobs\SendBookingRequest;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id);
    }

    public function updated(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id);
    }

    public function deleted(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id);
    }

    public function restored(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id);
    }

    public function forceDeleted(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id);
    }
}
