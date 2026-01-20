<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RFIDConnection extends Model
{
    protected $table = 'rfidKeyConnection';
    protected $fillable = [
        'rfidKeys_id',
        'rooms_id'
    ];
    public $timestamps = false;
}
