<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTag extends Model
{
    protected $table = 'serviceTags';
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
}
