<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'first_name',
        'last_name',
        'phonenumber',
        'password',
        'image_profile',
        'location',
        'role_id',
    ];


    public function role()
    {
        return $this->belongsTo(Role::class,'role_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}