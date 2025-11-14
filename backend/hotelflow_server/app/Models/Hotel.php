<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = 'hotels';

    public $timestamps = false; // mert nincs created_at és updated_at mező

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'description',
        'type',
        'starRating',
        'createdAt'
    ];

    // Kapcsolat a userhez
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
