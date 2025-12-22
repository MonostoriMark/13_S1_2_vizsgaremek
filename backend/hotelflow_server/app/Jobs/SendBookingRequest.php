<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use App\Models\PendingHandshake;

class SendBookingRequest implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $hotelId;
    public $endpoint;

    public function __construct($hotelId)
    {
        $this->hotelId = $hotelId;
        $this->endpoint = "https://hotelflow.optikart.hu/{$hotelId}";
    }

    public function handle()
{
    try {
        // 5 mp timeout, ne blokkolja a Jobot
        $response = Http::get($this->endpoint);

        PendingHandshake::updateOrCreate(
            ['hotel_id' => $this->hotelId],
            [
                'endpoint' => $this->endpoint,
                'tries' => \DB::raw('tries + 1'), // növeljük a próbálkozások számát
            ]
        );
        // Ha sikeres, töröljük a pending rekordot (ha volt)
        

    } catch (\Exception $e) {
        // Sikertelen request → tároljuk a pending_handshakes táblába
        PendingHandshake::where('hotel_id', $this->hotelId)->delete();

        // Hibát logoljuk
        \Log::error('Booking request failed', [
            'hotel_id' => $this->hotelId,
            'error' => $e->getMessage(),
        ]);
    }
}

}
