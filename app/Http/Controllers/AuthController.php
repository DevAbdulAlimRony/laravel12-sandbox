<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

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

        //* Registering:
        // Validate inputs, make password hash then,
        Auth::login($validatedUser);
        $request->session()->regenerate();

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

        //* Email Verification:
        // See all routes in web.php.
        // Model should implements MustVerifyEmail interface.
        // Once this interface has been added to your model, newly registered users will automatically be sent an email containing an email verification link.
        // users table must contain email_verified_at column.
        // Three routes: Display a should click verification link notice, when link clicked, resend link.
        // The route that returns the email verification notice should be named verification.notice with ->middleware('auth').
        // Because verified middleware included with Laravel will automatically redirect to this route name if a user has not verified their email address.
        // Verification route should be named verification.verify with ->middleware(['auth', 'signed']).
        // function (EmailVerificationRequest $request){$request->fulfill();}
        // fulfill method of EmailVerificationRequest will call the markEmailAsVerified method and dispatch the Verified event.
        // Sometimes a user may misplace or accidentally delete the email address verification email. Need resending the link.
        // Protect your routes using >middleware(['auth', 'verified']), this will use EnsureEmailIsVerified middleware.
        // If an unverified user attempts to access a route that has been assigned this middleware, they will automatically be redirected to the verification.notice named route. 
        // See VerifyEmail:: for Verification email customization in AppServiceprovider's boot method.
        //  From email verification link, if we change xpiration value and go to the link it will take us to 403  Invalid signature page of laravel.
        // Use throttle middleware for email veryfy and resend route.
        // showPasswordResetForm(#[SensitiveParameter] string $token)


        //* Resetting Forgotten Passwords:
        // Password reset logic of laravel is in PasswordBroker.php
        // By default password reset driver is database in auth config file. Can use cache driver also.
        // users table should have a password reset token column.
        // Model should use Notifiable and CanResetPassword trait.
        // Model class should implements CanResetPassword interface.
        // Should configure trust hosts in apache or nginx srver or in bootsrap/app.php- trustHosts middleware method
        // See Routes in we.php.
        // Delete password reset tokens which are expired: php artisan auth:clear-resets
        // Automate this process: Schedule::command('auth:clear-resets')->everyFifteenMinutes();
        // Reset link customization: See AppServiceProvider's boot()
        // Customize reset email: implement sendPasswordResetNotification($token): void , method in model.
        // For password reset routes, laravel by default provide 60 seconds throttle, but to make more secure, we should use email based and ip based RateLimiter for password link request and new password submission in boot method.
        // For example, maybe 3 requests per hour by the email and 10 request per hour by the ip.
        // In production , we can use infrastructure level rate limiting for more security like cloudfare's rate limiting rules that can block suspiscious traffic or nginx  rate limiting on web server level.


        //* Session Fixation attack:
        // A Session Fixation attack is a type of web security exploit where an attacker tricks a victim into using a specific, known session ID.
        // Unlike session hijacking—where an attacker steals an active session key after a user logs in—fixation happens when the attacker "hands" a valid session identifier to the user before they even authenticate.
        // To prevent it: make SESSION_SECURE_COOKIE as true in env file.

        //* Rate Limitting:
        // In login, we should use RateLimiter::tooManyAttempts with a throttle key.
        // For throttle key, we can use the combination of user's provided email and user's ip.
        // If login succeed, clear the rate limitter with the key.
        // If login failed, hit the the rate limitter again.
        // But user can use different email, so we should use multiple rate limitter.
        //  For example, a user tried with an email 5 times, and too many attempts came. But the user from same ip try again with an new email, laravel will allow that. 
        // Solution: Rather than combination of email and ip tr=hrottle key, one RateLimiter for email, and another rate limitter for ip. maybe max 100 times for ip, max 5 for email.
        // Rather than directly in controller, call RateLimiter::for in boot method and use as middleware to the route like throttle:login- now we dont need to clear the throttle key in controller.
        // If attempt in max, it will go to the 429 error page. But we can use ->response with RateLimiter to flash the ValidationException.
        // Validation exception will not flush password, current password and password_confirmation, which is good.
        // But it wont take 429 status code which is not standard coding.
        // So we can use back()->withErrors()->withInput($request->except($password)), also we can check if $request->expectsJson() for ajax request if the just return response()->json() with 429 status code.

        //* Timer Attack:
        // Laravel's Login::attempt() automatically prevents timer attack using timebox. 
        // It always take 2000ms if login failed. If suuceed, immediately login so that attacker cant measure response time and cant make list of valid email.

        //* Password Reset Poisioning:
        // To prevent we can configure inserver if not shared hosting.
        // In app level, we can use trustHosts middleware in bootsrap/app.php: at: ['yourdomain.com', 'www.yourdomain.com', 'staging,yourdomain.com'].
        // Sub domain automatically included. subdomain argument can be false by passing.
    }

    public function session(Request $request){
        // As HTTP driven apps are staeless, session provides a way to store info about the user across multiple requests.
        //* Drivers:
        // file: stored in storage/framework/sessions
        // cookie: stored in secure encrypted cookies
        // database: stored in a relational database. php artisan make:session-table, if alreday not having.
        // redis or memcached: fast, cache-based stores.
        // dynamodb: stored in AWS DynamoDB
        // array: stored in a PHP array. Used during testing.

        //* Accessing Session:
        $request->session()->all();
        $request->session()->only(['username', 'email']);
        $request->session()->except(['username', 'email']);
        $request->session()->get('key'); // Using Request instance
        $request->session()->get('key', 'default');
        $request->session()->get('key', function () {return 'default';});

        session('key'); // Using global helper.
        session('key', 'default');
        // Instance or helper both are testable via the assertSessionhas method.

        //* Checking Boolean:
        $request->session()->has('users'); // Checks if the key exists and if the value is not null. If not, return null.
        $request->session()->exists('users') // Checks if the key exist.

        //* Store Session:
        $request->session()->put('key', 'value');
        session(['key' => 'value']);
        $request->session()->push('user.teams', 'developers'); // Push into array session.

        //* Integer session
        $request->session()->increment('count');
        $request->session()->increment('count', $incrementBy = 2);
        // Same goes for decreament

        //* Flash Data:
        //  store items in the session for the next request.
        // Flash data is primarily useful for short-lived status messages
        // After the subsequent HTTP request, the flashed data will be deleted.
        $request->session()->flash('status', 'Task was successful!');
        $request->session()->now('status', 'Task was successful!'); // Only for the current request.
        $request->session()->reflash();
        $request->session()->keep(['username', 'email']); // reflash only specific keys.

        //* Delete
        $request->session()->pull('key', 'default'); // Retrive and delete the session.
        $request->session()->forget('name'); // Forget a single key
        $request->session()->forget(['name', 'status']); // Forget multi keys
        $request->session()->flush(); // Remove all from the session.

        //* Security:
        // To prevent malicious users from exploiting a session fixation attack , regenerate the session id.
        // Laravel starter kits and fortify automatically regenerate session id during authentication.
        $request->session()->regenerate();
        $request->session()->invalidate(); // Regenerate and remove all data.

        //* Cache for the current session or user:
        // The session cache is perfect for storing temporary, user-specific data that you want to persist across multiple requests within the same session, but don't need to store permanently.
        $request->session()->cache()->put(
            'discount', 10, now()->plus(minutes: 5)
        );
        $request->session()->cache()->get('discount'); // Access cached session.

        //* Session Blocking: Implemented in Route.

        //* We can add custom session driver using implements \SessionHandlerInterface
        // Methods will be: open($savePath, $sessionName), close(), read($sessionId), write($sessionId, $data),  destroy($sessionId), gc($lifetime) 
        // The gc method should destroy all session data that is older than the given $lifetime.
        // Now we can add that driver in SessionServiceProvider's boot method using Session::extend()
        // Finally, add the driver in end  and use it.
    }

    public function authorization(){
        // Laravel provides two primary ways of authorizing actions: gates and policies.
        // Gates provide a simple, closure-based approach to authorization while policies, like controllers, group logic around a particular model or resource. 
        // Gates are most applicable to actions that are not related to any model or resource, such as viewing an administrator dashboard.
        // Policies should be used when you wish to authorize an action for a particular model or resource.

        // Use Gate when general thing like can this user create a category? Here we are talking about all model in general.
        // Use policy when for specific model, Can this user edit a specific model or a model? Here we are talking about all model one by one specifically.

        // Typically, gates are defined within the boot method of the App\Providers\AppServiceProvider class using the Gate facade. 
        // See the service provider. Then use it:
        if(! Gate::allows('update-post', $post)){
            abort(403);
            // Dont need to pass auth user, laravel will automatically find it.
        }

        // Check if user other than currently authenticated user are authorized:
        if(Gate::forUser($user)->allows('update-post', $post)){}
        if(Gate::forUser($user)->denies('update-post', $post)){}

        // Multiple authorization at a time:
        if (Gate::any(['update-post', 'delete-post'], $post)) {}
        if (Gate::none(['update-post', 'delete-post'], $post)) {}

        Gate::authorize('update-post', $post); // Throw AuthorizationException if not allowed.
        if (Gate::check('create-post', [$category, $pinned])) {} // Additional context pinned.
        
        $response = Gate::inspect('edit-settings');
        if($response->allowed()){}
        else{echo $response->messgae()}

        // Inline Authorization (Without defining gate in ServiceProvider, directly here quickly):
        Gate::allowIf(fn (User $user) => $user->isAdministrator());
        Gate::denyIf(fn (User $user) => $user->banned());
        // If no user currently authenticated, automatically throw an AuthorizationException.

        //* Policies:
        // php artisan make:policy PostPolicy (app/Policies - will create empty class)
        // php artisan make:policy PostPolicy --model=Post (will create class with viewing, creating, updating, and deleting the resource)
        // The policy name must match the model name and have a Policy suffix.
        // If you would like to define your own policy discovery logic, you may register a custom policy discovery callback using the Gate::guessPolicyNamesUsing.
        // If we dont want automatic discovery, we can manually register policy in AppServiceProvider, or we can define in model as UsePolicy attribute. See flight model.
        if ($request->user()->cannot('update', $post)) {
            abort(403);
        }
        // Can use Gate facade also: Gate::authorize('update', $post)
        // create method do not need model, can call just class: $request->user()->cannot('create', Post::class),  Gate::authorize('create', Post::class).
        // If a policy is registered for the given model, the can method will automatically call the appropriate policy and return the boolean result.
        //  If no policy is registered for the model, the can method will attempt to call the closure-based Gate matching the given action name.
        // Or, we can use middleware in route: ->middleware('can:update,post'). or directly: ->can('update', 'post').
        // Actions that do not require model: ->middleware('can:create,App\Models\Post'), ->can('create', Post::class)
        // In blade templates, we can use directive @can @elsecan @cannot @canany @elsecanany. or manually @if @unless.

        // Using Inertia, we can provide authorization data to the frontend using inertia's HandleInertiaRequests middleware's share method.
    }

    //* Real life custom login without any starter kit:
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // --------------------------------------------
        // Old approach (commented out): no rate limiting
        // --------------------------------------------
        // if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
        //     throw ValidationException::withMessages([
        //         'email' => 'The provided credentials do not match our records.',
        //     ]);
        // }
        // $request->session()->regenerate();
        // return redirect()->route('dashboard');

        // --------------------------------------------
        // New approach: rate limiting (email+ip) + global ip
        // --------------------------------------------
        $emailKey = Str::lower($data['email']);
        $ip = $request->ip();

        $throttleKey = "login:email_ip:{$emailKey}|{$ip}";
        $ipKey       = "login:ip:{$ip}";

        $maxAttemptsPerEmailIp = 5;
        $maxAttemptsPerIp      = 20;
        $decaySeconds          = 60;

        // If too many attempts (email+ip)
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttemptsPerEmailIp)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // If too many attempts (global ip)
        if (RateLimiter::tooManyAttempts($ipKey, $maxAttemptsPerIp)) {
            $seconds = RateLimiter::availableIn($ipKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts from this IP. Please try again in {$seconds} seconds.",
            ]);
        }

        $remember = (bool) $request->boolean('remember');

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            // record failed attempts
            RateLimiter::hit($throttleKey, $decaySeconds);
            RateLimiter::hit($ipKey, $decaySeconds);

            // he preferred consistent handling (throw validation exception)
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // success => clear rate limit buckets
        RateLimiter::clear($throttleKey);
        RateLimiter::clear($ipKey);

        $request->session()->regenerate();

        return redirect()->route('dashboard');

        // Refactoring: Use RateLimiter in boot method, then dont need to clean or generate again and again.
        // Rather than using ValidationException, use custom message as we do in RateLimiter opf boot() method.
        // See AppServiceProvider.
    }

    //* All about fortify package:
    public function fortify(){
        // Fortify is a headless authentication library.
        // In software engineering, "headless" refers to a system that operates without a graphical user interface (GUI) or a front-end presentation layer.
        // Fortify registers the routes and controllers needed to implement all of Laravel's authentication features.
        // Laravel's application starter kits use Fortify internally to provide authentication scaffolding for your application that includes a user interface built with Tailwind CSS.
        // For api auth, can use both fortify and sanctum. Sanctum will handle token and fortify will give route and controller.
        // If use any starter kit, dont need to install fortify, its automatically installed.

        // Installation: composer require laravel/fortify
        // Publish resources: php artisan fortify:install. (See app/actions directory, FortifyServiceProvider, config files and necessary migration files will be created.)
        // Migrate the migration files to start the work.
        // Config file give features: 'features' => Features::registration, ... We have to just remove or comment out what we dont want.
        // If we are building SPA, dont need blade views then in config file: 'views' => false.
        // You should still define a route named password.reset that is responsible for displaying your application's "reset password" view. 

        // Customization: use this class- Laravel\Fortify\Fortify.
        // Login template should include a form that makes a POST request to /login.
        // The /login endpoint expects a string email / username and a password. 
        // A boolean remember field may be provided to indicate that the user would like to use the "remember me" functionality provided by Laravel.
        // For XHR request, if success then send 200 response, if error then send 422 HTTP response.

        // Fortify will automatically retrieve and authenticate the user based on the provided credentials and the authentication guard that is configured for your application.
        // But if we need customization, can do in boot() of FortifyServiceProvider:
        Fortify::authenticateUsing(function(Request $request){
            // Logic for login...
        });
        // We can customize guard in config file.
        // If you are attempting to use Laravel Fortify to authenticate an SPA, you should use Laravel's default web guard in combination with Laravel Sanctum.

        
    }
}