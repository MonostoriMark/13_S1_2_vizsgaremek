<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Room;

class Image extends Model
{
    protected $table = 'images';
    protected $fillable = ['url'];

    public $timestamps = false;

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'imagesRelation', 'images_id', 'rooms_id');
    }

}
