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
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('card_number', 16)->encrypted();
            $table->date('expiration_date');
            $table->string('ccv', 3)->encrypted();
            $table->decimal('balance', 15, 2)->default(2000.00); // Store card balance with precision
            $table->unsignedBigInteger('address_id');  // Foreign key for the user placing the order
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();  // Foreign key for the user placing the order
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('credit_cards');
    }
};
