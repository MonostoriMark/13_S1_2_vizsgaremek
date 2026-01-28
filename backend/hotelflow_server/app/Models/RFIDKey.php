<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RFIDKey extends Model
{
    protected $table = 'rfidKeys';
    
    protected $fillable = [
        'hotels_id',
        'isUsed',
        'rfidKey',
        'name',
        'type'
    ];

    public $timestamps = false;

    protected $casts = [
        'isUsed' => 'boolean'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotels_id');
    }

    public function assignments()
    {
        return $this->hasMany(RFIDAssignment::class, 'rfid_key_id');
    }

    public function activeAssignment()
    {
        return $this->hasOne(RFIDAssignment::class, 'rfid_key_id')
                    ->whereNull('released_at');
    }

    public function isAvailable()
    {
        return !$this->isUsed;
    }

    public function canBeAssigned()
    {
        return !$this->isUsed && $this->activeAssignment === null;
    }

    // Helper method to get status based on isUsed
    public function getStatusAttribute()
    {
        if ($this->isUsed) {
            return 'assigned';
        }
        return 'available';
    }
}
