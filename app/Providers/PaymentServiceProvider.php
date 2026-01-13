<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class PaymentServiceProvider extends ServiceProvider  implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentProcessor::class, Bkash::class);
        // Now, whenever we inject PaymentProcessor, Bkash object will be called.
        // Doesnt matter from which service provider this container is bounded.

        // Giving alias:
        $this->app->alias(PaymentProcessor::class, 'paymentProcessor'); 
        // Now, wecan return paymentProcessor string from Facade or fully qualified class.
    }

    // It does not required that service provider must have boot method.

    //* Deferred Providers:
    // They will not be loaded on every request, but only when the services they provide are actually needed.
    // Let's say you resolved PaymentProcessor in a show method of a controller, it will be only load when show method called.
    // Because its binded into a deferred provider.
    // Will improve the performance of your application, since it is not loaded from the filesystem on every request.
    // Choose only when you have service container binding in register method, not else, not boot().
    // To defer: 
    // 1. Implement the \Illuminate\Contracts\Support\DeferrableProvider interface 
    // 2. Define a provides method which returns the bindings registered in register method.
    // If we edit deferred provider, run : php artisan clear-compiled.
    public function provides(): array
    {
        // return [PaymentProcessor::class];
        return [PaymentProcessor::class, 'paymentProcessor'];
    }
}
