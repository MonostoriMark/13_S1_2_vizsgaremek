<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingHandshake extends Model
{
    protected $table = 'pending_handshakes';
    protected $fillable = [
        'hotel_id',
        'endpoint',
        'tries',
    ];
     public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
