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
        'email',
        'image',
        'location_id',
        'role',
        'gender',
        'birthdate'
    ];


    public function creditCard()
    {
        return $this->hasOne(CreditCard::class,'user_id');
    }

    public function shop()
    {
        // return $this->hasOne(Shop::class, 'owner');
        return $this->hasOne(Shop::class);
    }

    public function location()
    {
        // return $this->hasOne(Shop::class, 'owner');
        return $this->belongsTo(Location::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
