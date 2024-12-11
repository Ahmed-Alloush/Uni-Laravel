<?php

namespace App\Http\Controllers;

use App\Models\FavoriteList;
use App\Models\FavoriteProduct;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteListController extends Controller
{

    public function getAllMyFavoriteProducts(Request $request)
    {
        try {
            $user = $request->user();

            // Find the cart associated with the user
            $favoriteList = FavoriteList::where('user_id', $user->id)->first();

            if (!$favoriteList) {
                return response()->json(['status' => 'failed', 'message' => 'You don\'t any products in your favorite list'], 404);
            }

            // Get all products in the cart
            $products = $favoriteList->products;

            return response()->json(['status' => 'success', 'data' => $products], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }


    public function AddToMyFavoriteList(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $user = $request->user();

            // Find or create the cart for the user
            $favoriteList = FavoriteList::firstOrCreate(['user_id' => $user->id]);

            // Add the product to the cart
            $favoriteProduct = FavoriteProduct::create([
                'favorite_list_id' => $favoriteList->id,
                'product_id' => $request->product_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to your favorite list successfully.',
                'data' => $favoriteProduct,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    public function RemoveFromMyFavoriteList(Request $request, $productId)
    {
        try {



            $user = $request->user();

            // Find the cart associated with the user
            $favoriteList = FavoriteList::where('user_id', $user->id)->first();

            if (!$favoriteList) {
                return response()->json(['status' => 'failed', 'message' => 'favorite list not found'], 404);
            }

            // Find the product in the cart
            $favoriteProduct = FavoriteProduct::where([
                'favorite_list_id' => $favoriteList->id,
                'product_id' => $productId,
            ])->first();

            if (!$favoriteProduct) {
                return response()->json(['status' => 'failed', 'message' => 'Product not found in the favorite list'], 404);
            }

            // Delete the cart product
            $favoriteList->delete();

            return response()->json(['status' => 'success', 'message' => 'Product removed from your favorite list successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'failed', 'message' => 'favorite list or product not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }


    public function deleteMyFavoriteList(Request $request)
    {
        try {
            $user = $request->user();

            // Find the cart associated with the user
            $favoriteList = FavoriteList::where('user_id', $user->id)->first();

            if (!$favoriteList) {
                return response()->json(['status' => 'failed', 'message' => 'favorite list not found'], 404);
            }

            // Delete all products in the cart
            $favoriteList->products()->detach();

            // Delete the cart itself
            $favoriteList->delete();

            return response()->json(['status' => 'success', 'message' => 'favorite list deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }
}
