<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //* Explicit Route Binding
        Route::model('user', User::class); // all user parameter in route {user} will point to the User's instance.
        // If a matching model instance is not found in the database, a 404 HTTP response will be automatically generated.
        // every time a route has {user}, Laravel knows to look in the User table. If you want to change that logic globally to search by username instead, you only change it in one place
        
        // If we want our own binding resolution logic:
        Route::bind('user', function (string $value) {
            return User::where('name', $value)->firstOrFail();
        });
        // You use Route::bind when the logic to find a model is more complex than just looking at a single column.
        // Exmp: When a user visits a route, you don't just want to find the user by ID; you want to make sure they are active and belong to the correct company.
        
        //* Global Constraints fro route parameter validation:
        Route::pattern('id', '[0-9]+');
        
        //* Rate Limiters
        // The for method accepts a rate limiter name and a closure that returns the limit .
        // Limit configuration are instances of the Illuminate\Cache\RateLimiting\Limit class. 
        // If exceed, 429 status code
        RateLimiter::for('global', function (Request $request){
            return Limit::perMinute(1000)->response(function (Request $request, array $headers){
                return response('Custom Message', 429, $headers);
            });
        });

        RateLimiter::for('uploads', function(Request $request){
            return $request->user()->isAdmin()
                    ? Limit::none()->by($request->user()->id)
                    : Limit::perMinute(100)->by($request(ip));
        }); //by means per ip address or per user here

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Multiple Rate Limits:
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(500),
                Limit::perMinute(3)->by($request->input('email')),
                Limit::perDay(1000)->by('day:'.$request->user()->id),
            ];
        });

        // Laravel allows you to rate limit based on the response using the after method.
        // when you only want to count certain responses toward the rate limit, such as validation errors, 404 responses, or other specific HTTP status codes.
        // The after method accepts a closure that receives the response and should return true if the response should be counted toward the rate limit
        // Useful for preventing enumeration attacks by limiting consecutive 404 responses.
        RateLimiter::for('resource-not-found', function (Request $request) {
            return Limit::perMinute(10)
                         ->by($request->user()?->id ?: $request->ip())
                         ->after(function (Response $response) {
                            // Only count 404 responses toward the rate limit to prevent enumeration...
                            return $response->status() === 404;
            });

        });
    }
}
