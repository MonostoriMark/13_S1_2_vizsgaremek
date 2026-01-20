<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest extends Model
{
    use HasFactory;
 public $timestamps = false; // mert nincs created_at és updated_at mező
    protected $table = 'guests';
    protected $fillable = ['name', 'idNumber', 'dateOfBirth', 'bookings_id'];

    public function booking() {
        return $this->belongsTo(Booking::class, 'bookings_id');
    }
}
