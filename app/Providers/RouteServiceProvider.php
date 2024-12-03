<?php



// namespace App\Providers;

// use Illuminate\Cache\RateLimiting\Limit;
// use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\RateLimiter;
// use Illuminate\Support\Facades\Route;

// class RouteServiceProvider extends ServiceProvider
// {
//     /**
//      * The path to the "home" route for your application.
//      *
//      * Typically, users are redirected here after authentication.
//      *
//      * @var string
//      */
//     public const HOME = '/home';

//     /**
//      * Define your route model bindings, pattern filters, and other route configuration.
//      *
//      * @return void
//      */
//     public function boot()
//     {
//         // Add route model bindings here
//         $this->bootBindings();

//         // Set up rate limiting and routes
//         $this->configureRateLimiting();

//         $this->routes(function () {
//             Route::middleware('api')
//                 ->prefix('api')
//                 ->group(base_path('routes/api.php'));

//             Route::middleware('web')
//                 ->group(base_path('routes/web.php'));
//         });
//     }

//     /**
//      * Configure the rate limiters for the application.
//      *
//      * @return void
//      */
//     protected function configureRateLimiting()
//     {
//         RateLimiter::for('api', function (Request $request) {
//             return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
//         });
//     }

//     /**
//      * Define custom route model bindings.
//      *
//      * @return void
//      */
//     protected function bootBindings()
//     {
//         // Custom binding for 'product' to handle 404 JSON response
//         Route::bind('product', function ($value) {
//             return \App\Models\Product::find($value) ?? abort(404, json_encode([
//                 'success' => false,
//                 'message' => 'Product not found.'
//             ]));
//         });
//     }
// }













namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
