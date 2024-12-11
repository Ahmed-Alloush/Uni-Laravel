<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $hashedPassword = Hash::make('0940468172mtn');

        return [
            'phonenumber' => '0988795525',
            'password' => $hashedPassword,
            'email' => 'ahmed0940468172mtn@gmail.com',
            'first_name' => 'Ahmed',
            'last_name' => 'Alloush',
            'birthdate' => '2004/07/01',
            'gender' => 'male',
            'role' => 'super admin',
            'location_id' =>'1'
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
