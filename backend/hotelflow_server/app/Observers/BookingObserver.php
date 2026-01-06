<?php

namespace App\Observers;

use App\Models\Booking;
use App\Jobs\SendBookingRequest;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id)->afterCommit();
    }

    public function updated(Booking $booking): void
    {
      SendBookingRequest::dispatch($booking->hotels_id)->afterCommit();
    }

    public function deleted(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id)->afterCommit();
    }

    public function restored(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id)->afterCommit();
    }

    public function forceDeleted(Booking $booking): void
    {
        SendBookingRequest::dispatch($booking->hotels_id)->afterCommit();
    }
}
