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
        return $this->belongsTo(Hotel::class, 'hotels_id');
    }

    // Kapcsolat a foglalásokhoz (many-to-many)
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'bookingsRelation', 'rooms_id', 'booking_id');
    }

    // Kapcsolat a képekhez (many-to-many)
    public function images()
    {
        return $this->belongsToMany(Image::class, 'imagesRelation', 'rooms_id', 'images_id');
    }

    // Kapcsolat a szolgáltatás címkékhez (many-to-many)
    public function serviceTags()
    {
        return $this->belongsToMany(ServiceTag::class, 'roomTagRelation', 'rooms_id', 'serviceTags_id');
    }
    public function tags()
    {
        return $this->belongsToMany(
            ServiceTag::class,
            'roomTagRelation',
            'rooms_id',
            'serviceTags_id'
        );
    }
}
