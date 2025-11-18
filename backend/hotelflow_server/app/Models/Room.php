<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    public $timestamps = false; // mert nincs created_at és updated_at mező

    protected $fillable = [
        'hotels_id',
        'name',
        'description',
        'pricePerNight',
        'capacity',
        'basePrice',
        'createdAt'
    ];

    // Kapcsolat a hotelhez
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
