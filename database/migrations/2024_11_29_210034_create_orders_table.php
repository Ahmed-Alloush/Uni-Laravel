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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // Foreign key for the user placing the order
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('location_id');  // Foreign key for the user placing the order
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->string('payment_status')->default('not paid');  // Status of the order
            $table->string('payment_way')->default('cash');  // Status of the order
            $table->decimal('total_price', 10, 2)->default(0);  // Total cost of the order
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
        Schema::dropIfExists('orders');
    }
};
