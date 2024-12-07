<?php



namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{



    public function editProfile(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                // 'imageProfile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'first_name' => 'required|string|max:20',
                'last_name' => 'required|string|max:20',
                'country' => 'required|string',
                'city' => 'required|string',
                'streetAddress' => 'required|string',
            ]);

// return response()->json([
//     'HasFile?'=> $request->hasFile('image')
// ],200);

            // Fetch the currently authenticated user
            $user = $request->user()->load('location');
            $imageUrl = $user->image; // Default to existing image path in the database

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if (!empty($user->image)) {
                    try {
                        // Extract the relative path and delete it
                        $relativePath = str_replace(asset('storage') . '/', '', $user->image);
                        Storage::disk('public')->delete($relativePath);
                    } catch (Exception $e) {
                        // Log the error but don't block the update
                        logger()->error('Failed to delete old image: ' . $e->getMessage());
                    }
                }

                // Store the new image
                $path = $request->file('image')->store('users', 'public');
                $imageUrl = asset('storage/' . $path);
            }




            $location = Location::where(['country' => $request->country, 'city' => $request->city, 'street_address' => $request->streetAddress])->first();

            if (empty($location)) {
                $location = Location::create([
                    'country' => $request->country,
                    'city' => $request->city,
                    'street_address' => $request->streetAddress,
                ]);
            }

            // Update the user's profile
            $user->update([
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name' => $request->last_name ?? $user->last_name,
                'location_id' => $location->id,
                'image' => $imageUrl,
                
            ]);

            // Return success response
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 400);
        } catch (Exception $e) {
            // Catch any unexpected exceptions
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the profile.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }





    /**
     * Update the authenticated user's profile.
     */
    // public function editProfile(Request $request)
    // {
    //     // Validate the incoming request
    //     $validated = $request->validate([
    //         'first_name' => 'string|max:255|nullable',
    //         'last_name' => 'string|max:255|nullable',
    //         'location' => 'string|max:255|nullable',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     // Fetch the currently authenticated user
    //     $user = $request->user();
    //     $imageUrl = $user->image_profile_url; // Default to existing image path in the database

    //     // Handle image upload
    //     if ($request->hasFile('image')) {
    //         // Delete the old image if it exists
    //         if (!empty($user->image_profile_url)) {
    //             // Extract the relative path and delete it
    //             $relativePath = str_replace(asset('storage') . '/', '', $user->image_profile_url);
    //             Storage::disk('public')->delete($relativePath);
    //         }

    //         // Store the new image
    //         $path = $request->file('image')->store('users', 'public');

    //         // Generate the full URL for third-party storage (e.g., Cloudinary)
    //         // Example: Replace with your actual third-party URL structure
    //         $imageUrl = asset('storage/' . $path);
    //     }

    //     // Update the user's profile
    //     $user->update([
    //         'first_name' => $request->first_name ?? $user->first_name,
    //         'last_name' => $request->last_name ?? $user->last_name,
    //         'location' => $request->location ?? $user->location,
    //         'image_profile_url' => $imageUrl, // Save the full URL in the database
    //     ]);

    //     // Return success response
    //     return response()->json([
    //         'message' => 'Profile updated successfully',
    //         'user' => $user,
    //         'image_url' => $imageUrl, // Include the full URL in the response
    //     ]);
    // }

    /**
     * Get the authenticated user's profile.
     */
    public function getMe(Request $request)
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Please Login or Register !'], 401);
        }

        // Return the authenticated user's information
        return response()->json([
            'user' => $request->user()->load('location'),
        ], 200);
    }
}




// namespace App\Http\Controllers;

// use App\Models\User;
// use Exception;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class UserController extends Controller
// {


//     public function editProfile(Request $request)
//     {
//         // Validate the incoming request
//         $validated = $request->validate([
//             'first_name' => 'string|max:255',
//             'last_name' => 'string|max:255',
//             'location' => 'string|max:255',
//             'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
//         ]);

//         // Fetch the currently authenticated user
//         $user = $request->user();
        
//         $imageUrl = $user->image_profile_url; // Default to existing full URL

        
        
//         // Handle image upload
//         if ($request->hasFile('image')) {
//             // Delete the old image if it exists
//             if ($user->image_profile !== null) {
//                 // Extract the relative path from the full URL
//                 $relativePath = str_replace(asset('storage') . '/', '', $user->image_profile);
//                 Storage::disk('public')->delete($relativePath);
//             }
            
//             // Store the new image and get its relative path
//             $path = $request->file('image')->store('users', 'public');
//             // Generate the full URL
//             $imageUrl = asset('storage/' . $path);
//         }
        
//         return response()->json(['user' => $user, 'image' => $imageUrl], 200);
     
//         // Update the user's profile
//         $user->update([
//             'first_name' => $request->first_name,
//             'last_name' => $request->last_name,
//             'location' => $request->location,
//             'image_profile' => $imageUrl, // Store the full URL in the database
//         ]);

//         // Return success response
//         return response()->json([
//             'message' => 'Profile updated successfully',
//             'user' => $user,
//         ]);
//     }

//     public function getMe(Request $request)
//     {


//         if (!$request->user()) {
//             return response()->json(['message' => 'Unauthenticated'], 401);
//         }

//         return response()->json([$request->user(),200]);


//         // try {

//         //     return response()->json($request->user());
//         // } catch (Exception $e) {
//         //     return response()->json(['message' => $e->getMessage()], 500);
//         // }
//     }

    // public function editProfile(Request $request)
    // {
    //     // return response()->json([$request->user(),$request->all()],200);
    //     // Validate the incoming request
    //    $validated= $request->validate([
    //         'first_name' => 'nullable|string|max:255',
    //         'last_name' => 'nullable|string|max:255',
    //         'location' => 'nullable|string|max:255',
    //         // 'image_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    // //     // Fetch the currently authenticated user
    //     $user = $request->user();



    //     return response()->json([$user,$validated,'hasFile'=>$request->hasFile('image')],200);

    //     // If an image is uploaded, handle the file storage
    //     if ($request->hasFile('image')) {

    //         if ($user->image_profile_url) {
    //             // Extract the relative path from the URL
    //             $relativePath = str_replace(asset('storage') . '/', '', $user->image_profile_url);
    //             Storage::disk('public')->delete($relativePath);
    //         }

    //         $path = $request->file('image')->store('users', 'public'); // Store image
    //         $imageUrl = asset('storage/' . $path);
    //     }

    //     // Update the user's profile
    //     $user->update([
    //         'first_name' => $request->first_name ?? $user->first_name,
    //         'last_name' => $request->last_name ?? $user->last_name,
    //         'location' => $request->location ?? $user->location,
    //         'image_profile_url' => $imageUrl ?? $user->image_profile_url, // Store the image URL/path in the database
    //     ]);

    //     return response()->json([
    //         'message' => 'Profile updated successfully',
    //         'user' => $user,
    //     ]);
    // }
// }




// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;

// class UserController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function index()
//     {
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function create()
//     {
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function store(Request $request)
//     {
//         //
//     }

//     /**
//      * Display the specified resource.
//      *
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Http\Response
//      */
//     public function show(User $user)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      *
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Http\Response
//      */
//     public function edit(User $user)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Http\Response
//      */
//     public function update(Request $request, User $user)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      *
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Http\Response
//      */
//     public function destroy(User $user)
//     {
//         //
//     }
// }
