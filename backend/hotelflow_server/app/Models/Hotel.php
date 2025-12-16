<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = 'hotels';
    public $timestamps = false; // mert nincs created_at / updated_at

    protected $fillable = [
        'user_id',
        'location',
        'description',
        'type',
        'starRating',
        'createdAt'
    ];

    // Kapcsolat a userhez (host)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Kapcsolat a szobákhoz
    public function rooms()
    {
        return $this->hasMany(Room::class, 'hotels_id');
    }

    // Kapcsolat a szolgáltatásokhoz
    public function services()
    {
        return $this->hasMany(Service::class, 'hotels_id');
    }
    public function tags()
    {
        return $this->belongsToMany(
            ServiceTag::class,
            'hotelTagRelation',
            'hotels_id',
            'serviceTags_id'
        );
    }
}
