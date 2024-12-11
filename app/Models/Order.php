<?php






namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'total_price', 'location_id'];

    /**
     * Relationship to the User who placed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the products in the order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity') // Include quantity in the pivot table
                    ->withTimestamps();    // Automatically handle timestamps
    }

    /**
     * Relationship to the location associated with the order.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}














// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Order extends Model
// {
//     use HasFactory;

//     protected $fillable = ['user_id', 'status', 'total_price', 'location_id'];

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function products()
//     {
//         return $this->belongsToMany(Product::class, 'order_products')
//                     ->withPivot('quantity', 'price')
//                     ->withTimestamps();
//     }
// }
