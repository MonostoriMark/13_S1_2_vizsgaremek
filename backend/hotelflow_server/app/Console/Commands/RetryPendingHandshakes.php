<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingHandshake;
use App\Jobs\SendBookingRequest;

class RetryPendingHandshakes extends Command
{
    protected $signature = 'handshake:retry';
    protected $description = 'Retry pending handshake requests';

    public function handle()
    {
        $pending = PendingHandshake::all();
        if ($pending->isEmpty()) {
            $this->info('No pending handshakes.');
            return;
        }

        foreach ($pending as $ph) {
            SendBookingRequest::dispatch($ph->hotel_id, $ph->endpoint);
        }

        $this->info('Dispatched '.count($pending).' handshake jobs.');
    }
}
