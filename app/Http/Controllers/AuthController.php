<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {


        $request->validate([
            'role_id' => 'nullable|integer',
            // 'role_id' => 'integer|exists:roles,id',
            'phonenumber' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
        ]);



        $user = User::create([
            'role_id' => $request->role_id ?? 1,
            'phonenumber' => $request->phonenumber,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }





    public function login(Request $request)
    {
        $request->validate([
            'phonenumber' => 'required|string',
            'password' => 'required|string',
        ]);

        // return response()->json([$request->phonenumber , $request->password]);




        $user = User::where('phonenumber', $request->phonenumber)->first();



        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
