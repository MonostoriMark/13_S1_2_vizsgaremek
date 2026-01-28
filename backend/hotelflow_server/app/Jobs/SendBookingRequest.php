<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

class SendBookingRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $hotelId;
    public $endpoint;
    public $tries = 5; // max retry
    public $timeout = 30; // 30 másodperc max futás ideje jobonként

    /**
     * Backoff idők másodpercben: 10s, 30s, 1min, 5min
     */
    public function backoff()
    {
        return [10, 30, 60, 300];
    }

    /**
     * Job konstruktor
     */
    public function __construct($hotelId)
    {
        $this->hotelId = $hotelId;
        $this->endpoint = "https://hotelflow.optikart.hu/{$hotelId}";
    }


    public function uniqueId()
    {
        return (string) $this->hotelId;
    }

    /**
     * A Job fő logikája
     */


public function handle()
{
    $hotelId = $this->hotelId; // a konstruktorból
    $url = "https://hotelflow.optikart.hu/{$hotelId}";

    try {
        $response = Http::timeout(1)
                        ->withoutVerifying()
                        ->get($url);

        if ($response->successful()) {
            Log::info('Async GET success', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } else {
            Log::warning('Async GET returned non-200', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        Log::error('Async HTTP connection failed', [
            'url' => $url,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
    } catch (\Exception $e) {
        Log::error('Async other exception', [
            'url' => $url,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
    }
}

}
