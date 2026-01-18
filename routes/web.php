<?php

use App\Models\User;
use App\Enum\FileType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BasicController;

// web.php defines http  routes
// We can split this route file into multiple route file like admin.php, user.php
// You will find the implementation of Route facade in Router.php file, Route.php is the definition just.
Route::get('/', function () {
    // return view('welcome'); // Go to the views/welcome.blade.php
    // We can pass data to the view file:
    return view('welcome', ['name' => 'Abdul']);
});

// We can share data globally in all view blade file using View::share from AppServiceProvider's boot method.

// Using View Facade
Route::get('/home', function () {
    return View::first(['dashboard', 'welcome']);
}); // If dashboard.blade.php not found then render welcome blade file.
// View directory names should not contain the . character.

// Rather than array, we can pass data one by one as key value one by one:
return view('greeting')
       ->with('name', 'Victoria')
       ->with('occupation', 'Astronaut');

// If blade file in a directory, we can use dot notation:
// admin.pages.welcome

// Determining if View exists
if (View::exists('admin.profile')){}

// To chek if xdebug is installed and enabled. If we see a dashboard of xdebug, then it is enabled.
Route::get('/xdebug-check', function () {
    return xdebug_info();
});

Route::get('/dashboard-test', function () {
    // return 'Welcome';
    return [1, 2, 3]; // Array will automatically be converted in json format and show that way
});

Route::match(['get', 'post'], '/admin-test', function () {
    return 'It will match get or post request automatically based on the incoming request.';
});
Route::match(['get', 'post'], '/welcome', 'WelcomeController@index');

Route::any('/dashboard-test/admin-test', function () {
    return 'Any kind of HTTP request will be allowed for /user';
}); 
// We should always define routes with specific HTTP method before these match and any route.
// match and any route should always after everything. Ex- 404 error page for any page.

//* Fallback Route: will be executed when no other route matches the incoming request.
Route::fallback(function () {});

//* Redirect and View Routes
Route::redirect('/home', '/dashboard'); // If we go to the /home url, it will automatically go to the /dashboard url.
Route::redirect('/here', '/there', 404); //If 404 not defined, normally give 302 status code
Route::permanentRedirect('/here', '/there'); //301 Status Code
Route::view('/', 'welcome');

//* Encoded Forward Slash
Route::get('/search/{search}', function (string $search) {
    return $search;
})->where('search', '.*');

/*
|--------------------------------------------------------------------------
| Route Artisan Commands
|--------------------------------------------------------------------------
|
| 1. php artisan route:list
| 2. Display with Route Middleware: php artisan route:list -v, php artisan route:list -vv (expand middleware roups)
| 3. php artisan route:list --path=api
| 4. php artisan route:list --except-vendor
| 5. php artisan route:list --only-vendor
| 6. php artisan route:cache - when deploy to boost perfromance.
| 7. php artisan route:clear - to clear cache
| 8. Each time a request is made laravel has to load and register all loads, to boost performance- php artisan route:cache. It will be stored in a single file in bootstrap/cache/routes..php
*/

/*
|--------------------------------------------------------------------------
| HTTP Verbs
|--------------------------------------------------------------------------
|
| 1. Get: Request Data from the Server, Fetch Data
| 2. Post: Submit Data to the Server to Process like when we register
| 3. Put: Update a Resource. Replace the Entire Resource or Create another one
| 4. Patch: Partial Update
| 5. Delete: Removal of a Resource
| 6. Any: Respond to All Possible Methods
| 7. Options: It asks about communication options or capabilities available for specific URL: What can I do with
|    this URL?
| 8. Match: Specify an array of HTTP methods
| 9. We normally use Get and Post method, because HTML form, all browsers and server support them. Normally,
|    when we use API, then we can use other methods explicitly.
| 10. POST, PUT, PATCH, DELETE must define @csrf token in the form tag
*/

//* Route Parameters
Route::get('/transactions-test/{transactionId}', function($transactionId){
    return $transactionId; // The name of the variable $transactionId doesn't matter, laravel just check the serial. At first, which parameter come, then which
}); // So, route param will automatically be injected in callback or our controller in the serial we define it.

Route::get('/transactions-test/{transactionId}/files/{fileId}', function ($id, $file) {
    return $id . $file;
});

// When using route parameters in view routes Route::view()..
// Following parameters are reserved by Laravel and cannot be used: view, data, status, and headers.

//* Optional Parameter using ?, you have to give default value for optional parameter
Route::get('/report-test/{year}/{month?}', function ($year, $month = null) {
    return $year . $month;
});
// Instead of passing route parameter, we can pass query string parameter.
// Query string is generally usefull for filtering, sorting, paginating etc.
// You have to decide what for which. For example: year/month/day- 1997/11/5- Instead of route query string will be more suitable for it: report/975?year=2025&month=5

//* Dependency Injection: Laravel allows using any dependency in route
// Route parameters recommended to specify later after all dependency injection
// Here we first take Request dependency, then take route parameter $invoiceId. But its not the required rule, recommended for best practice.
Route::get('/invoice-test/{invoiceId}', function (Request $request, int $invoiceId) {
    // If we give string type, it will throw error for the parameter.
    // Laravel doesnt enable strict type for route parameter, so if we enable strict_types and give float value, it will still work.
    // But we can validate it using regular expression in where.
    $year = $request->get('year');
    $month = $request->get('month');
    return $year . $month . $invoiceId;
})->where('invoiceId', '[0-9]+'); 
// We can chain multiple where for other parameters also.
// or, instead of chaining, we can take all parameters as an array in where as key value pair.
// Another way: whereNumber(['transactionId', 'fileId'])
// Now, let'say we have another route with post method and validation will be same, we have to right again
// Instead of code duplicating, we can define the validation in AppServiceProvider's boot method like that:
// Route::pattern('transactionId', '[0-9]+');, Now dont need to use the where anymore, automatically validated from boot() of AppServiceProvider.
// More Methods to check: whereAlpha(), whereAlphaNumeric().
// whereIn('fileType', ['receipt', 'statement']): check if the fileType parameter stays in those values, otherwise throw 404 error.
// Instead of haing array in whereIn(), we can inject an Enum class also.
Route::get('/files/{fileType}', function (FileType $fileType) {
    return $fileType->value;
    // Now, we dont need whereIn anymore, because enum check type automatically.
    // and laravel implicitely resolve backed enum when it resolve a route. You can check it in ImplicitRouteBinding.php's resolveForRoute() method.
});

//* Using Method from Controller Class
Route::get('/home-test', [BasicController::class, "index"]);
Route::get('/home-test', 'BasicController@index');

//* Grouping:
// But this is not a perfect way to define a route.
// Rather than calling a closure, we should pass a controller class
// php artisan make:controller TransactionController
Route::prefix('transactions')->group(function () {
    Route::controller(BasicController::class)->group(function () {
        // We can give a name for each route, route name must be unique.
        // If name is not unique, and we call by a name which has multiple routes, last route will be called.
        Route::get('/', 'index')->name('transactions');
        Route::get('/create', 'create');
        Route::get('/{transactionId}', 'show')->name('transaction');
        Route::post('/', 'store');

        // We should define /create at first, then /{transactionId} or validate the transactionId withNumber
        // If we do not do it then create will be traeted as a transactionId, then that route will be called. 

        // Single Action Controller:
        // When a controlle has just one action, or one method- thats called invokable or single action controller
        // Typically it uses php's __invoke() magic method behind the scene
        // Creating: php artisan make:controller ProcessTransactionController --invokable
        // We will get __invoke with Request dependency automatically.
        Route::get('/{transactionId}/process', InvokableController::class); // we can pass '__invoke' as second argument, but not passing or not defining like [] this will work also.
        // In real application invokable class will always be post request
    });

    // Route Name can be also more creative to differ conflick like transaction.show 
    // or we can create a group like Route::name('transactions.')
    // We can chain the grouping also like Route::prefix()->name()->controller()->group()
    // or, we can make another route file called transaction.php. and register it in bootstrap/app.php like this in withRouting:
    // then:  function() {
    //     Route::prefix('transactions')
    //           ->name('transactions.')
    //           ->group(base_path(routes/transaction.php)); - base_path will generate fully qualified path for that file.
    // Now, we can just define routes in transactions.php or any extra grouping if need
    // }
    // or, complete control using 'using'
    // using: function () {
    //     Route::middleware('api')
    //         ->prefix('api')
    //         ->group(base_path('routes/api.php'));
    //     Route::middleware('web')
    //         ->group(base_path('routes/web.php'));
    // },
    // Dont go crazy with routing, prefixing, or grouping. If you over-engineer, sometimes it will be hard to understand.
    // Do not create separate route file unless your web.php or api.php grows more.
    // Do not after two or three levels gouping. Don't make harder for your team or yourself to understand it later.
});

Route::middleware(['admin'])->group(function(){});
Route::controller(BasicController::class)->group(function(){});
Route::domain('{account}.example.com')->group(function () {}); //Sub Domain Routing
Route::prefix('admin')->group(function(){});
Route::name('admin.')->group(function(){}); //domain(), resource(), apiResource()
// Route::middleware()->name()->prefix()->controller()->group(function(){});
// Organizing Routes: Public Route, Private Route as middleware grouped.
// If controllers in the separate folder, that can be called as namespace.

//* Soft Delete
Route::get('/', function (){})->withTrashed();

//* Using group()
//* Resource route: Users can create, read, update, or delete resources.
// Laravel resource routing assigns the typical create, read, update, and delete ("CRUD") routes to a controller with a single line of code. 
// php artisan make:controller PhotoController --resource
// Type hint a model instance: php artisan make:controller PhotoController --model=Photo --resource
// Generating Form Requests: php artisan make:controller PhotoController --model=Photo --resource --requests
Route::group(['middleware'=>'auth'], function(){
    Route::group(['middleware'=>'admin', 'prefix'=> 'admin', 'namespace' => 'Admin', 'as' => 'admin.'], function(){
        //Partial Resource Controller
        Route::resource('admin', BasicController::class)->except('edit');
        //->only()
        // Extra Method must be defined before the resource controller to work
        // Partisal Resource Route: when we use only or except.
    });

    Route::group(['middleware'=>'user', 'prefix'=> 'user', 'namespace' => 'User', 'as' => 'user.'], function(){
        //Partial Resource Controller
        Route::resource('user', BasicController::class)->except('edit');
    });
});

// By default resources will have name:
// photos.index, photos.create, photos.store, photos.show, photos.update, photos.edit, photos.destroy
// But we can override any name:
Route::resource('photos', PhotoController::class)->names([
    'create' => 'photos.build'
]);

// Multiple resources at once:
Route::resources([
    'photos' => PhotoController::class,
    'posts' => PostController::class,
])->missing(function (Request $request) {
        return Redirect::route('photos.index');
})->withTrashed(['show']); // Route::softDeletableResources
// Routes will be: /photos(GET), /photos/create, /photos(POST), /photos/{photo} (GET), /photos/{photo}/edit,  /photos/{photo} (PUT/PATCH), /photos/{photo} (DELETE) 
// 404 HTTP response if an implicitly bound resource model is not found. 
// Can customize this behavior by calling the missing method. 
// Calling withTrashed with no arguments will allow soft deleted models for the show, edit, and update resource routes. 

// API Resource Route: Does not include the create or edit methods
// php artisan make:controller PhotoController --api
Route::apiResource('photos', PhotoController::class); // Multiple: Route::apiResources([])

// Nested Resource:
Route::resource('photos.comments', PhotoCommentController::class); // Route: /photos/{photo}/comments/{comment}

// Shallow Nesting:
// When using unique identifiers such as auto-incrementing primary keys to identify your models in URI segments
Route::resource('photos.comments', CommentController::class)->shallow();
// Routes: /photos/{photo}/comments, /photos/{photo}/comments/create, /photos/{photo}/comments, /comments/{comment}, /comments/{comment}/edit, /comments/{comment}, /comments/{comment}
// Names: photos.comments.index, photos.comments.create, photos.comments.store, comments.show, comments.edit, comments.update, comments.destroy.  

// By default, Route::resource will create the route parameters for your resource routes
// But we can override:
Route::resource('users', AdminUserController::class)->parameters([
'users' => 'admin_user' // Route: /users/{admin_user}
]);

// Localize URIs:
// We may want to change from create to banaw.
// Do this in ApplicationServiceProvider's boot method.

// If need to add additional routes to a resource controller beyond the default set of resource routes:
// should define those routes before your call to the Route::resource method to consider precedence.

// Singleton Resource Controllers:
// A user may not have more than one "profile"
// An image may have a single "thumbnail"
// These resources are called "singleton resources", meaning one and only one instance of the resource may exist.
Route::singleton('profile', ProfileController::class); // Routes: /profile(GET), /profile/edit, /profile(PUT/PATCH)
Route::singleton('photos.thumbnail', ThumbnailController::class); // Routes: /photos/{photo}/thumbnail, /photos/{photo}/thumbnail/edit, /photos/{photo}/thumbnail(PUT/PATCH) 
Route::singleton('photos.thumbnail', ThumbnailController::class)->creatable(); // create and delete will be added also.
// ->destroyable(): only delete route
// Same goes for: apiSingleton

// Applying middleware for specific methods:
Route::resource('users', UserController::class)->middlewareFor('show', 'auth');
//  ->middlewareFor(['show', 'update'], 'auth'), ->middlewareFor(['show', 'update'], ['auth', 'verified'])
//  ->withoutMiddlewareFor('index', ['auth', 'verified'])

/*
|--------------------------------------------------------------------------
| *Route Model Binding
|--------------------------------------------------------------------------
|
| 1. Short Way to Find By ID. Typically Used in Edit or Show Method
| 2. Instead of Finding ID, Just Pass argument show(User $user), edit(User $user)
| 3. Explicit Binding in boot Method of Route Service Provider
| 4. We can conditionally make our own customized route model binding like find this id if condition
*/
//Implicit Binding: automatically bind where variable name compares with model name
Route::get('users/{user}', function(User $user){}); //$user and {user} matches
// Typically, implicit model binding will not retrieve models that have been soft deleted. Use ->withTrashed().
Route::get('users/{user:slug}', function(User $user){}); //Rather than ID - slug. If Define in Model, don't need :slug here.
// or,  may override the getRouteKeyName method on the Eloquent model. 
// public function getRouteKeyName(): string
// {
//     return 'slug';
// } // Now, model will be found by slug, not by id.
Route::get('users/{user}/posts/{post:slug}', function(User $user, Post $post){
    // Don't look for the post with ID 432; look for the post where the slug column in the database matches 'laravel-11-released-today
}); 
// Now, It will be assumed that the User model has a relationship named post 
Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) { 
    // Now even with IDs, it checks ownership
})->scopeBindings();
Route::get('/categories/{category}/items/{item}', function (Category $category, Item $item) {
    // Load the item even if it's not in this category
})->withoutScopedBindings();

// Scoped bindings in Laravel limit data retrieval to a specific context
// for instance, getting posts that belong to a particular user. With ->scopeBindings()
// Laravel only finds a Post that is related to the provided User, otherwise, it returns a 404 error. 
// Without scoped bindings (without ->scopeBindings()), Laravel will fetch a Post based on its ID across all posts, regardless of the user it's related to. It broadens the search to the entire dataset, ignoring the user-post relationship.
//In Model
// public function resolveRouteBinding($value, $field = null)
// {
//         return $this->where('slug', $value)->firstOrFail();
// }
// Customize missing model behaviour using ->missing(function(Request $request)){return Redirect::route('location.index')}
// We can use implicit enum binding also.
// We can do explicit route binding in AppServiceProvider's boot() method above using Route::model()

// Scoping Resource Route:
Route::resource('photos.comments', PhotoCommentController::class)->scoped(['comment' => 'slug']);
// /photos/{photo}/comments/{comment:slug}

//* Rate Limiting
// Restricts amount of traffic for a given route/routes
// Rate limiters may be defined within the boot method of your application's App\Providers\AppServiceProvider class, check it.
// Assign Rate Limiter to Route
Route::middleware(['throttle:uploads'])->group(function(){});
// If use redis, you may wish to instruct Laravel to use Redis to manage rate limiting.
// Use the throttleWithRedis method in your application's bootstrap/app.php file.

//* Form Security:
// Use @csrf under form in blade file.
// In spa application, you will see in frontend:
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'; ...
// Those two lines tell Axios to handle the CSRF token for you.

// HTML forms do not support PUT, PATCH, or DELETE actions.
// So, when defining those, you will need to add a hidden _method field to the form.
// <form action="/example" method="POST">input type="hidden" name="_method" value="PUT">
// or, use @method('PUT') @csrf


//* Accessing Current Route:
$route  = Route::current(); // Illuminate\Routing\Route
$name   = Route::currentRouteName(); // string
$action = Route::currentRouteAction(); // string
//  if ($request->route()->named('profile')) {}

//* cors configuration:
//* Though laravel do everything, but If you need to change cors configuration:
//  php artisan config:publish cors

//* API Route:
// Installing api Routes: php artisan install:api
// The install:api command installs Laravel Sanctum, which provides a robust, yet simple API token authentication guard which can be used to authenticate third-party API consumers, SPAs, or mobile applications. 
// Also creates the routes/api.php file.
// You can change prefix in app.php in withRouting: apiPrefix: 'api/admin'

//* Middleware:
// Middleware provide a convenient mechanism for inspecting and filtering HTTP requests entering your application.
// It sits between your route and controller.
// Exmp: Auth middleware redirect to login or admin panel
// Logging middleware log all incoming requests.
// If we inspect withMiddleware in ApplicationBuilder.php inside the framework, we see it is adding global middlewares, setting middleware groups and aliases.
// If we inspect the getMiddleware method in Middleware.php, there are some global middlewares:
// InvokeDifferCallbacks, TrustHosts, TrustProxies, HandleCors, PreventRequestDuringMaintenance, ValidatePostSize, TrimString, ConvertEmptyStringToNull
// If we see getMiddlewareGroups method, for web, we have EncryptCookies, AddQueuedCookiesToResponse, StartSession, ShareErrorFromSession, ValidateCsrfToken, SubstitueBindings middlewares
// for api, EnsureFrontendRequestsAreStsteful, SubstituteBindings.
// Then its mapping middlewares and finally merging with prepen and append middlewares.
// So, if we want to register a middleware we can prepend or append in bootstrap/app.php
// Making a Middleware in app/http/Middleware: php artisan make:middleware EnsureTokenIsValid.

//* Binding Dependency using attribute
Route::get('/user', function (#[CurrentUser] User $user) {
    return $user; // User class points to the CurrentUser's instance
})->middleware('auth');