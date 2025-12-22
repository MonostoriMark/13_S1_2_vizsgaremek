<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class SendBookingRequest implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $hotelId;

    public function __construct($hotelId)
    {
        $this->hotelId = $hotelId;
    }

    public function handle()
    {
        try {
            // hosszabb timeout a lassú endpoint miatt
            $response = Http::timeout(1)->get("https://hotelflow.optikart.hu/{$this->hotelId}");
        } catch (\Exception $e) {
            \Log::error('Booking request failed', [
                'hotel_id' => $this->hotelId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
