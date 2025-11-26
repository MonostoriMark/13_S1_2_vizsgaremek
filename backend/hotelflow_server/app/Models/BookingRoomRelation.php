<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoomRelation extends Model
{
    protected $table = 'bookingsRelation';
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'rooms_id',
    ];
}
