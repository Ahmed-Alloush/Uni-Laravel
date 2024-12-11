<?php


namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{



    public function getUserOrders(Request $request)
    {
        // Fetch all orders for the authenticated user along with their products
        $orders = Order::where('user_id', $request->user()->id)
            ->with('products') // Eager load products to minimize queries
            ->get(); // Fetch all orders (not just the first one)

        if ($orders->isEmpty()) {
            return response()->json(['status' => 'failed', 'message' => 'You don\'t have any orders.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $orders], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array', // Ensure products is an array
            'products.*.id' => 'required|exists:products,id', // Validate each product id
            'products.*.quantity' => 'required|numeric|min:1', // Validate each product quantity
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'streetAddress' => 'nullable|string',
        ]);

        $location = null;

        // Check if the address is provided or use the user's existing location
        if (is_null($request->country) && is_null($request->city) && is_null($request->streetAddress)) {
            $location = $request->user()->location; // Assuming the user has a `location` relationship
        } else {
            $location = Location::where([
                'country' => $request->country,
                'city' => $request->city,
                'street_address' => $request->streetAddress,
            ])->first();

            // If location does not exist, create it
            if (empty($location)) {
                $location = Location::create([
                    'country' => $request->country,
                    'city' => $request->city,
                    'street_address' => $request->streetAddress,
                ]);
            }
        }

        // Initialize total price
        $totalPrice = 0;

        // Create the order
        $order = Order::create([
            'user_id' => $request->user()->id,
            'location_id' => $location->id,
            'status' => 'pending',
            'total_price' => $totalPrice, // Will update later
        ]);

        // Process each product in the order
        foreach ($request->products as $product) {
            $productModel = Product::findOrFail($product['id']);

            // Update product stock if enough quantity is available
            if ($productModel->quantity >= $product['quantity']) {
                $productModel->update([
                    'quantity' => $productModel->quantity - $product['quantity'],
                ]);
            } else {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $productModel->name,
                ], 400);
            }

            // Create a record in the pivot table (OrderProduct)
            OrderProduct::create([
                'product_id' => $product['id'],
                'order_id' => $order->id,
                'quantity' => $product['quantity'],
            ]);

            // Update total price
            $totalPrice += $productModel->price * $product['quantity'];
        }

        // Update the total price in the order
        $order->update(['total_price' => $totalPrice]);

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order,
        ]);
    }


    public function update(Request $request, $orderId)
    {
        // Fetch the order along with its products and ensure it belongs to the authenticated user
        $order = Order::where([
            'id' => $orderId,
            'user_id' => $request->user()->id,
        ])->with('products')->first();

        if (!$order) {
            return response()->json(['status' => 'failed', 'message' => 'Order not found or you do not have permission to update it.'], 404);
        }

        // Check if the order was created more than an hour ago
        $orderCreatedAt = Carbon::parse($order->created_at);
        $currentTime = Carbon::now();

        if ($orderCreatedAt->diffInMinutes($currentTime) >= 60) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You can only update your order one hour after it was created.',
            ], 403);
        }

        // Validate incoming data
        $request->validate([
            'products' => 'required|array', // Ensure products is an array
            'products.*.id' => 'required|exists:products,id', // Validate each product ID
            'products.*.quantity' => 'required|numeric|min:1', // Validate each product quantity
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'streetAddress' => 'nullable|string',
        ]);

        // Handle location update
        $location = $order->location; // Assuming an `Order` has a `location` relationship
        if ($request->filled(['country', 'city', 'streetAddress'])) {
            $location = Location::firstOrCreate(
                [
                    'country' => $request->country,
                    'city' => $request->city,
                    'street_address' => $request->streetAddress,
                ]
            );
            $order->update(['location_id' => $location->id]);
        }

        // Update products in the order
        $totalPrice = 0;
        $existingProducts = $order->products->keyBy('id'); // Map existing products by ID for easy lookup

        foreach ($request->products as $productData) {

            $productId = $productData['id'];
            $newQuantity = $productData['quantity'];

            // Check if the product exists in the order
            if ($existingProducts->has($productId)) {
                $existingProduct = $existingProducts[$productId];
                $currentQuantity = $existingProduct->pivot->quantity;

                // If the quantity has changed, update it
                if ($currentQuantity != $newQuantity) {
                    $productModel = Product::findOrFail($productId);
                    $totalQuantity = $productModel->quantity + $currentQuantity;

                    if (!($totalQuantity >= $newQuantity)) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Insufficient stock for product: ' . $productModel->name,
                        ], 400);
                    }

                    // Adjust stock (return previous quantity to stock, then subtract new quantity)
                    $productModel->update([
                        'quantity' => $totalQuantity - $newQuantity,
                    ]);

                    // Update the pivot table
                    $order->products()->updateExistingPivot($productId, ['quantity' => $newQuantity]);
                }

                // Update total price
                $totalPrice += $existingProduct->price * $newQuantity;
            } else {
                // If the product is new, attach it
                $productModel = Product::findOrFail($productId);

                // Check stock availability
                if ($productModel->quantity < $newQuantity) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Insufficient stock for product: ' . $productModel->name,
                    ], 400);
                }

                // Deduct stock
                $productModel->update([
                    'quantity' => $productModel->quantity - $newQuantity,
                ]);

                // Attach the new product to the order
                $order->products()->attach($productId, ['quantity' => $newQuantity]);

                // Update total price
                $totalPrice += $productModel->price * $newQuantity;
            }
        }

        // Remove products from the order if they are no longer in the updated list
        $updatedProductIds = collect($request->products)->pluck('id')->toArray();
        $productsToRemove = $existingProducts->keys()->diff($updatedProductIds);

        foreach ($productsToRemove as $productId) {
            $productModel = Product::findOrFail($productId);
            $removedQuantity = $existingProducts[$productId]->pivot->quantity;

            // Return stock to the product
            $productModel->update([
                'quantity' => $productModel->quantity + $removedQuantity,
            ]);

            // Detach the product from the order
            $order->products()->detach($productId);
        }

        // Update the total price of the order
        $order->update(['total_price' => $totalPrice]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully.',
            'order' => $order->load('products'),
        ]);
    }

    public function destroy(Request $request, $orderId)
    {
        // Find the order and ensure it belongs to the authenticated user
        $order = Order::where([
            'id' => $orderId,
            'user_id' => $request->user()->id,
        ])->with('products')->first();
    
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found or you do not have permission to delete it.',
            ], 404);
        }
    
        // Check if the order is deletable (e.g., status is pending)
        if ($order->status !== 'pending') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only pending orders can be deleted.',
            ], 403);
        }
    
        // Restore stock for all products in the order
        foreach ($order->products as $product) {
            $productModel = Product::find($product->id);
    
            if ($productModel) {
                $restoredQuantity = $product->pivot->quantity;
                $productModel->update([
                    'quantity' => $productModel->quantity + $restoredQuantity,
                ]);
            }
        }
    
        // Detach all products from the order
        $order->products()->detach();
    
        // Delete the order
        $order->delete();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully.',
        ]);
    }
    
}













































































    // public function getUserOrder(Request $request)
    // {
    //     // Fetch orders for the authenticated user

    //     // return response()->json(['status' => 'success', 'data' => $request->user()->id], 200);


    //     $orders = Order::where( ['user_id' => $request->user()->id] )->first()->load('products');

    //     if (!$orders) {
    //         return response()->json(['status' => 'falid', 'message' => 'You don\'t any order.'], 404);
    //     }

    //     return response()->json(['status' => 'success', 'data' => $orders], 200);
    // }



    // public function update(Request $request, $orderId)
    // {
        
    //     $order = Order::where([
        //         'id' => $orderId,
        //         'user_id' => $request->user()->id,
        //     ])->first()->load('products'); // Fetch the order
        
        

    //     $orderCreatedAt = Carbon::parse($order->created_at); // Parse the created_at timestamp
    //     $currentTime = Carbon::now(); // Get the current time




    //     if ($orderCreatedAt->diffInMinutes($currentTime) > 60) {
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'You can only update your order one hour after it was ordered.',
    //         ], 403);
    //     }





    //     $request->validate([
    //         'products' => 'required|array', // Ensure products is an array
    //         'products.*.id' => 'required|exists:products,id', // Validate each product id
    //         'products.*.quantity' => 'required|numeric|min:1', // Validate each product quantity
    //         'country' => 'nullable|string',
    //         'city' => 'nullable|string',
    //         'streetAddress' => 'nullable|string',
    //     ]);



    //     $location = null;

    //     // Check if the address is provided or use the user's existing location
    //     if (is_null($request->country) && is_null($request->city) && is_null($request->streetAddress)) {
    //         $location = $request->user()->location; // Assuming the user has a `location` relationship
    //     } else {
    //         $location = Location::where([
    //             'country' => $request->country,
    //             'city' => $request->city,
    //             'street_address' => $request->streetAddress,
    //         ])->first();

    //         // If location does not exist, create it
    //         if (empty($location)) {
    //             $location = Location::create([
    //                 'country' => $request->country,
    //                 'city' => $request->city,
    //                 'street_address' => $request->streetAddress,
    //             ]);
    //         }
    //     }

    //     // Initialize total price
    //     $totalPrice = 0;

    //     // Create the order


    //     // Process each product in the order
    //     foreach ($request->products as $product) {

    //         $productModel = Product::findOrFail($product['id']);

    //         // Update product stock if enough quantity is available
    //         if ($productModel->quantity >= $product['quantity']) {
    //             $productModel->update([
    //                 'quantity' => $productModel->quantity - $product['quantity'],
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'message' => 'Insufficient stock for product: ' . $productModel->name,
    //             ], 400);
    //         }

    //         // Create a record in the pivot table (OrderProduct)
    //         OrderProduct::create([
    //             'product_id' => $product['id'],
    //             'order_id' => $order->id,
    //             'quantity' => $product['quantity'],
    //         ]);

    //         // Update total price
    //         $totalPrice += $productModel->price * $product['quantity'];
    //     }

    //     // Update the total price in the order
    //     $order->update(['total_price' => $totalPrice]);

    //     return response()->json([
    //         'message' => 'Order placed successfully',
    //         'order' => $order,
    //     ]);
    // }

    //     public function store(Request $request)
    //     {
    //         $request->validate([
    //             'products' => 'required|array', // Ensure products is an array
    //             'products.*.id' => 'required|exists:products,id', // Validate each product id
    //             'products.*.quantity' => 'required|numeric|min:1', // Validate each product quantity
    //             'country' => 'nullable|string',
    //             'city' => 'nullable|string',
    //             'streetAddress' => 'nullable|string',
    //         ]);

    //         // return response()->json(['status'=> 'success','data'=> $request->products], 200);


    //         $location = null;

    //         if ($request->country == null && $request->city == null  && $request->streetAddress == null) {
    //           $this->$location = $request->user()->location;
    //         }

    //         // return response()->json(['status' => 'success', 'data' => [$request->products,$request->country,$request->city,$request->streetAddress,'location'=> $request->user()->location->id]], 200);



    //         $this->$location = Location::where(['country' => $request->country, 'city' => $request->city, 'street_address' => $request->streetAddress])->first();



    //         if (empty($this->$location)) {
    //             $location = Location::create([
    //                 'country' => $request->country,
    //                 'city' => $request->city,
    //                 'street_address' => $request->streetAddress,
    //             ]);
    //         }

    //         $totalPrice = 0;

    //         $order = Order::create([
    //             'user_id' => $request->user()->id,
    //             'location_id' => $location->id,
    //             'status' => 'pending',
    //             'total_price' => $totalPrice
    //         ]);


    //         // Calculate total amount and validate products
    //         foreach ($request->products as $product) {

    //             $orderProduct = OrderProduct::create([
    //                 'product_id' => $product->id,
    //                 'order_id' => $order->id,
    //                 'quantity' => $product->quantity
    //             ]);

    //             $productModel = Product::findOrFail($product['id']);

    //             $totalPrice += $productModel->price * $product['quantity'];

    //             if ($productModel->quantity > $product->quantity) {
    //                 $productModel::update([
    //                     'quantity' => ($productModel->quantity - $product->quantity)
    //                 ]);
    //             }
    //         }

    //         return response()->json(['message' => 'Order placed successfully', 'order' => $order->load('products')]);
    //     }

























































































































// namespace App\Http\Controllers;

// use App\Models\Order;
// use App\Models\Product;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class OrderController extends Controller
// {
//     public function index()
//     {
//         // Fetch orders for the authenticated user
//         return response()->json(Auth::user()->orders);
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'products' => 'required|array',  // Expect a list of products
//             'products.*.id' => 'exists:products,id',
//             'products.*.quantity' => 'integer|min:1',
//             'address' => 'required|string',
//         ]);

//         $total = 0;
//         foreach ($request->products as $product) {
//             $productModel = Product::find($product['id']);
//             $total += $productModel->price * $product['quantity'];
//         }

//         $order = Order::create([
//             'user_id' => Auth::id(),
//             'status' => 'pending',
//             'total_amount' => $total,
//             'address' => $request->address,
//         ]);

//         foreach ($request->products as $product) {
//             $order->products()->attach($product['id'], [
//                 'quantity' => $product['quantity'],
//                 'price' => Product::find($product['id'])->price,
//             ]);
//         }

//         return response()->json(['message' => 'Order placed successfully', 'order' => $order]);
//     }
// }





// namespace App\Http\Controllers;

// use App\Models\Order;
// use Illuminate\Http\Request;

// class OrderController extends Controller
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
//      * @param  \App\Models\Order  $order
//      * @return \Illuminate\Http\Response
//      */
//     public function show(Order $order)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      *
//      * @param  \App\Models\Order  $order
//      * @return \Illuminate\Http\Response
//      */
//     public function edit(Order $order)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \App\Models\Order  $order
//      * @return \Illuminate\Http\Response
//      */
//     public function update(Request $request, Order $order)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      *
//      * @param  \App\Models\Order  $order
//      * @return \Illuminate\Http\Response
//      */
//     public function destroy(Order $order)
//     {
//         //
//     }
// }
