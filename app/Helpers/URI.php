<?php
// Generates a fluent URI instance for the given URI
uri('https://example.com')->withPath('/users')->withQuery(['page' => 1]);
uri([UserController::class, 'show'], ['user' => $user]); // create a Uri instance for the controller method's route path
uri(UserIndexController::class); // for invokable controller

// Uri class provides a convenient and fluent interface for creating and manipulating URIs. 
use Illuminate\Support\Uri;
use Illuminate\Routing\Route;
// Generate a URI instance from the given string...
$uri = Uri::of('https://example.com/path');

// Generate URI instances to paths, named routes, or controller actions...
$uri = Uri::to('/dashboard');
$uri = Uri::route('users.show', ['user' => 1]);
$uri = Uri::signedRoute('users.show', ['user' => 1]);
$uri = Uri::temporarySignedRoute('user.index', now()->plus(minutes: 5));
$uri = Uri::action([UserController::class, 'index']);
$uri = Uri::action(InvokableController::class);

// In Laravel, Signed URLs are special links that have a "signature" (a cryptographic hash) appended to the query string.
// Generates: https://warehouse.com/users/1?signature=a7b8c9...

$uri = Uri::of('https://warehouse.com/products/desk?stock=5#dimensions');

echo $uri->scheme();   // Output: "https"
echo $uri->host();     // Output: "warehouse.com"
echo $uri->path();     // Output: "/products/desk"
echo $uri->fragment(); // Output: "dimensions"
$segments = $uri->pathSegments();
$query = $uri->query();
$port = $uri->port();

// Generate a URI instance from the current request URL...
$uri = $request->uri();

// Modify URI:
$uri = Uri::of('https://example.com')
           ->withScheme('http')
           ->withHost('test.com')
           ->withPort(8000)
           ->withPath('/users')
           ->withQuery(['page' => 2])
           ->withFragment('section-1');

$uri = $uri->withQuery(['sort' => 'name']);
$uri = $uri->withQueryIfMissing(['page' => 1]);
$uri = $uri->replaceQuery(['page' => 1]);
$uri = $uri->pushOntoQuery('filter', ['active', 'pending']);
$uri = $uri->withoutQuery(['page']); // Remove parameters

return $uri->redirect();

// or imply return the Uri instance from a route or controller action, which will automatically generate a redirect response to the returned URI
Route::get('/redirect', function () {
    return Uri::to('/index')
              ->withQuery(['sort' => 'name']);
});

// URI (Uniform Resource Identifier): This is the "parent" category. It is a string of characters used to identify any resource (a name, a location, or both).
// URL (Uniform Resource Locator): This is a specific type of URI. It not only identifies the resource but also tells you how to get there (by specifying the protocol like http or ftp).