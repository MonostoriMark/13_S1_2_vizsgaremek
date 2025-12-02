<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RFIDKey extends Model
{
    protected $table = 'rfidKeys';
    protected $fillable = [
        'hotels_id',
        'isUsed',
        'rfidKey'
    ];
    public $timestamps = false;
}
