<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthController {
    // Guards- Session and Cookies, Providers- Retrive Auth data using eloquent and query builder.
    // Config file: auth.php.

    // By default, laravel use eloquent User model to authenticate, We can use database driver also. Can use different table also.
    // users table should have password column (at least 60 characters), nullable remember_token column for forget password option which is by default provided by laravel.

    // A Cookie is a tiny piece of data stored on your browser (the client side). Think of it like a physical coat-check ticket.
    // When you visit a site, the server sends a cookie to your browser. Your browser saves it and automatically sends it back every time you request a new page from that site.
    // Use case: Remembering your "Dark Mode" preference or items in a shopping cart.
    // A Session is a way to store data about a user on the server side.
    // The cookie is the key, and the session is the locked cabinet on the server that holds your specific info (like your bank balance or user profile).
    // An API Token is a unique string of characters used to identify a user or application to a server. It’s like a digital badge or a VIP pass.
    // Unlike cookies (which are handled automatically by browsers), tokens are usually sent manually in the "Header" of a request.
    // OAuth2 is not a piece of code; it is a protocol (a set of rules) that allows a website or app to access resources from another app without you giving away your password.
    // While a traditional API token is like giving someone a copy of your house key, OAuth2 is like giving them a smart-lock code that only works for the kitchen and expires in two hours.
    // OAuth2 benefits: Granular access, no password sharing, token expiration and refreshing, 
    
    // Operation: User provided credentials, if right credentials, app will store info about the user in session.
    // A cookie issued to the browser contains the session Id so that subsequent request can be authenticated.
    // When a remote service needs to authenticate to access an API, cookies are not typically used for authentication because there is no web browser.
    // Instead, the remote service sends an API token to the API on each request.
    // Laravel provides built-in-browser cookie based authentication system.
    // Laravel also provides two optional packages for API tokens authenticating- Passport and Sanctum.
    // Many applications will use both Laravel's built-in cookie based authentication services and one of Laravel's API authentication packages.

    // Passport is an OAuth2 authentication provider, offering a variety of OAuth2 "grant types" which allow you to issue various types of tokens. Complex and Confused.
    // Simpler, more streamlined authentication package that could handle both first-party web requests from a web browser and API requests via tokens.
    // Laravel Sanctum is a hybrid web / API authentication package that can manage your application's entire authentication process.
    // Sanctum first check, If the request is not being authenticated via a session cookie, Sanctum will inspect the request for an API token.
    // In general, Sanctum should be preferred when possible since it is a simple, complete solution for API authentication, SPA authentication, and mobile authentication, including support for "scopes" or "abilities".
    // When using Sanctum, you will either need to manually implement your own backend authentication routes or utilize Laravel Fortify as a headless authentication backend service that provides routes and controllers for features such as registration, password reset, email verification, and more.

    // At first, we have to install a starter kit.
    // If you are using one of our application starter kits, rate limiting will automatically be applied to login attempts. 
    // By default, the user will not be able to login for one minute if they fail to provide the correct credentials after several attempts.

    public function authentication(Request $request){
        // Attach the middleware in route: ->middleware('auth')
        // Specifying guard: ->middleware('auth:admin')
        // Redirect authenticated and unauthenticated user from bootsrap/app.php.

        // Add Custom Guard within a service provider's boot method. See AppServiceProvider.

        //* Retrive authenticated user:
        Auth::user();
        Auth::id();

        // Can retrive by type hinting Request object:
        $request->user();

        // Check if authenticated.
        if (Auth::check()) {}

        // Manual authentication without starte kit:
        // You should not hash the incoming request's password value, since the framework will automatically hash the value before comparing it to the hashed password in the database. 
        // In auth config, loquent user provider is specified and it is instructed to use the App\Models\User model when retrieving users.
        // You may change these values within your configuration file based on the needs of your application. 
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
            // The intended method provided by Laravel's redirector will redirect the user to the URL they were attempting to access before being intercepted by the authentication middleware.
            // Additional Condition: if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1, fn (Builder $query) => $query->has('activeSubscription'),]))
            // Do something when authenticate: if (Auth::attemptWhen(['email' => $email,'password' => $password,], function (User $user) {return $user->isNotBanned();}))
            // Accessing specific guard: if (Auth::guard('admin')->attempt($credentials))
            // Remember me: if (Auth::attempt(['email' => $email, 'password' => $password], $rememberingCondition))
        }
        return back()->withErrors([''])->onlyInput('email');

        // If the user authenticated using remember me cookie: if (Auth::viaRemember())
        
        // Set an existing user instance as the currently authenticated user: Auth::login($user).
        // Remember me functionality is desired: Auth::login($user, $remember = true);
        // Auth::guard('admin')->login($user);
        // Auth::loginUsingId(1); Auth::loginUsingId(1, remember: true);
        // Authenticate for a single request, no sessions or cookies will be utilized: if (Auth::once($credentials)) {}

        // Stateless HTTP basic authentication:
        // Define a middleware AuthenticateOnceWithBasicAuth. Auth::onceBasic()
        // Use that middleware in route.

        //* Logging Out:
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');

        //* Invalidating session on other devices:
        // Useful when a user is changing or updating their password and you would like to invalidate sessions on other devices while keeping the current device authenticated.
        // Add this auth.session middleware in route: middleware(['auth', 'auth.session'])
        Auth::logoutOtherDevices($currentPassword);

        //* Password Confirmation:
        // After confirming their password, a user will not be asked to confirm their password again for three hours by default.
        // Can change the time in auth config: password_timeout.
        // Two Routes- For view, for confirm.
        if (! Hash::check($request->password, $request->user()->password)){
            return back()->withErrors([]);
        }
        $request->session()->passwordConfirmed();
        return redirect()->intended();
        // In confirmation route, can use throttle: ->middleware(['auth', 'throttle:6,1'])
        // passwordConfirmed method will set a timestamp in the user's session that Laravel can use to determine when the user last confirmed their password.
        // Protecting Routes: Route::get('/settings', function () {})->middleware(['password.confirm']);

        //* Events:
        // Registered, Attempting, Authenticated, Login, Failed, Validated, Verified, Logout, CurrentDeviceLogout, OtherDeviceLogout, Lockout, PasswordReset, PasswordResetLinkSent.

        //* Encryption:
        // Laravel's encryption services provide a simple, convenient interface for encrypting and decrypting text via OpenSSL using AES-256 and AES-128 encryption. 
        // php artisan key:generate command to generate this variable's value since the key:generate command will use PHP's secure random bytes generator to build a cryptographically secure key for your application. 
        // If you change your application's encryption key, all authenticated user sessions will be logged out of your application. 
        // To mitigate this issue, Laravel allows you to list your previous encryption keys in your application's APP_PREVIOUS_KEYS environment variable. 
        Crypt::encryptString($request->token); // Encrypt anything.
        try{
            $decrypted = Crypt::decryptString($encryptedValue);
        }
        catch(DecryptException $e){}

        //* Hashing:
        // The Laravel Hash facade provides secure Bcrypt and Argon2 hashing for storing user passwords.
        // Starter kits use bcrypt by default.
        // Can customize hash driver HASH_DRIVER env variable.
        // Bcrypt is a great choice for hashing passwords because hash can be increased as hardware power increases.
        // Slow hashing is good, he longer an algorithm takes to hash a password, the longer it takes malicious users to generate "rainbow tables" of all possible string hash values that may be used in brute force attacks against applications.
        // php artisan config:publish hashing
        // The hashing algorithm is not expected to change and different algorithms can be an indication of a malicious attack. 
        // If we change the hashing algorithm, Hash::check will throw RunTimeException.
        // If we need multiple hashing algorithm, make: HASH_VERIFY=false in env.
        Hash::make($request->newPassword);
        Hash::make('password', ['rounds' => 12,]); // If use bcrypt worker.
        Hash::make('password', ['memory' => 1024,'time' => 2,'threads' => 2,]); // If use Argon2 worker.
        if (Hash::check('plain-text', $hashedPassword)) {}
        if (Hash::needsRehash($hashed)) {Hash::make('plain-text');} // If work factor changed after a hashing.

    }
}