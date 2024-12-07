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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phonenumber')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image')->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('role')->default('user');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->timestamps();
        });
    }
    
    // $table->string('first_name');
    // $table->string('last_name');
    // $table->string('location')->nullable();
    // $table->date('birthday')->nullable();
    // $table->enum('gender', ['male', 'female'])->nullable();
    // $table->string('city')->nullable();
    // $table->string('street_address');
    // $table->string('country')->nullable();
    // $table->unsignedBigInteger('role_id')->default(1); // Default to 'user' role
    // $table->foreign('role')->references('id')->on('roles')->onDelete('cascade');
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
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
//         Schema::create('users', function (Blueprint $table) {
//             $table->id();
//             $table->string('first_name');
//             $table->string('last_name');
//             $table->string('image_profile_url');
//             $table->string('location');
//             $table->string('phonenumber')->unique();
//             $table->string('password');
//             $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade')->defaultValue(1);
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
//         Schema::dropIfExists('users');
//     }
// };
