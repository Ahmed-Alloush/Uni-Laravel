<?php



namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated =   $request->validate([
                'role' => 'nullable|string|in:user,admin,super admin',
                'phonenumber' => 'required|string|unique:users',
                'password' => 'required|string|min:8',
                'email' => 'required|email|string|unique:users',
                'first_name' => 'required|string|max:20',
                'last_name' => 'required|string|max:20',
                'gender' => 'required|string',
                'birthDate' => 'date|string',
                'country' => 'required|string',
                'city' => 'required|string',
                'streetAddress' => 'required|string',
            ]);


            
            
            $location = Location::where(['country' => $request->country, 'city' => $request->city, 'street_address' => $request->streetAddress])->first();
            if (!$location) {


                $location = Location::create([
                    'country' => $request->country,
                    'city' => $request->city,
                    'street_address' => $request->streetAddress,
                ]);
            }
            $hashedPassword = Hash::make($request->password);

            // Create user
            $user = User::create([
                'role' => $request->role ?? 'user',
                'phonenumber' => $request->phonenumber,
                'password' => $hashedPassword,
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'birthdate' => $request->birthDate,
                'location_id' => $location->id,
            ])->load('location');

            // echo $hashedPassword;
            return $this->generateAuthResponse($user);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during registration.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'phonenumber' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('phonenumber', $request->phonenumber)->first()->load('location');

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Something went wrong! Please try again.'], 401);
            }

            return $this->generateAuthResponse($user);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during login.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    private function generateAuthResponse($user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 200);
    }
}

            // return response()->json([
            //     'data'=> $validated
            //     ]);
            // "street_address"
            // 'image_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Handle image upload
            // $imageUrl = null;
            // if ($request->hasFile('image_profile')) {
            //     $path = $request->file('image_profile')->store('users', 'public');
            //     $imageUrl = asset('storage/' . $path);
            // }