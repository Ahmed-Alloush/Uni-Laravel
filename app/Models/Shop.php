<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'owner', 'image'];

    public function owner()
    {
        return $this->belongsTo(User::class,'owner');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}





// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Shop extends Model
// {
//     use HasFactory;


//     protected $fillable = [
//         'name',
//         'image_logo',
//         // 'shop_owner',
//     ];


//     // public function owner()
//     // {
//     //     return $this->belongsTo(User::class);
//     // }

//     public function products()
//     {
//         return $this->hasMany(Product::class);
//     }




    
// }
