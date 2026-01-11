<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// This initializes the Laravel Application instance.
// basePath tells Laravel where the root of your project is. This allows Laravel to find your .env file and other resources.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*'); // To share sail app using sail share

        //* Registering global middleware:
        // The append method adds the middleware to the end of the list of global middleware. 
        // Can use prepend also if necessary to check first.
        $middleware->append(EnsureTokenIsValid::class);

        //* Customize laravel's default middleware:
        // $middleware->use([\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class])
        // So, only that global middleware in use will work, others will not wok anymore.

        //* Middleware Group
        // We have already web and api middleware group, a group contains many middlewares as a name so that we can apply all middlewares using only that name.
        // Group several middleware under a single key to make them easier to assign to routes
        // $middleware->web(prepend: [CustomMiddleware::class], replace: [], remove: [])
        // Do grouping using appendGroup or prependGroup:
        // $middleware->appendToGroup('web', UserRole::class);- check user role for every web request.
        // $middleware->appendToGroup('custom-group-name', [First::class, Second::class]);
        // Now, that group can be apply to the route, same process of normal middleware adding, just give the group name in place of middleware name.

        // We can also manually redefine all web and api middleware.
        // $middleware->group('web', [\Illuminate\Cookie\Middleware\EncryptCookies::class])

        //* Middleware Aliases:
        // Middleware aliases allow you to define a short alias for a given middleware class.
        $middleware->alias(['token-validity' => EnsureTokenIsValid::class]);
        // If we just do alias, we dont need to append or prepend to assign route
        // We simply can use that alias as middleware for the route.
        // Laravel's default alias: auth, auth.basic, auth.session, cache.headers
        // can(Authorize middleware), guest, password.confim, precognitive, signed, subscribed, throttle, verified.

        //* Sorting Middleware:
        // Though rarely need middleware to execute in a specific order
        // We can do it by $middeware->priority([\Illuminate\Cookie\Middleware\EncryptCookies::class, ...others])
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
