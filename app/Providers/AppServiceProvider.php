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

    //* Binding Dependency (First way)
    public $bindings = [
        PaymentProcessor::class => Bkash::class
    ] // So, here laravel is saying, when someone inject PaymentProcessor interface, give him a Bkash instance.

    public function register(): void
    {
        //* Binding Terminable middleware from same handled middleware
        // $this->app->singleton(TerminatingMiddleware::class);

        //* Binding Dependency (Second Way)
        $this->app->bind(PaymentProcessor::class, Bkash::class);

        //* Binding Dependency (Third Way- If class have extra config or constructor)
        $this->app->bind(PaymentProcessor::class, function(){
            return new Bkash('something-here-from-constructor');
        })
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
