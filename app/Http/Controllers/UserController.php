<?php




namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{


    public function editProfile(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'location' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Fetch the currently authenticated user
        $user = $request->user();
        $imageUrl = $user->image_profile; // Default to existing full URL
    
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($user->image_profile) {
                // Extract the relative path from the full URL
                $relativePath = str_replace(asset('storage') . '/', '', $user->image_profile);
                Storage::disk('public')->delete($relativePath);
            }
    
            // Store the new image and get its relative path
            $path = $request->file('image')->store('users', 'public');
            // Generate the full URL
            $imageUrl = asset('storage/' . $path);

        }
    
        // Update the user's profile
        $user->update([
            'first_name' => $request->first_name ,
            'last_name' => $request->last_name ,
            'location' => $request->location,
            'image_profile' => $imageUrl, // Store the full URL in the database
        ]);
    
        // Return success response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
    

  
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
}




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
