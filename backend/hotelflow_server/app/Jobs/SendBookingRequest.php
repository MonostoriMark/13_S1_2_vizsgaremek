<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use App\Models\PendingHandshake;

class SendBookingRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, Queueable;

    public $hotelId;
    public $endpoint;

    public $tries = 5;

    public function backoff()
    {
        return [10, 30, 60, 300]; // retry 10s, 30s, 1min, 5min
    }

    public function __construct($hotelId)
    {
        $this->hotelId = $hotelId;
        $this->endpoint = "https://hotelflow.optikart.hu/{$hotelId}";
    }

    public function uniqueId()
    {
        return $this->hotelId;
    }

    public function handle()
{
    try {
        // ⚡ async HTTP Guzzle használat Laravel Http Facade alatt
        $promise = Http::withOptions([
            'verify' => false, // SSL kikapcsolva
            'timeout' => 1,
        ])->async()->get($this->endpoint);

        // callback siker / hiba
        $promise->then(
            function ($response) {
                // siker
                PendingHandshake::where('hotel_id', $this->hotelId)->delete();
                \Log::info('Async GET success', [
                    'hotel_id' => $this->hotelId,
                    'status' => $response->status()
                ]);
            },
            function ($exception) {
                // hiba
                PendingHandshake::updateOrCreate(
                    ['hotel_id' => $this->hotelId],
                    [
                        'endpoint' => $this->endpoint,
                        'tries' => \DB::raw('tries + 1'),
                    ]
                );
                \Log::error('Async GET failed', [
                    'hotel_id' => $this->hotelId,
                    'error' => $exception->getMessage()
                ]);
            }
        );

        // trigger a promise végrehajtását
        $promise->wait(); // ez opcionális, csak ha meg akarjuk várni a response-t

    } catch (\Throwable $e) {
        \Log::error('Async handle outer exception', [
            'hotel_id' => $this->hotelId,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
}