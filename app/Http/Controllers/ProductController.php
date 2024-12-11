<?php




namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\Shop;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products for a shop.
     *
     * @return \Illuminate\Http\JsonResponse
     */
//     public function index(Request $request,$shopId)
//     {
//         try {
// // return response()->json(['shopId' => $shopId], 200);

//             $shop = Shop::find(['id'=>$shopId]);
//             // $shop = Shop::find(['id'=>$shopId]);
//             if (!$shop) {
//                 return response()->json(['error' => 'Shop not found'], 404);
//             }
// return response()->json(['shop products' => $shop->products()], 200);
//             $products = $shop->products;
//             return response()->json($products, 200);
//         } catch (Exception $e) {
//             return response()->json(['error' => 'Failed to fetch products.', 'message' => $e->getMessage()], 500);
//         }
//     }


public function index(Request $request, $shopId)
{
    try {
        // Find the shop by ID
        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        // Fetch products related to the shop
        $products = $shop->products;

        return response()->json(['products' => $products], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch products.',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function getAllProducts(Request $request){
    try {
        $products = Product::with(['category', 'brand'])->get(); // Eager load relations
        return response()->json([
           'success' => true,
            'data' => $products
        ]);
    } catch (Exception $e) {
        return response()->json([
           'success' => false,
           'message' => 'Failed to fetch the products.',
            'error' => $e->getMessage()
        ], 500);
    
}
}

    /**
     * Store a newly created product for a shop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $shopId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $shopId)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'product_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // return response()->json(['message' => 'this is just for debbuging' ,'image'=>$request->hasFile('product_image'),'category'=>$request->category_id,'brand'=>$request->brand_id,'shop_id'=> $shopId],200);


            // Handle image upload
            $path = $request->file('product_image')->store('products', 'public');
            $imageUrl = asset('storage/' . $path);

            // Create product
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'image' => $imageUrl,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'shop_id' => $shopId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (Exception $e) {
            // return response()->json(['message' => 'this is just for debbuging in error handling', 'image' => $request->hasFile('product_image'), 'category' => $request->category_id, 'brand' => $request->brand_id], 500);

            return response()->json(['error' => 'Failed to create product.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($productId): JsonResponse
    {

        $product = Product::find($productId);

        try {
            return response()->json([
                'success' => true,
                'data' => $product->load(['category', 'brand']),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch product.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edite(Request $request, $productId, $shopId): JsonResponse
    {
        try {

            $product = Product::find($productId);
            $shop = Shop::find($shopId);

            if (!$product || !$shop) {
                return response()->json(['error' => 'Product or shop not found'], 404);
            }

            // if ($request->user()->id !== $shop->owner || $product->shop_id !== $shopId) {
            if ($request->user()->id !== $shop->owner) {
                return response()->json(['error' => 'Permission denied', 'shop_id' => $shopId, 'product_id' => $productId, 'user_id' => $request->user()->id, 'shop_owner' => $shop->owner], 403);
            }


            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'product_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);


            $imageUrl = $product->image;

            // Handle image upload
            if ($request->hasFile('product_image')) {
                $relativePath = str_replace(asset('storage') . '/', '', $product->image);
                Storage::disk('public')->delete($relativePath);

                $path = $request->file('product_image')->store('products', 'public');
                $imageUrl = asset('storage/' . $path);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'image' => $imageUrl,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'shop_id' => $shopId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update product.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $shopId, $productId)
    {
        try {



            $product = Product::find($productId);
            $shop = Shop::find($shopId);

            if (!$product || !$shop) {
                return response()->json(['error' => 'Product or shop not found'], 404);
            }


            // if ($request->user()->id !== $shop->owner || $product->shop_id !== $shopId) {
                if ($request->user()->id !== $shop->owner) {
                return response()->json(['error' => 'Permission denied'], 403);
            }


            // Delete associated image
            if ($product->image) {
                $relativePath = str_replace(asset('storage') . '/', '', $product->image);
                Storage::disk('public')->delete($relativePath);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete product.', 'message' => $e->getMessage()], 500);
        }
    }
}


