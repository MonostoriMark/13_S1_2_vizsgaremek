<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
     public $timestamps = false; // mert nincs created_at és updated_at mező
    protected $fillable = ['name', 'description', 'price', 'hotels_id'];

    public function bookings() {
        return $this->belongsToMany(Booking::class, 'servicesRelation', 'services_id', 'bookings_id');
    }
}
