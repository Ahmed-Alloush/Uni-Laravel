<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteListController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Models\Product;
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



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/getMe', [UserController::class, 'getMe']);
    Route::post('/editProfile​', [UserController::class, 'editProfile']);

    // Get shop information
    
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);



    // Additional routes for products within a shop
    Route::get('/products', [ProductController::class, 'getAllProducts']);
    Route::get('shops/{shopId}/products', [ProductController::class, 'index']);
    Route::get('shops/{shopId}/products/{productId}', [ProductController::class, 'show']);

    // Get category information

    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);

    // Get brand information
    
    Route::get('/brand', [BrandController::class, 'index']);
    Route::get('/brand/{id}', [BrandController::class, 'show']);
    
    // this is for RoleRequest 

    Route::post('/rolerequest', [RoleRequestController::class, 'store']);
   
    // this is for orders crud
   

    Route::post('/order', [OrderController::class, 'store']);
    Route::get('/order/myorders', [OrderController::class, 'getUserOrders']);
    Route::post('/order/myorders/edite/{orderId}', [OrderController::class, 'update']);
    Route::delete('/order/myorders/{orderId}', [OrderController::class, 'destroy']);

    // this is for cart 
    
    Route::get('/cart/mycart', [CartController::class, 'getAllProductsInMyCart']);
    Route::post('/cart/addtocart', [CartController::class, 'AddToCart']);
    Route::delete('/cart/removefromcart/{productId}', [CartController::class, 'RemoveFromCart']);
    Route::delete('/cart/deletecart', [CartController::class, 'deleteCart']);


    // this is for favoriteList 
    
    Route::get('/favoritelist/myfavoritelist', [FavoriteListController::class, 'getAllMyFavoriteProducts']);
    Route::post('/favoritelist/addtofavoritelist', [FavoriteListController::class, 'AddToMyFavoriteList']);
    Route::delete('/favoritelist/removefromfavoritelist/{productId}', [FavoriteListController::class, 'RemoveFromMyFavoriteList']);
    Route::delete('/favoritelist/deletefavoritelist', [FavoriteListController::class, 'deleteMyFavoriteList']);

    // this is for shop

    Route::middleware('checkRole:admin,super admin')->group(function () {
        Route::post('/shops', [ShopController::class, 'store']);
        Route::delete('/shops/{id}', [ShopController::class, 'destroy']);
        Route::post('/shops/edite/{id}', [ShopController::class, 'update']);

        // Route::post('shops/{shopId}/products', [ProductController::class, 'store']);
        // Route::post('shops/{shopId}/products/edite/{productId}', [ProductController::class, 'edite']);
        // Route::delete('shops/{shopId}/products/{productId}', [ProductController::class, 'destroy']);

        // Route::apiResource('/brand', BrandController::class);

        Route::post('/brand', [BrandController::class, 'store']);
        Route::post('/brand/edite/{id}', [BrandController::class, 'update']);
        Route::delete('/brand/{id}', [BrandController::class, 'destroy']);
    });
    Route::middleware('checkRole:admin')->group(function () {

    Route::post('shops/{shopId}/products', [ProductController::class, 'store']);
    Route::post('shops/{shopId}/products/edite/{productId}', [ProductController::class, 'edite']);
    Route::delete('shops/{shopId}/products/{productId}', [ProductController::class, 'destroy']);


});

    Route::middleware('checkRole:super admin')->group(function () {
        Route::post('/category', [CategoryController::class, 'store']);
        Route::post('/category/edite/{id}', [CategoryController::class, 'update']);
        Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
    });



    Route::middleware('checkRole:super admin')->group(function () {
        Route::get('/rolerequest', [RoleRequestController::class, 'index']);
        Route::get('/rolerequest/{id}', [RoleRequestController::class, 'show']);
        Route::post('/rolerequest/edite/{id}', [RoleRequestController::class, 'update']);
    });




});




// Route::post('/user/editProfile', [UserController::class, 'editProfile'])->middleware('auth:sanctum');



// Route::apiResource('orders', OrderController::class);



    // Route::apiResource('/category', CategoryController::class);
    // Route::apiResource('/product', ProductController::class);

    // Route::post('/product/edite/{id}', [ProductController::class, 'edite']);



// Route::apiResource('/brand',BrandController::class)->middleware('auth:sanctum');

// Route::apiResource('/product', ProductController::class);

// Route::post('/product/edite/{id}', [ProductController::class, 'edite']);
