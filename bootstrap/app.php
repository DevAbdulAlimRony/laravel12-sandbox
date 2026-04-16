<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Client\RequestException;
use Illuminate\Console\Scheduling\Schedule;

use PDOException;
use Psr\Log\LogLevel;
use App\Exceptions\InvalidOrderException;

use Illuminate\Support\Lottery;
use Throwable;

// This initializes the Laravel Application instance.
// basePath tells Laravel where the root of your project is. This allows Laravel to find your .env file and other resources.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web     : __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php', // Broadcasting routes.
        health  : '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //* Trusting Hosts:
        // In a perfect world, every computer could talk to every other computer directly. In reality, we use Proxy Servers (and their cousins, Reverse Proxies) to act as a protective "shield" or a smart "traffic controller" for your application.
        // Security: If you connect directly to your app, your server's Real IP Address is exposed to the entire internet.
        // Load Balancing: A direct connection only works for one server. If your app becomes popular and you need three servers to handle the traffic, a direct connection won't know which one to go to.
        // A proxy can "remember" (cache) your homepage or your images. When the next user asks for them, the proxy sends the file immediately without even waking up your PHP server. This makes your site feel much faster.
        // User hits a public URL (e.g., https://random-slug.shared.com)., The Proxy Server (Expose/Ngrok) receives that request., The Proxy forwards the request to your Local Machine.
        // trustProxies determine which "proxies" (intermediate servers) it should trust when determining the origin of a web request.
        // "I don't know the exact IP address of the proxy server forwarding these requests, so trust all proxies to tell me the truth about the user's IP and whether the connection was secure (HTTPS).
        // Useful to share sail app using sail share in local, Production (Cloudflare/Load Balancers).
        // For direct connection, we dont need it.

        // We should configure our web server such as nginx or apache to only send requests that match a given hostname.
        // If we have no ability to customize server, we can do in laravel:
        // $middleware->trustHosts(at: ['^laravel\.test$']); // or from config file:
        // $middleware->trustHosts(at: fn () => config('app.trusted_hosts'));
        // By default, requests coming from subdomains of the application's URL are also automatically trusted. 
        // We can disable this by subdomains: false.

        // We can enable TrustProxies middleware if we run our application behind a load balancer  that terminates TLS / SSL certificates.
        $middleware->trustProxies(at: ['192.168.1.1', '10.0.0.0/8']);
        // We may also configure the proxy headers that should be trusted: $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR | and others...
        // If we use AWS Elastic Load Balancing, the headers value should be Request::HEADER_X_FORWARDED_AWS_ELB.
        // If we are using Amazon AWS or another "cloud" load balancer provider, we may not know the IP addresses of the actual balancers.
        // In that case, trust all proxies:
        $middleware->trustProxies(at: '*');

        //* Trusting Proxies and Load Balancer (like AWS ELB, Cloudflare, or Nginx)
        // A Load Balancer is a server (or service) that sits in front of your web servers. 
        // When a user visits your site, they don't talk to your server directly; they talk to the Load Balancer first.
        // To the server, it looks like the request is coming from the Load Balancer's IP, not the user's and laravel got confused.
        // Benefecial for: 
        // Scaling (High Traffic): If you have 10,000 people visiting your site at once, one server might crash. A Load Balancer spreads that traffic across multiple servers
        // Reliability (No Downtime), SSL Termination, t decrypts the secure traffic and sends it to your servers as plain "HTTP" traffic over a private network.
        // The request technically hits the Load Balancer first, which then passes it to the server.
        // Without Trusted Proxies, if you call request()->ip() in your code, it will return the IP of the Load Balancer, not the customer.

        //* Users Redirection:
        $middleware->redirectGuestsTo('/login'); // redirect uauthenticated user.
        $middleware->redirectGuestsTo(fn (Request $request) => route('login')); // using closure.
        $middleware->redirectUsersTo('/panel'); // Redirect authenticated user.
        $middleware->redirectUsersTo(fn (Request $request) => route('panel')); // Using a closure.
        
        //* Global middleware:
        // While most middleware only runs for specific routes (like checking if a user is logged in before they see their dashboard), global middleware runs for every single HTTP request that hits your application.
        // Think of middleware like a series of filters in a water pipe, so The Order Matters: append vs. prepend
        // Laravel have some global middlewares by default: TrimStrings, ValidatePostSize, ConvertEmptyStringsToNull, TrustProxies, PreventRequestsDuringMaintenance etc.

        // Customize laravel's default middleware:
        $middleware->use([\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class])
        // So, only that global middleware in use will work, others will not wok anymore.

        // Registering Your own global middleware:
        // The append method adds the middleware to the end of the list of global middleware. 
        // Can use prepend also if necessary to check first.
        $middleware->append(EnsureTokenIsValid::class);

        //* Middleware Group
        // We have already web and api middleware group, a group contains many middlewares as a name so that we can apply all middlewares using only that name.
        // Group several middleware under a single key to make them easier to assign to routes
        $middleware->web(prepend: [CustomMiddleware::class], replace: [], remove: [])
        $middleware->appendToGroup('web', UserRole::class); // Check user role for every web request.
        $middleware->prependToGroup('custom-group-name', [First::class, Second::class]);
        // Now, that group can be apply to the route, same process of normal middleware adding, just give the group name in place of middleware name.

        // Remove Global middleware:
        $middleware->remove([ConvertEmptyStringsToNull::class,  TrimStrings::class]);

        // Remove Global middleware for specific set of requests:
        $middleware->convertEmptyStringsToNull(except: [fn (Request $request) => $request->is('admin/*')]);

        //* Cookies Encryption
        // All cookies are encypted automatically so that can't be modified or read.
        // If we dont want it for any cookie:
        $middleware->encryptCookies(except: ['cookie_name']);
        // In general, cookie encryption should never be disabled.

        //* Validating csrf token for a route (Not recommended):
        $middleware->validateCsrfTokens('/dashboard');

        // We can also manually redefine all web and api middleware.
        // $middleware->group('web', [\Illuminate\Cookie\Middleware\EncryptCookies::class])

        //* Sorting Middleware:
        // Though rarely need middleware to execute in a specific order
        // We can do it by $middeware->priority([\Illuminate\Cookie\Middleware\EncryptCookies::class, ...others])

        //* Middleware Aliases:
        // Middleware aliases allow you to define a short alias for a given middleware class.
        $middleware->alias(['token-validity' => EnsureTokenIsValid::class]);
        // If we just do alias, we dont need to append or prepend to assign route to make a local middleware.
        // Laravel's default alias: auth, auth.basic, auth.session, cache.headers
        // can(Authorize middleware), guest, password.confim, precognitive, signed, subscribed, throttle, verified.

        //* Sanctum Token Middleware:
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
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
        // To customize those define error pages of those.

        //* How exceptions are reported and rendered by your application:
        // The $exceptions object provided to the withExceptions closure is an instance of Illuminate\Foundation\Configuration\Exceptions.
        // During local development, should set the APP_DEBUG environment variable to true. During production, make it false always.
        // In Laravel, exception reporting is used to log exceptions or send them to an external service like Sentry or Flare. 
        // Exceptions will be logged based on our logging configuration.
        $exceptions->report(function (InvalidOrderException $e): void { return $e;});
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
         $exceptions->dontReport([InvalidOrderException::class]);
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

         //* Rarely, need to customize the entire HTTP response 
         $exceptions->respond(function(Response $response){if(){}return $response;});

         //* Instead of defining custom report and render here, we can do it directly in Exception class
         // See InvalidOrderException.

        //* Throttling Reported Exceptions:
        // If your application starts throwing thousands of errors per second (for example, if your database goes down), a standard reporting system will try to log every single one of them. 
        // This can lead to a secondary disaster where your logging tool or your server's disk space crashes under the pressure.
        // When a core service fails, you don't need 50,000 log entries telling you the exact same thing. You only need a few to realize there is a problem.
        // If application have very large number of exceptions, to take just random:
        $exceptions->throttle(function (Throwable $e) { return Lottery::odds(1, 1000); });
        // can use if else condition to make Lottery
        // Can apply Rate Limiting also.
    })
    ->withEvents(
        // The discover array tells Laravel: "Go look in these specific folders. If you find a class that looks like a Listener, automatically connect it to the Event it's looking for."
        discover: [
        // __DIR__.'/../app/Domain/Orders/Listeners',
        __DIR__.'/../app/Domain/*/Listeners', // Multiple similar dirctories.
    ])
    ->withCommands([
        // Custom directory for artisan commands. If directory is console/commands, no need to add.
        __DIR__.'/../app/Domain/Orders/Commands',
        // Or, manually give the class name:
        SendEmails::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Calling task schedule here rather than in console.php
        $schedule->call(new DeleteRecentUsers)->daily();
    })
    // Authorizing private Broadcast Channel using Sanctum:
    // First, remove channels from withRouting. Then,
    ->withBroadcasting(
         __DIR__.'/../routes/channels.php',
         ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )
    ->registered(function (): void {
        // If the API returns a massive amount of data (like a giant HTML page or a 5MB JSON blob), Laravel will "truncate" (cut off) the message so your logs don't explode in size.
        RequestException::truncateAt(240); // It tells Laravel: "If an API error occurs, only show me the first 240 characters of the response body in the exception message."
        RequestException::dontTruncate();
    })
    ->create();
