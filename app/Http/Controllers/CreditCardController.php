<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    public function executePayment(Request $request)
    {
        // Step 1: Validate input data
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'card_number' => 'required|string|size:16',
            'expiration_date' => 'required|string',
            'ccv' => 'required|string|size:3',
            'order_id' => 'required|exists:orders,id',
        ]);
        // Step 2: Find the user's credit card
        $creditCard = CreditCard::where([
            'full_name' => $validated['full_name'],
            'card_number' => $validated['card_number'],
            'expiration_date' => $validated['expiration_date'],
            'ccv' => $validated['ccv']
        ])->first();

        if (!$creditCard) {
            return response()->json(['success' => false, 'message' => 'Credit card not found'], 404);
        }

        // Step 3: Load the order
        $order = Order::with('products')->find($validated['order_id']);


        if ($creditCard->balance < $order->total_price) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }

        // Step 4: Deduct the total price from user's credit card balance
        $creditCard->balance -= $order->total_price;

        $creditCard->save();

        return response()->json([
            'success' => true,
            'data' => $order->products,
        ], 200);

        // Step 5: Distribute payments to shop owners based on products' prices and quantities
        foreach ($order->products as $product) {

            $productTotalPrice = $product->price * $product->pivot->quantity;

            // Get the shop and its owner
            $shop = Shop::find($product->shop_id)->load('owner');

            $owner = User::with('creditcard')->find($shop->owner);

            if ($shop && $shop->owner && $owner->creditcard) {

                $shopOwnerCreditCard = $owner->creditcard;

                // Add the product's total price to the shop owner's credit card balance
                $shopOwnerCreditCard->balance += $productTotalPrice;
                $shopOwnerCreditCard->save();
            }
        }

        // Step 6: Update order status to "paid"
        $order->payment_status = 'paid';
        $order->save();

        // Step 7: Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Payment successful, balances updated',
            'total_order_price' => $order->total_price,
            'user_balance' => $creditCard->balance
        ], 200);
    }



    public function refundMoneyToCustomer(Request $request)
    {

        // Step 1: Validate input data
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'card_number' => 'required|string|size:16',
            'expiration_date' => 'required|string',
            'ccv' => 'required|string|size:3',
            'order_id' => 'required|exists:orders,id',
        ]);

        // Step 2: Find the user's credit card
        $creditCard = CreditCard::where([
            'full_name' => $validated['full_name'],
            'card_number' => $validated['card_number'],
            'expiration_date' => $validated['expiration_date'],
            'ccv' => $validated['ccv']
        ])->first();

        if (!$creditCard) {
            return response()->json(['success' => false, 'message' => 'Credit card not found'], 404);
        }

        // Step 3: Load the order with products
        $order = Order::with('products')->find($validated['order_id']);

        // Check if the order is already refunded
       
        if ($order->payment_status === 'refunded') {
            return response()->json(['success' => false, 'message' => 'Order already refunded'], 400);
        }

        // Step 4: Add the total price back to the user's credit card balance
        $creditCard->balance += $order->total_price;
        $creditCard->save();

        // Step 5: Deduct payments from shop owners based on product prices and quantities

        foreach ($order->products as $product) {

            $productTotalPrice = $product->price * $product->pivot->quantity;

            // Get the shop and its owner
            $shop = Shop::find($product->shop_id)->load('owner');

            $owner = User::with('creditcard')->find($shop->owner);

            if ($shop && $shop->owner && $owner->creditcard) {

                $shopOwnerCreditCard = $owner->creditcard;

                // Add the product's total price to the shop owner's credit card balance
                $shopOwnerCreditCard->balance -= $productTotalPrice;
                $shopOwnerCreditCard->save();
            }
        }

        // Step 6: Update order status to "refunded"
        $order->payment_status = 'refunded';
        $order->save();

        // Step 7: Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Refund successful, balances updated',
            'refunded_amount' => $order->total_price,
            'customer_balance' => $creditCard->balance
        ], 200);
    }
}














































// return response()->json([
//     'success' => true,
//     'data'=> $creditCard,
//     ],200);



// public function executePayment(Request $request)
// {
//     $validated = $request->validate([
//         'full_name' => 'required|string|max:255', // Full name must be a string and not exceed 255 characters
//         'card_number' => 'required|string|size:16', // Card number must be exactly 16 characters
//         'expiration_date' => 'date|string', // Expiration date in m/y format, must not be in the past
//         'ccv' => 'required|string|size:3', // CCV must be exactly 3 characters
//         'country' => 'required|string',
//         'city' => 'required|string',
//         'streetAddress' => 'required|string',
//         'order_id' => 'required|exists:orders,id',
//     ]);
//     // Use where() instead of find() for multiple conditions
//     $creditCard = CreditCard::where([
//         'full_name' => $validated['full_name'],
//         'card_number' => $validated['card_number'],
//         'expiration_date' => $validated['expiration_date'],
//         'ccv' => $validated['ccv']
//     ])->first(); // Use first() to get the first matching result

//     if (!$creditCard) {
//         return response()->json(['success' => false, 'message' => 'Credit card not found'], 404);
//     }

//     // Now load the related address
//     $creditCard->load('address');

//     if (!($creditCard->address->country == $validated['country'] && $creditCard->address->city == $validated['city'] && $creditCard->address->street_address == $validated['streetAddress'])) {
//         return response()->json(['success' => false, 'message' => 'Credit card not found'], 404);
//     }


//     $order = Order::find($validated['order_id']);



//     if ($creditCard->balance >= $order->total_price) {
//         // $creditCard->balance -= $validated['total_price'];
//         // $creditCard->save();












//         $order = Order::find($validated['order_id']);

//         $products = $order->load('products')->products;


//         $shop = Shop::find($products[0]->shop_id)->load('owner');


//         $user_id = $shop->load('owner')->owner;

//         $user = User::find($user_id)->load('creditCard');

//         return response()->json(['status' => 'success', 'message' => 'Payment successful', 'data' => $products, 'shop' => $shop, 'user' => $user], 200);
//     } else {
//         return response()->json(['status' => 'failed', 'message' => 'You don\'t have enough money to make this payment!'], 400);
//     }
// }
