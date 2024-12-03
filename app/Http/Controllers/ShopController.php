<?php



namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Display a listing of the shops.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $shops = Shop::with(['owner', 'products'])->get();
        return response()->json($shops, 200);
    }

    /**
     * Store a newly created shop in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image_logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload the logo
        $path = $request->file('image_logo')->store('shops', 'public');
        $imageUrl = asset('storage/' . $path);

        // Create the shop
        $shop = Shop::create([
            'name' => $request->name,
            'shop_owner' => $request->user()->id,
            'image_logo' => $imageUrl,
        ]);

        return response()->json(['message' => 'Shop created successfully', 'shop' => $shop], 201);
    }

    /**
     * Display the specified shop along with its products.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Shop $shop)
    {


        // return response()->json($shop->load([ 'products']), 200);
        return response()->json(['user_id'=>$request->user()], 200);
 
        // return response()->json($shop->load(['owner', 'products']), 200);
    }

    /**
     * Update the specified shop in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Shop $shop)
    {


        if ($request->user()->id !== $shop->shop_owner) {
            return response()->json(['message' => 'You don\'t have permission to use this resource.'], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'image_logo' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imageUrl = $shop->image_logo;

        if ($request->hasFile('image_logo')) {
            if ($shop->image_logo) {
                $relativePath = str_replace(asset('storage') . '/', '', $shop->image_logo);
                Storage::disk('public')->delete($relativePath);
            }

            $path = $request->file('image_logo')->store('shops', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $shop->update([
            'name' => $request->name ,
            'image_logo' => $imageUrl,
        ]);

        return response()->json(['message' => 'Shop updated successfully', 'shop' => $shop], 200);
    }

    /**
     * Remove the specified shop from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Shop $shop)
    {

        if ($request->user()->id !== $shop->shop_owner && $request->user()->role->name !== 'super admin') {
            return response()->json(['message' => 'You don\'t have permission to use this resource.'], 403);
        }

        if ($shop->image_logo) {
            $relativePath = str_replace(asset('storage') . '/', '', $shop->image_logo);
            Storage::disk('public')->delete($relativePath);
        }

        $shop->delete();

        return response()->json(['message' => 'Shop deleted successfully'], 200);
    }

    /**
     * List products for the specified shop.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Shop $shop)
    {
        $products = $shop->products;
        return response()->json($products, 200);
    }

    /**
     * Add a product to the specified shop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProduct(Request $request, Shop $shop)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'available_numbers' => 'required|integer|min:0',
            'image_url' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $imagePath = $request->file('image_url')->store('product_images', 'public');
        $imageUrl = asset('storage/' . $imagePath);

        $product = $shop->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'available_numbers' => $request->available_numbers,
            'image_url' => $imageUrl,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
        ]);

        return response()->json(['message' => 'Product added to shop successfully', 'product' => $product], 201);
    }
}







// namespace App\Http\Controllers;

// use App\Models\Shop;
// use App\Models\Product;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class ShopController extends Controller
// {
//     /**
//      * Display a listing of the shops.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function index()
//     {
//         // Fetch all shops with their owners and products
//         $shops = Shop::with(['owner', 'products'])->get();
//         return response()->json($shops, 200);
//     }

//     /**
//      * Store a newly created shop in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function store(Request $request)
//     {
//         // Validate the request
//         $request->validate([
//             'name' => 'required|string|max:255',
//             // 'shop_owner' => 'required|exists:users,id', // Ensure the owner exists
//             'image_logo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validate the shop logo
//         ]);

//         // Upload the logo
//         $path = $request->file('image_logo')->store('shops', 'public');

//         $imageUrl = asset('storage/' . $path);


//         // Create the shop
//         $shop = Shop::create([
//             'name' => $request->name,
//             'shop_owner' => $request->user()->id,
//             'image_logo' => $imageUrl,
//         ]);

//         return response()->json(['message' => 'Shop created successfully', 'shop' => $shop], 201);
//     }

//     /**
//      * Display the specified shop along with its products.
//      *
//      * @param  \App\Models\Shop  $shop
//      * @return \Illuminate\Http\Response
//      */
//     public function show(Shop $shop)
//     {
//         // Return the shop with its owner and products
//         return response()->json($shop->load(['owner', 'products']), 200);
//     }

//     /**
//      * Update the specified shop in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\Shop  $shop
//      * @return \Illuminate\Http\Response
//      */
//     public function update(Request $request, Shop $shop)
//     {

//         if ($request->user()->id == $shop->owner) {
//             return response()->json(['message' => 'You don\'t have permmiosn to use this resource.'], 403);
//         }



//         $request->validate([
//             'name' => 'string|max:255',
//             // 'shop_owner' => 'exists:users,id',
//             'image_logo' => 'image|mimes:jpeg,png,jpg|max:2048',
//         ]);

//         $imageUrl = $shop->image_logo;

//         if ($request->hasFile('image_logo')) {
//             // Delete the old image if it exists
//             if ($shop->image_logo) {
//                 // Extract the relative path from the full URL
//                 $relativePath = str_replace(asset('storage') . '/', '', $shop->image_logo);
//                 Storage::disk('public')->delete($relativePath);
//             }

//             // Store the new image and get its relative path
//             $path = $request->file('image_logo')->store('shops', 'public');
//             // Generate the full URL
//             $imageUrl = asset('storage/' . $path);
//         }

//         // Update other fields
//         $shop->update([
//             'name' => $request->name,
//             'image_logo' => $imageUrl,
//         ]);

//         return response()->json(['message' => 'Shop updated successfully', 'shop' => $shop], 200);
//     }

//     /**
//      * Remove the specified shop from storage.
//      *
//      * @param  \App\Models\Shop  $shop
//      * @return \Illuminate\Http\Response
//      */
//     public function destroy(Request $request, Shop $shop)
//     {

//         if (!($request->user()->id == $shop->owner() || $request->user()->role->name == 'super admin')) {

//             return response()->json(['message' => 'You don\'t have permmiosn to use this resource.'], 403);
//         }

//         if ($shop->image_logo) {
//             // Extract the relative path from the full URL
//             $relativePath = str_replace(asset('storage') . '/', '', $shop->image_logo);
//             Storage::disk('public')->delete($relativePath);
//         }

//         $shop->delete();

//         return response()->json(['message' => 'Shop deleted successfully'], 200);
//     }

//     /**
//      * List products for the specified shop.
//      *
//      * @param  \App\Models\Shop  $shop
//      * @return \Illuminate\Http\Response
//      */
//     public function products(Shop $shop)
//     {
//         $products = $shop->products; // Use the relationship defined in the Shop model
//         return response()->json($products, 200);
//     }

//     /**
//      * Add a product to the specified shop.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\Shop  $shop
//      * @return \Illuminate\Http\Response
//      */
//     public function addProduct(Request $request, Shop $shop)
//     {
//         // Validate the product data
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'description' => 'required|string',
//             'price' => 'required|numeric|min:0',
//             'available_numbers' => 'required|integer|min:0',
//             'image_url' => 'required|image|mimes:jpeg,png,jpg|max:2048',
//             'category_id' => 'required|exists:categories,id',
//             'brand_id' => 'required|exists:brands,id',
//         ]);

//         // Upload the product image
//         $imagePath = $request->file('image_url')->store('product_images', 'public');

//         // Create the product and associate it with the shop
//         $product = $shop->products()->create([
//             'name' => $request->name,
//             'description' => $request->description,
//             'price' => $request->price,
//             'available_numbers' => $request->available_numbers,
//             'image_url' => $imagePath,
//             'category_id' => $request->category_id,
//             'brand_id' => $request->brand_id,
//         ]);

//         return response()->json(['message' => 'Product added to shop successfully', 'product' => $product], 201);
//     }
// }
