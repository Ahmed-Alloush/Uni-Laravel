<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json(['token' => $request->bearerToken(), 'user' => $request->user()]);
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/user/editProfile', [UserController::class, 'editProfile'])->middleware('auth:sanctum');

Route::apiResource('/category', CategoryController::class);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{shop}', [ShopController::class, 'show']);

    Route::middleware('checkRole:admin,super admin')->group(function () {
        Route::post('/shops', [ShopController::class, 'store']);
        Route::delete('/shops/{shop}', [ShopController::class, 'destroy']);
        Route::post('/shops/edite/{shop}', [ShopController::class, 'update']);
    });


    Route::apiResource('orders', OrderController::class);

    // Additional routes for products within a shop
    Route::get('shops/{shop}/products', [ShopController::class, 'products']);
    Route::post('shops/{shop}/products', [ShopController::class, 'addProduct']);


    Route::post('/editProfile​', [UserController::class, 'editProfile']);
    Route::apiResource('/category', CategoryController::class);
    Route::apiResource('/brand', BrandController::class);
    Route::apiResource('/product', ProductController::class);

    Route::post('/product/edite/{id}', [ProductController::class, 'edite']);
});


// Route::apiResource('/brand',BrandController::class)->middleware('auth:sanctum');

Route::apiResource('/product', ProductController::class);

Route::post('/product/edite/{id}', [ProductController::class, 'edite']);
