<?php

namespace App\Observers;

use App\Models\Booking;
use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Http;

class BookingObserver
{
    
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        
       Http::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
         Http::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
       Http::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
       Http::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
         Http::get('https://bumper-developing-tiffany-dealer.trycloudflare.com');
    }
}
