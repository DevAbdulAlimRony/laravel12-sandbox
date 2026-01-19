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

        //* Remove Global middleware:
        // $middleware->remove([ConvertEmptyStringsToNull::class,  TrimStrings::class]);
        
        //* Remove Global middleware for specific set of requests:
        $middleware->convertEmptyStringsToNull(except: [fn (Request $request) => $request->is('admin/*')]);

        //* Cookies Encryption
        // All cookies are encypted automatically so that can't be modified or read.
        // If we dont want it for any cookie:
        $middleware->encryptCookies(except: ['cookie_name']);
        // In general, cookie encryption should never be disabled

        //* Trusting Proxies and Load Balancer (like AWS ELB, Cloudflare, or Nginx)
        // A Load Balancer is a server (or service) that sits in front of your web servers. 
        // When a user visits your site, they don't talk to your server directly; they talk to the Load Balancer first.
        // To the server, it looks like the request is coming from the Load Balancer's IP, not the user's and laravel got confused.
        // Benefecial for: 
        // Scaling (High Traffic): If you have 10,000 people visiting your site at once, one server might crash. A Load Balancer spreads that traffic across multiple servers
        // Reliability (No Downtime), SSL Termination, t decrypts the secure traffic and sends it to your servers as plain "HTTP" traffic over a private network.
        // The request technically hits the Load Balancer first, which then passes it to the server.
        // Without Trusted Proxies, if you call request()->ip() in your code, it will return the IP of the Load Balancer, not the customer.

        // We can enable TrustProxies middleware if we run our application behind a load balancer  that terminates TLS / SSL certificates.
        $middleware->trustProxies(at: ['192.168.1.1', '10.0.0.0/8']);
        // We may also configure the proxy headers that should be trusted: $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR | and others...
        // If we use AWS Elastic Load Balancing, the headers value should be Request::HEADER_X_FORWARDED_AWS_ELB.
        // If we are using Amazon AWS or another "cloud" load balancer provider, we may not know the IP addresses of the actual balancers.
        // In that case, trust all proxies: $middleware->trustProxies(at: '*');

        //* Trusting Hosts:
        // We should configure our web server such as nginx or apache to only send requests that match a given hostname.
        // If we have no ability to customize server, we can do in laravel:
        // $middleware->trustHosts(at: ['^laravel\.test$']); // or from config file:
        // $middleware->trustHosts(at: fn () => config('app.trusted_hosts'));
        // By default, requests coming from subdomains of the application's URL are also automatically trusted. 
        // We can disable this by subdomains: false.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
