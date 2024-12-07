<?php




use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->double('price', 8, 2);  // Fixed precision for price (8 digits, 2 decimals)
            $table->integer('available_numbers');
            $table->string('image');
            
            // Add the missing columns for category_id and brand_id
            $table->unsignedBigInteger('category_id');  // This creates the category_id column
            $table->unsignedBigInteger('brand_id');     // This creates the brand_id column
            $table->unsignedBigInteger('shop_id');     // This creates the shop id column
            

            // Add the foreign key constraints after the columns are defined
            $table->foreign('category_id')->references('id')->on('categories')->OnDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->OnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->OnDelete();
            


            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};



// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      *
//      * @return void
//      */
//     public function up()
//     {
//         Schema::create('product', function (Blueprint $table) {
//             $table->id();
//             $table->string('name');
//             $table->string('description');
//             $table->double('price', 2);
//             $table->integer('available_numbers');
//             $table->string('image_url');
//             $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
//             $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      *
//      * @return void
//      */
//     public function down()
//     {
//         Schema::dropIfExists('product');
//     }
// };
