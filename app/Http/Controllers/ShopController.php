<?php



namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class ShopController extends Controller
{
    /**
     * Display a listing of the shops.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $shops = Shop::with('products')->get();
            return response()->json($shops);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong while fetching shops.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created shop in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {
            // Validate request
            $request->validate([
                'name' => 'required|string|max:255',
                'image_logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // return response()->json(['message'=>'this is just for debbuging' ,'image'=>$request->hasFile('image_logo')],200);




            $path = $request->file('image_logo')->store('shops', 'public');
            $imageUrl = asset('storage/' . $path);


            // Create shop
            $shop = Shop::create([
                'name' => $request->name,
                'owner' => $request->user()->id,
                'image' => $imageUrl,
            ]);

            return response()->json(['message' => 'Shop created successfully', 'shop' => $shop], 201);
        } catch (Exception $e) {

            return response()->json(['message' => 'this is just for debbuging', 'image' => $request->hasFile('image_logo')], 200);


            return response()->json(['error' => 'Failed to create shop.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified shop along with its products.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $shop = Shop::find($id);

            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            return response()->json($shop->load(['owner', 'products']), 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch shop.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified shop in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $shop = Shop::find($id);
            $imageUrl = $shop->image;
            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            if ($request->user()->id !== $shop->owner) {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            // Validation can be added here if needed
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'image_logo' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);






            // Handle image upload
            if ($request->hasFile('image_logo')) {
                // Delete the old image if it exists
                if (!empty($shop->image)) {
                    // Extract the relative path and delete it
                    $relativePath = str_replace(asset('storage') . '/', '', $shop->image);
                    Storage::disk('public')->delete($relativePath);
                }
                // Store the new image
                $path = $request->file('image_logo')->store('shops', 'public');

                // Generate the full URL for third-party storage (e.g., Cloudinary)
                // Example: Replace with your actual third-party URL structure
                $imageUrl = asset('storage/' . $path);
            }






            $shop->update([
                'name' => $request->name,
                'image' => $imageUrl
            ]);

            return response()->json(['message' => 'Shop updated successfully', 'shop' => $shop], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update shop.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified shop from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request,  $id)
    {
        try {

            $shop = Shop::find($id);

            if (!$shop) {
                return response()->json(['message' => 'Shop not found'], 404);
            }

            if ($request->user()->id !== $shop->owner && $request->user()->role !== 'super admin') {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            // Delete image if exists
            if ($shop->image) {
                $relativePath = str_replace(asset('storage') . '/', '', $shop->image);
                Storage::disk('public')->delete($relativePath);
            }

            $shop->delete();

            return response()->json(['message' => 'Shop deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete shop.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * List products for the specified shop.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Shop $shop)
    {
        try {
            $products = $shop->products;
            return response()->json($products, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch products.', 'message' => $e->getMessage()], 500);
        }
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
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'available_numbers' => 'required|integer|min:0',
                'image_url' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'category' => 'required|exists:categories,id',
                'brand' => 'required|exists:brands,id',
            ]);

            $imagePath = $request->file('image_url')->store('products', 'public');
            $imageUrl = asset('storage/' . $imagePath);

            $product = $shop->products()->create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'available_numbers' => $request->available_numbers,
                'image' => $imageUrl,
                'category' => $request->category_id,
                'brand' => $request->brand_id,
            ]);

            return response()->json(['message' => 'Product added to shop successfully', 'product' => $product], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to add product.', 'message' => $e->getMessage()], 500);
        }
    }
}
