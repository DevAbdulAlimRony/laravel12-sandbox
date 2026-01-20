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
     * Its extending ServiceProvider that has a $app field, we can use that.
     */

    // Service providers are the central place of all Laravel application bootstrapping.
    // Service providers are the central place to configure your application, registering things like service container bindings.
    // All user-defined service providers are registered in the bootstrap/providers.php file
    // If we create our own service provider, and register and do things like here in the AppServiceProvider, they will work same as here.
    // Make Custom Provider: php artisan make:provider PaymentProcessorProvider
    // If we manually created the provider, we should register it in bootsrap/providers.php
    // Within any of your service provider methods, you always have access to the $app property which provides access to the service container

    //* Binding Dependency (First way)
    public $bindings = [
        PaymentProcessor::class => Bkash::class
    ]; // So, here laravel is saying, when someone inject PaymentProcessor interface, give him a Bkash instance.
    // Can define singletons like this also.

    // Within the register method, you should only bind things into the service container, nothing else.
    public function register(): void
    {
        //* Binding Terminable middleware from same handled middleware
        // $this->app->singleton(TerminatingMiddleware::class);

        //* Binding Dependency (Second Way)
        $this->app->bind(PaymentProcessor::class, Bkash::class);

        //* Binding Dependency (Third Way- If class have extra config or constructor)
        $this->app->bind(PaymentProcessor::class, function(){
            return new Bkash('something-here-from-constructor');
        });
        // We can pass the extra dependency of Bkash as second argument, but thats not the standard solution
        // Because that dependency can have another dependency and so on.

        //* Binding Dependency (Fourth Way- If dependency have another dependency)
        $this->app->bind(PaymentProcessor::class, function(Application $app){
            return $app->make(Bkash::class, ['parameter-from-constructor-in-bkash' => ['value-of-the-constructor']])
        })

        //* Binding Dependency (Fifth Way- singleton- If we want to instantiate just once no matter how many times we inject it into a single class)
        $this->app->singleton(PaymentProcessor::class, function(Application $app){
            return $app->make(Bkash::class, ['parameter-from-constructor-in-bkash' => ['value-of-the-constructor']])
        })
        // You may use the 'singletonIf()' method to register a singleton container binding only if a binding has not already been registered for the given type
        // or in the interface or class, we can use attribute: #[Singleton] class Transa..{}

        //* Binding Dependency (Sixth Way- Use scoped if you want to bind only one time within a given request or job lifecycle.
        // $this->app->scoped()
        // $this->app->scopedIf:  A scoped container binding only if a binding has not already been registered for the given type
        // Can use attribute also: #[Scoped]
        
        //* Binding Dependency (Seventh Way):
        //* $this->app->instance(Transistor::class, $service): 
        // when you already have an object and you want to hand it over to Laravel to manage.

        //* Binding Dependency (Eighth Way):
        // Just use the Bind attribute before the interface. Then you dont need to bind it in here.
        // #[Bind(RedisEventPusher::class)] #[Bind(FakeEventPusher::class, environments: ['local', 'testing'])] interface EventPusher{} 
        // #[Bind(RedisEventPusher::class)] #[Singleton]: Automatically binded as singleton, no need to register here.

        //* Binding Dependency (Ninth Way-Contextual Binding two controllers may depend on different implementations):
        $this->app->when(TransactionController::class)
                  ->needs(PaymentProcessor::class)
                  ->give(function () {
                    return Storage::disk('local');
                 });
         // and another binding for another controller

        //* Binding Dependency (Tenth Way- Primitives Value Binding)
        $this->app->when(UserController::class)->needs('$variableName')->give($value);
        //  ->giveTagged('reports'), ->giveConfig('app.timezone');

        //* $this->app->tag([CpuReport::class, MemoryReport::class], 'reports');
        //* $this->app->extend

        //* Binding as user input
        $this->app->bind(PaymentProcessor::class, function ($app) {
            $gateway = request()->input('gateway'); // Get 'bkash' or 'rocket' from the URL/Form
            if ($gateway === 'bkash') {
                return new BkashProcessor();
            }
            return new RocketProcessor();
        });
        // Be careful with singleton here. If you use singleton, the first gateway chosen in a request will stay "locked in" for the duration of that request.
        // But strategy pattern is the best choice for this scenario.

        //* Laravel provides some Contextual Attributes
        // #[Storage('local')] protected Filesystem $filesystem: to inject a specific storage disk
        // #[Auth('web')] protected Guard $auth,
        // #[Cache('redis')] protected Repository $cache,
        // #[Config('app.timezone')] protected string $timezone,
        // #[Context('uuid')] protected string $uuid,
        // #[Context('ulid', hidden: true)] protected string $ulid
        // #[DB('mysql')] protected Connection $connection
        // #[Give(DatabaseRepository::class)] protected UserRepository $users
        // #[Log('daily')] protected LoggerInterface $log
        // #[RouteParameter('photo')] protected Photo $photo
        // #[Tag('reports')] protected iterable $reports

        //* Own Custom Attribute:
        // We can make our own custom attribute implementing the Illuminate\Contracts\Container\ContextualAttribute contract.
        // The container will call your attribute's resolve method.

        // The service container fires an event each time it resolves an object.
        // We may listen to this:
        // $this->app->resolving(Transistor::class, function (Transistor $transistor, Application $app) {}
        // Listen for rebinding: $this->app->rebinding()
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Boot method is being called after all service priver registered
        // So we can inject any service provider here to use.

        View::share('name', 'Abdul Alim'); // Now, this name will be available in all blade file into our entire application.

        //* Register a View Composer
        View::composer('profile', ProfileComposer::class); // Class Based Composer
        // Closure Based Composer:
        View::composer('transaction', function(View $view){
            // Do or return something...
        });
        // Assigning multiple views to a single composer:
        View::composer(['profile', 'welcome'], ProfileComposer::class);
        // Assigning all views
        View::composer('*', ProfileComposer::class);

        //* Custom Blade directive:
        Blade::directive('datetime', function (string $expression) {
            return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
        }); // Now, we can use @datetime ... @enddatetime in our blade.

        //* Custom Echo Handler:
        // When we attempt to echo an object using blade, magic method __toString will be invoked.
        // Sometimes we may not have conntrol when using third party package.
        //  Blade allows you to register a custom echo handler for that particular type of object.
        Blade::stringable(function (Money $money) {
            return $money->formatTo('en_GB');
        });

        //* Custom If Directive:
        Blade::if('disk', function (string $value) {
            return config('filesystems.default') === $value;
        }); // Now we can use @disk('value') @enddisk

        //* Making blade component's alias during package development:
        Blade::component('package-alert', Alert::class); // Now can access as <x-package-alert/>
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade'); // <x-nightshade::calendar />
        Blade::anonymousComponentPath(__DIR__.'/../components');
        Blade::anonymousComponentPath(__DIR__.'/../components', 'dashboard');

        // View Creator: Let's say we have Views/Creators/ProfileCreaor same as ProfileComposer.
        // View::creator('profile', ProfileCreator::class)
        // Very similar to view composers
        // However, they are executed immediately after the view is instantiated instead of waiting until the view is about to render. 

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

        //* Localizing Resource URIs
        Route::resourceVerbs(['create' => 'banaw','edit' => 'edit-koro']);
        
        //* Global Locale currency of all Number helper functions to use
        Number::useCurrency('USD');

        //* Macro Response:
        Response::macro('caps', function (string $value) {
            return Response::make(strtoupper($value));
        });
        // Now, use it: return response()->caps('foo');

        //* Vite Path Alias Macro:
        Vite::macro('image', fn (string $asset) => $this->asset("resources/images/{$asset}"));
        // Now, can use in blade: <img src="{{ Vite::image('logo.png') }}" alt="Laravel Logo">

        //* Asset Prefetching: (when we do vite config splitting)
        // In a typical Single Page Application (SPA), code splitting breaks your app into small chunks.
        // While this makes the initial load faster, it creates a "waterfall" effect where clicking a link triggers a network request for the next page's code, leading to a noticeable delay or a "loading" state.
        // Laravel’s Vite::prefetch solves this by downloading those chunks in the background before the user even clicks the link.
        // Ex: Load the main inventory list, Immediately start downloading the code for the "Edit Product," "Stock Management," and "Order History" views in the background.
        Vite::prefetch(concurrency: 3); // Assets will be prefetched with a maximum of 3 concurrent downloads on each page load
        Vite::prefetch(); // No concurrency limit if the application should download all assets at once
        Vite::prefetch(event: 'vite:prefetch'); // Rather than in page load, pefetch will happen when that listener call.

        //* Adding extra attribute in built <script> tag for our js
        Vite::useScriptTagAttributes(['async' => true]);
        
        // Conditionally add attribute:
        Vite::useScriptTagAttributes(fn (string $src, string $url, array|null $chunk, array|null $manifest) => [
            'data-turbo-track' => $src === 'resources/js/app.js' ? 'reload' : false
        ]);

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

    //* Facades: See app/Facades/Payment.php
}
