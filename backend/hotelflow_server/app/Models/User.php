<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'passwordHash',
        'role',
        'createdAt'
    ];

    protected $hidden = [
        'passwordHash',
        'remember_token',
    ];

    public $timestamps = false;
}
