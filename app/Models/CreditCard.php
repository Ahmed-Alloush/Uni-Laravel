<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'card_number','user_id', 'expiration_date', 'ccv', 'address_id'];
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    
}
