<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;
 public $timestamps = false; // mert nincs created_at és updated_at mező
    protected $table = 'bookings';
    protected $fillable = [
        'users_id','hotels_id', 'startDate', 'endDate', 'totalPrice', 'status', 
        'checkInToken', 'checkOutToken'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function rooms() {
        return $this->belongsToMany(Room::class, 'bookingsRelation', 'booking_id', 'rooms_id');
    }

    public function guests() {
        return $this->hasMany(Guest::class, 'bookings_id');
    }

    public function services() {
        return $this->belongsToMany(Service::class, 'servicesRelation', 'bookings_id', 'services_id');
    }
}