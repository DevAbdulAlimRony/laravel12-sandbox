<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use PDOException;
use Psr\Log\LogLevel;
use App\Exceptions\InvalidOrderException;

use Illuminate\Support\Lottery;
use Throwable;

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
        //* HTTP Exceptions:
        //  abort(404);

        //* Custom error page for HTTP status code:
        // We may have resources/views/errors/404.blade.php.
        // This view will be rendered for all 404 errors generated by the application.
        // We can access message in blade: {{ $exception->getMessage() }}

        //* Publish laravel's default error pages: php artisan vendor:publish --tag=laravel-errors

        //* Fallback Error Page:
        // Define a 4xx.blade.php template and a 5xx.blade.php template in your application's resources/views/errors directory.
        // When defining fallback error pages, the fallback pages will not affect 404, 500, and 503 error responses since Laravel has internal, dedicated pages for these status codes. 
        // Tocustomize those define error pages of those.

        //* How exceptions are reported and rendered by your application.
        // The $exceptions object provided to the withExceptions closure is an instance of Illuminate\Foundation\Configuration\Exceptions.
        // During local development, should set the APP_DEBUG environment variable to true. During production, make it false always.
        // In Laravel, exception reporting is used to log exceptions or send them to an external service like Sentry or Flare. 
        // Exceptions will be logged based on our logging configuration.
        $exception->report(function (InvalidOrderException $e): void { return $e;});
        // We may use ->stop() or return false, to prevent logging by default logging configuration.

        //* If available, current user's id will be the default contextual data. But we cn change it:
        $exceptions->cotext(fn () => ['foo' => 'bar']);
        // If we want to add different context for different exception, we can do it in that Exception class.
        // See Exceptions/InvalidOrderException.

        //* If we use report() helper, same exception instance can occur duplicately, we can prevent that:
         $exceptions->dontReportDuplicates();
         // When the report helper is called with the same instance of an exception, only the first call will be reported.

         //* Changing Log Level:
         $exceptions->level(PDOException::class, LogLevel::CRITICAL);

         //* Some exceptions, you never want to log or report:
         $exceptions->dontReport[[InvalidOrderException::class]];
         // or Just in InvalidOrderException add this interface: implements ShouldntReport

         //We can give condition when not to report
         $exceptions->dontReportWhen(function(Throwable $e): void{
            return $e instanceof InvalidOrderException && $e->reason() === 'Subscription Expired';
         })

         //* Laravel automatically ignores some exceptions like 404 HTTP errors or 419 HTTP responses by csrf tokens.
         $exceptions->stopIgnoring(HttpException::class);

         //* Exceptions automatically rendered as Response. If we want to change that:
         $exceptions->render(function (InvalidOrderException $e, Request $request){
            return response()->view('errors.invalid-order', status: 500);
            // or send esponse()->json();
         });

         // May also use the render method to override the rendering behavior for built-in Laravel or Symfony exceptions such as NotFoundHttpException

         // $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e)

         //* Raely, need to customize the entire HTTP response 
         $exceptions->respond(function(Response $response){if(){}return $response;});

         //* Instead of defining custom report and render here, we can do it directly in Exception class
         // See InvalidOrderException.

        //* Throttling Reported Exceptions:
        // If application have very large number of exceptions, to take just random:
        $exceptions->throttle(function (Throwable $e) { return Lottery::odds(1, 1000); });
        // can use if else condition to make Lottery
        // Can apply Rate Limiting also.
    })
    ->create();
