<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function index()
    {
        $products = Product::with(['category', 'brand'])->get(); // Eager load relations
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CreateProductRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */


     public function store(CreateProductRequest $request)
{

    // return response()->json([
    //     'success' => true,
    //     'message' => 'this is a dummy message create',
    //     'validated' => [$request->validated(), 'image'=> $request->hasFile('image')]
    // ]);


    $validated = $request->validated();

    // Handle image upload
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('products', 'public'); // Store image
        $validated['image_url'] = asset('storage/' . $path); // Save path to database
         

    }

    $product = Product::create($validated);

    // Modify response to include the accessible URL
    // $product->image_url = asset('storage/' . $product->image_url);

    return response()->json([
        'success' => true,
        'message' => 'Product created successfully',
        'data' => $product
    ], 201);
}


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'brand'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function edite(UpdateProductRequest $request,$id): JsonResponse
    {
        $product=Product::findOrFail($id);
        // return response()->json([
        //    'success' => true,
        //    'message' => 'this is a dummay update product',
        //     'validated' => [$product,'image'=> $request->hasFile('image')],
        // ]);

        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
         
                // Extract the relative path from the URL
                $relativePath = str_replace(asset('storage') . '/', '', $product->image_url);
                Storage::disk('public')->delete($relativePath);
            
            $path = $request->file('image')->store('products', 'public'); // Store image

            $validated['image_url'] = asset('storage/' . $path);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */


     public function destroy(Product $product)
{
    // Delete the associated image if exists
    if ($product->image_url) {
        // Extract the relative path from the URL
        $relativePath = str_replace(asset('storage') . '/', '', $product->image_url);
        Storage::disk('public')->delete($relativePath);
    }

    $product->delete();

    return response()->json([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
}

}
