<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    // If the given token does not match our secret token, the middleware will return an HTTP redirect to the client; otherwise, the request will be passed further into the application.
    // To pass the request deeper into the application ,we should call the $next callback with the $request.
    // A series of "layers" HTTP requests must pass through before they hit your application. Each layer can examine the request and even reject it entirely.
    // In middlewar's constructor we can type hint any dependency, it will be automatically resolved via the service container
    // A middleware can perform tasks before or after passing the request deeper into the application. 
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->input('token') !== 'my-secret-token') {
            return redirect('/home');
        }
        return $next($request); // perform some task before the request is handled

        // $response = $next($request);  return $response;
        // This will perform its task after the request is handled by the application.
    }

    //* Register this middleware as global middleware into bootstrap/app.php
    // At last we can assign this middleware to route as:
    // ->middleware(EnsureTokenIsValid::class) or, ->middleware([First::class, Second::class]) or as group. 
    // When assigning group middleware, we may want a child route without applying that middleware, just use: ->withoutMiddleware([EnsureTokenIsValid::class])
    // Can have a set of route without this middleware: Route::withoutMiddleware([EnsureTokenIsValid::class])->group(function ()

    //* Middleware Parameter:
    // We can pass extra parameter also.
    // Let's say we have passed string $role in handle() after the Closure
    // And we have a role = admin.
    // We aliased the middleware as role in app.php
    // Now we can check in route like that: ->middleware('role:admin') or directly CheckUserRole::class . ':admin'
    // Multiple parameters may be delimited by commas: ->middleware(EnsureUserHasRole::class.':editor,publisher')

    //* Terminable Middleware:
    // Sometimes a middleware may need to do some work after the HTTP response has been sent to the browser.
    // You use terminable middleware when you have a heavy task that doesn't need to be finished for the user to see the page.
    // Running these tasks after the response makes your application feel much faster.
    // Real-Life Example: Request Logging & API Analytics.
    // Sending a "Welcome" email immediately after a successful registration response
    public function terminate(Request $request, Response $response): void
    {
        // It will automatically be called after the response is sent to the browser
        // should receive both the request and the response
        // When calling the terminate method on your middleware, Laravel will resolve a fresh instance of the middleware from the service container. 
        //  If you would like to use the same middleware instance when the handle and terminate methods are called, register the middleware with the container using the container's singleton method.
        // In AppServiceProvider's register method: $this->app->singleton(TerminatingMiddleware::class);
    }
}
