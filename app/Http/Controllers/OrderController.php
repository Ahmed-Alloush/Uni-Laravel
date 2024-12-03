<?php


namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Fetch orders for the authenticated user

        return response()->json(array('orders' => $request->user()->orders));
        return response()->json($request->user()->orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array',  // Expect a list of products
            'products.*.id' => 'exists:products,id',
            'products.*.quantity' => 'integer|min:1',
            'address' => 'required|string',
        ]);

        $total = 0;
        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);
            $total += $productModel->price * $product['quantity'];
        }

        $order = Order::create([
            'user_id' => $request->user()->id(),
            'status' => 'pending',
            'total_amount' => $total,
            'address' => $request->address,
        ]);

        foreach ($request->products as $product) {
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => Product::find($product['id'])->price,
            ]);
        }

        return response()->json(['message' => 'Order placed successfully', 'order' => $order]);
    }
}



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
