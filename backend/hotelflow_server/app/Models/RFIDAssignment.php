<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RFIDAssignment extends Model
{
    protected $table = 'rfid_assignments';
    
    protected $fillable = [
        'rfid_key_id',
        'booking_id',
        'room_id',
        'assigned_at',
        'released_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime'
    ];

    public function rfidKey()
    {
        return $this->belongsTo(RFIDKey::class, 'rfid_key_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function isActive()
    {
        return $this->released_at === null;
    }
}
