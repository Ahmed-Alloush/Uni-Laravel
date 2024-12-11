<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display all products in the user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAllProductsInMyCart(Request $request)
    {
        try {
            $user = $request->user();

            // Find the cart associated with the user
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json(['status' => 'failed', 'message' => 'You don\'t any products in your cart'], 404);
            }

            // Get all products in the cart
            $products = $cart->products;

            return response()->json(['status' => 'success', 'data'=>$products], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add a product to the user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function AddToCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $user = $request->user();

            // Find or create the cart for the user
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Add the product to the cart
            $cartProduct = CartProduct::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ]);

            return response()->json([
                'status'=> 'success',
                'message' => 'Product added to your cart successfully.',
                'data' => $cartProduct,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove a product from the user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function RemoveFromCart(Request $request,$productId)
    {
        try {



            $user = $request->user();

            // Find the cart associated with the user
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json(['status' => 'failed', 'message' => 'Cart not found'], 404);
            }

            // Find the product in the cart
            $cartProduct = CartProduct::where([
                'cart_id' => $cart->id,
                'product_id' => $productId,
            ])->first();

            if (!$cartProduct) {
                return response()->json(['status' => 'failed', 'message' => 'Product not found in the cart'], 404);
            }

            // Delete the cart product
            $cartProduct->delete();

            return response()->json(['status' => 'success', 'message' => 'Product removed from your cart successfully.'], 200);
         } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Cart or product not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the entire cart for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteCart(Request $request)
    {
        try {
            $user = $request->user();

            // Find the cart associated with the user
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json(['status' => 'failed', 'message' => 'Cart not found'], 404);
            }

            // Delete all products in the cart
            $cart->products()->detach();

            // Delete the cart itself
            $cart->delete();

            return response()->json(['status' => 'success', 'message' => 'Cart deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }
}
