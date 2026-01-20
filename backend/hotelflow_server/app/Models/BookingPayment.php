<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id',
        'method',
        'status',
        'confirmed_at',
        'confirmed_by_user_id',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}

