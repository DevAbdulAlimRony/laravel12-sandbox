<?php
// URL generation: helpful when building links in your templates and API responses, or when generating redirect responses to another part of your application.
// Generates a fully qualified URL to the given path
url('user/profile'); // http://example.com/user/profile
url()->current(); // Get the current URL without the query string...
// Can use Facade also:
use Illuminate\Support\Facades\URL;
echo URL::current();
url()->full(); // Get the current URL including the query string...
url()->previous(); // Get the full URL for the previous request...
echo url()->previousPath(); // Get the path for the previous request...
// $request->session()->previousUri(); // Previous uri via the session
// $request->session()->previousRoute(); // Previous route name.
// echo route('post.show', ['post' => $post]) // Using route key of eloquent models.

url()->query('/posts', ['search' => 'Laravel']); // https://example.com/posts?search=Laravel
url()->query('/posts?sort=latest', ['search' => 'Laravel']); // http://example.com/posts?sort=latest&search=Laravel
url()->query('/posts?sort=latest', ['sort' => 'oldest']); // http://example.com/posts?sort=oldest
url()->query('/posts', ['columns' => ['title', 'body']]); // http://example.com/posts?columns%5B0%5D=title&columns%5B1%5D=body
echo urldecode($url); // http://example.com/posts?columns[0]=title&columns[1]=body

// Generates a fully qualified HTTPS URL to the given path. 
secure_url('user/profile');
secure_url('user/profile', [1]);

// Generates a URL for the given controller's action
action([TransactionTestController::class, 'index']);
action([UserController::class, 'profile'], ['id' => 1]); // with route parameter

// Generates url for assets:
asset('img/photo.jpg');
// We can take ASSET_URL env if we use any cdn
// In env: ASSET_URL=http://example.com/assets
asset('img/photo.jpg'); // http://example.com/assets/img/photo.jpg

// Generates a URL for an asset using HTTPS
secure_asset('img/photo.jpg');

// Generates a redirect HTTP response for a given controller action
to_action([UserController::class, 'show'], ['user' => 1]);
to_action(
    [UserController::class, 'show'],
    ['user' => 1],
    302, // http status code
    ['X-Framework' => 'Laravel']
);

// Generates  redirect HTTP response for a given named route
to_route('users.show', ['user' => 1]); // also can pass status code and X- ...

// Generates a URL for a given named route:
route('route.name'); // absolute url
route('route.name', ['id' => 1]);
route('route.name', ['id' => 1], false); // relative url

// Signed URL:
// Without a signature, an attacker could simply change the user ID in the URL (e.g., changing user=1 to user=2) to unsubscribe other people.
// Signed URLs are especially useful for routes that are publicly accessible yet need a layer of protection against URL manipulation.
// These URLs have a "signature" hash appended to the query string which allows Laravel to verify that the URL has not been modified since it was created.
// Laravel appends a signature hash to the URL. If someone changes the user ID, the hash will no longer match the data, and the application will return a 403 Forbidden error.
use Illuminate\Support\Facades\URL;
URL::signedRoute('unsubscribe', ['user' => 1]);
URL::signedRoute('unsubscribe', ['user' => 1], absolute: false);
URL::temporarySignedRoute(
    'unsubscribe', now()->plus(minutes: 30), ['user' => 1]
); // Expire after 30 minutes.
// It limits the window of opportunity if an email is intercepted or forwarded.
// You don’t have to manually write code to check if a link is "old."

// We can check if request has valid signature in our controller:
if (! $request->hasValidSignature()) {
        abort(401);
}
// you can specify request query parameters that should be ignored if that presents from frontend extra info:
if (! $request->hasValidSignatureWhileIgnoring(['page', 'order'])) {
    abort(401);
}
// But, instead of checking again and again, we can use ->middleware('signed') in the route or ->middleware('signed:relative') if url doesnt contain domain.

// When visits a signed url after expiration, it will recieve a generic 403 error. We can customize this InvalidSignatureException in bootstrap/app.php.

<?php
// Laravel's Uri class provides a convenient and fluent interface for creating and manipulating URIs via objects. 
// This class wraps the functionality provided by the underlying League URI package and integrates seamlessly with Laravel's routing system.

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

// Sometimes, we need to add a default directory for each route like blog/bn/, blog/en/
// Call URL::defaults in route middleware, class SetDefaultLocaleForUrls...... After setting it, no need to call it again and again in route.
// But it can create conflict with model bindings, set middleware priority in bootstrap/app.php