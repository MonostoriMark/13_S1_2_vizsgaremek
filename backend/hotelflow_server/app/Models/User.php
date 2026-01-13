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
        'password',
        'role',
        'isVerified',
        'email_verification_token',
        'email_verified_at',
        'tax_number',
        'bank_account',
        'eu_tax_number',
        'two_factor_secret',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'passwordHash',
        'remember_token',
    ];
    

    public $timestamps = true;
}
