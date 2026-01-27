<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';
    
    public $timestamps = true;
    
    protected $fillable = [
        'hotels_id',
        'name',
        'token',
        'is_active'
    ];

    protected $hidden = [
        'token'
    ];

    // Relationship to Hotel
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotels_id');
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
}
