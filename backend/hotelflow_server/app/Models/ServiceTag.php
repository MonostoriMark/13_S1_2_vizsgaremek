<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTag extends Model
{
    protected $table = 'serviceTags';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
