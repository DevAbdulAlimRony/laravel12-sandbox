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
use Laravel\Socialite\Socialite;

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
        
        // Fortify authenticates login requests through a pipeline of invokable classes.
        // If we want our own pipeline, add in boot():
        Fortify::authenticateThrough(function(Request $request){});
        // By default throttled to a username and ip address combination.
        // We can customize it in config files'- fortify.limiters.login configuration option.
        // Utilizing a mixture of throttling, two-factor authentication, and an external web application firewall (WAF) will provide the most robust defense for your legitimate application users.

        // After login, automatically redirect to the /home, we can customize it Service provider's regiser method:
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {}); // LoginResponse also.

        // If two factor authentication enabled,
        // The user is required to input a six digit numeric token during the authentication process.
        // This token is generated using a time-based one-time password (TOTP) that can be retrieved from any TOTP compatible mobile authentication application such as Google Authenticator.
        // In User model: use Notifiable, TwoFactorAuthenticatable;
        // Next, build a screen where user can enable and disable their two factor auth and for recovery code.
        // Enable endpoint: /user/two-factor-authentication.
        // If the request is successful, the user will be redirected back to the previous URL and the status session variable will be set to two-factor-authentication-enabled.
        // After choosing to enable two-factor authentication, the user must still "confirm" their two-factor authentication configuration by providing a valid two-factor authentication code. 
        // $request->user()->twoFactorQrCodeSvg();
        // /user/confirmed-two-factor-authentication endpoint.
        // $request->user()->recoveryCodes(), for xhr: /user/two-factor-recovery-codes endpoint to show recovery codes if mobile access gone.
        // To begin implementing two-factor authentication functionality, in boot():
        Fortify::twoFactorChallengeView(function () {});
        // Fortify will take care of defining the /two-factor-challenge route that returns this view.
        // The /two-factor-challenge action expects a code field that contains a valid TOTP token or a recovery_code field that contains one of the user's recovery codes.
        // To disable two-factor authentication, your application should make a DELETE request to the /user/two-factor-authentication endpoint.

        // Registration:
        //  /register endpoint
        // The /register endpoint expects a string name, string email address / username, password, and password_confirmation fields. 
         Fortify::registerView(function () {
            // return blade of the registration template.
         });
         // If registration successful, will take to the /home route, for xhr, 201 status code.
         // The user validation and creation process may be customized by modifying the App\Actions\Fortify\CreateNewUser action.

         // Password Reset:
         // Fortify::requestPasswordResetLinkView(function () {}); // endpoint: /forgot-password.
         // Fortify::resetPasswordView(function (Request $request) {})
        
         // Email Verification:
         // ensure the emailVerification feature is enabled in your fortify configuration file's features array. 
         // In user model, implements MustVerifyEmail.
         Fortify::verifyEmailView(function () {});

         // Password Confirmation:
         // While building your application, you may occasionally have actions that should require the user to confirm their password before the action is performed. 
         Fortify::confirmPasswordView(function () {});

         // In laravel 13, WebAuthn (Passkeys) is natively integrated directly into Laravel Fortify and the Starter Kits. 
         // Your users can securely log in via Face ID, Touch ID, or hardware security keys out-of-the-box, letting you ditch complex, third-party WebAuthn packages.
         // his will allow users to simultaneously act on behalf of different teams via distinct URLs (even in different browser tabs) without encountering session conflicts.
    }

    public function socialite(){
        // Socialite currently supports authentication via Facebook, X, LinkedIn, Google, GitHub, GitLab, Bitbucket, and Slack.
        // Install: composer require laravel/socialite.
        // Put OAuth providers' credentials in services.php config file: 'github' => [].

        // Need two Routes:
        // Redirecting the user to the OAuth provider, and another for receiving the callback from the provider after authentication.
        Route::get('/auth/redirect', function () {
            return Socialite::driver('github')->redirect();
        });
        Route::get('/auth/callback', function () {
            $user = Socialite::driver('github')->user();
            $user = Socialite::driver('github')->userFromToken($token);
            Socialite::driver('google')->stateless()->user(); // useful when adding social authentication to a stateless API that does not utilize cookie based sessions
            // ->scopes(['read:user', 'public_repo'])->redirect(): Merged with existing scopes.
            // Override all scopes: ->setScopes(['read:user', 'public_repo'])
            // Socialite::driver('slack')->asBotUser()->setScopes(['chat:write', 'chat:write.public', 'chat:write.customize'])->redirect();
            // Bot tokens are primarily useful if your application will be sending notifications to external Slack workspaces that are owned by your application's users.
            // When generating a bot token, the user method will still return a Laravel\Socialite\Two\User instance; however, only the token property will be hydrated. 
            // Optional parameters if support: ->with(['hd' => 'example.com']).
            // When using the with method, be careful not to pass any reserved keywords such as state or response_type.
        });

        // Once the user has been retrieved from the OAuth provider, you may determine if the user exists in your application's database and authenticate the user.

        // Testing: https://laravel.com/docs/12.x/socialite#testing
    }

    public function sanctum(Request $request){
        // Sanctum allows each user of your application to generate multiple API tokens for their account. 
        // These tokens typically have a very long expiration time (years), but may be manually revoked by the user anytime.
        
        // Install: php artisan install:api
        // We can override default models creating a class PersonalAccessToken extends SanctumPersonalAccessToken then registering in AppService Provider's boot:
        // Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // When making requests using API tokens, the token should be included in the Authorization header as a Bearer token.
        // In User model: use HasApiTokens.

        // PI tokens are hashed using SHA-256 hashing before being stored in your database
        $token = $request->user()->createToken($request->token_name);
        // return ['token' => $token->plainTextToken];
        // foreach ($user->tokens as $token) {}

        // Give Token Abilities:
        $user->createToken('token-name', ['server:update'])->plainTextToken;
        // if ($user->tokenCan('server:update')), tokenCant().

        // Protecting Routes: ->middleware('auth:sanctum').

        // Can add abilities and ability middleware alias in bootstrap/app.php.
        // Then can use for any route: ->middleware(['auth:sanctum', 'abilities:check-status,place-orders']).

        // Revoking or Deleting Tokens:
        // By default, Sanctum tokens never expire and may only be invalidated by revoking the token.
        $request->user()->tokens()->delete(); // Revoke all tokens...
        $request->user()->currentAccessToken()->delete(); // Revoke current token...
        $request->user()->tokens()->where('id', $tokenId)->delete(); 

        // But we can set an expiration time also:
        // In sanctum config file: 'expiration' => 525600.
        // If wanna do for each token independently, take second argument:
        $user->createToken('token-name', ['server:update'], now()->addDays(10))->plainTextToken;  
        // If we set expiration time, we should schedule a prune: Schedule::command('sanctum:prune-expired --hours=24')->daily();

        // SPA Authentication:
        // In order to authenticate, your SPA and API must share the same top-level domain. 
        // In configuration set first party stateful domain: Sanctum::currentApplicationUrlWithPort().
        // In app.php withMiddleware: $middleware->statefulApi().
        
        // If need cors configuration for separate sub domain: php artisan config:publish cors.
        // In cors.php: Access-Control-Allow-Credentials header with a value of True. 
        // In bootstrap.js: axios.defaults.withCredentials = true, axios.defaults.withXSRFToken = true.
        // In session.php: 'domain' => '.domain.com'.

        // To authenticate SPA, first to login: axios.get('/sanctum/csrf-cookie').then(response => {}
        // Once CSRF protection has been initialized, you should make a POST request to your Laravel application's /login route, maybe using fortify.
        // If the login request is successful, you will be authenticated and subsequent requests to your application's routes will automatically be authenticated via the session cookie that the Laravel application issued to your client. 
    
        // Authorizing Private broadcast Channel: See app.php.
        // You will need to provide a custom Pusher authorizer when initializing Laravel Echo. 

        // For Mobile Application Authentication:
        // Use device_name with email and password when login to issue token. Create Tken with that device name.

        // Testing:
        Sanctum::actingAs(User::factory()->create(), ['view-tasks']); // view tasks can be ['*']
        $response = $this->get('/api/task');
        $response->assertOk();
    }

    public function jetstream(){
         // Jetstream is a eautifully designed application starter kit for Laravel.
         // Jetstream provides the implementation for your application's login, registration, email verification, two-factor authentication, session management, API via Laravel Sanctum, and optional team management features.
         // Jetstream is designed using Tailwind CSS and offers your choice of Livewire or Inertia scaffolding.
         // Laravel Livewire is a library that makes it simple to build modern, reactive, dynamic interfaces using Laravel Blade as your templating language. 
         // The Inertia stack provided by Jetstream uses Vue.js as its templating language.

         // Install: omposer require laravel/jetstream.
         // php artisan jetstream:install, and select the stack- livewire or inertia.
         // You may use the --teams switch to enable team support.
         // Or, everything with a command: php artisan jetstream:install inertia --teams.
         // SSR Support: php artisan jetstream:install inertia --ssr.
         // Change default jetstream logo in inertia: See the vue pages for ApplicationLogo, Applicationmark, ApplicationCardLogo.
         // During the Jetstream installation process, actions are published to your application's app/Actions directory.
         
         // In contrast to Laravel Breeze, Laravel Jetstream does not publish controllers or routes to your application.
         // Action classes typically perform a single action and correspond to a single Jetstream or Fortify feature, such as creating a team or deleting a user.
         // When using Inertia, "Pages" will be published to your resources/js/Pages directory.
         // Application Layout for Inertia Located in: App\View\Components\AppLayout.
         // Other default pages: Dashboard.vue, GuestLayout.vue, Banner.vue, Teams/Create and Show.vue etc.
         // A postcss.config.js file and tailwind.config.js file will be created. 
         // After authenticating with your application, you will be redirected to the /dashboard route.
         // We can customize or edit them anything we want.

         //* API:
         // We can give multipple api token for user with permission. Check profiles dropdown.
         // If not in config/jetstream.php features: Features::api().
         // If not install: php artisan install:api
         // The permissions available to API tokens are defined using the Jetstream::permissions method within your application's App\Providers\JetstreamServiceProvider class. 
         Jetstream::defaultApiTokenPermissions(['read']);
         Jetstream::permissions(['post:create']);
         // HasApiTokens trait is automatically applied to your application's App\Models\User model during Jetstream's installation.
         // Checking Permission: $request->user()->tokenCan('post:update').
         // When a user makes a request to a route within your routes/web.php file, the request will typically be authenticated by Sanctum through an authenticated session cookie based guard.

         //* Login:
         // Under the hood, the authentication portions of Jetstream are powered by Laravel Fortify, which is a frontend agnostic authentication backend for Laravel.
         // When Jetstream is installed, the config/fortify.php configuration file is installed.
         // Backedn classes in app/Action directory.

         // Livewire Page: esources/views/auth/login.blade.php
         // Inertia page: resources/js/Pages/Auth/Login.vue
         // Customizing view in JetStreamServiceprovider boot():
         Fortify::loginView(function () {return view('auth.login');}); // If Inertia:
         Fortify::loginView(function () {return Inertia::render('Auth/Login');});

         // Full Logic Customization in boot:
         Fortify::authenticateUsing(function (Request $request) {$user = User::where('email', $request->email)->first(); });
         Fortify::authenticateUsing([new AuthenticateLoginAttempt, '__invoke']); // Call a class instead of direct logic.
         // Custom pipeline to authenticate: Fortify::authenticateThrough(function (Request $request) {  return array_filter([...

         //* Registration:
         // App\Actions\Fortify\CreateNewUser class will be invoked when a user registers with your application. 
         // App\Actions\Fortify\CreateNewUser, App\Actions\Fortify\ResetUserPassword, App\Actions\Fortify\UpdateUserPassword will use Password Validation Rules here: App\Actions\Fortify\PasswordValidationRules
         // Pages: resources/views/auth/register.blade.php or resources/js/Pages/Auth/Register.vue.
         
         // Customize View in boot:
         Fortify::registerView(function () {return view('auth.register');}); // If Inertia:

        // Many applications require users to accept their terms of service / privacy policy during registration. 
        // Setup in config/jetstream.php: 'features' => Features::termsAndPrivacyPolicy(), the write terms in resources/markdown/terms.md or localized version terms.es.md.
        // Enable email verification during register in config/fortify.php: 'features' => Features::emailVerification(), then in User model implements MustVerifyEmail.

        //* Profile Management:
        // App\Actions\Fortify\UpdateUserProfileInformation
        // views/profile/update-profile-information-form.blade.php, esources/js/Pages/Profile/UpdateProfileInformationForm.vue.
        // Enable user can upload profile photo in config/jetstream.php features: Features::profilePhotos() and  execute the storage:link Artisan command.
        // Laravel\Jetstream\HasProfilePhoto trait that is automatically attached to your App\Models\User class
        // Account Deletion: enable feature in config-  Features::accountDeletion().

        //* Password Update:
        // App\Actions\Fortify\UpdateUserPassword, resources/views/profile/update-password-form.blade.php or resources/js/Pages/Profile/UpdatePasswordForm.vue.

        //* Password Confirmation:
        // Confirm user's password before the action is performed.
        // redirect based password confirmation and modal based password confirmation.
        // Redirect based password confirmation is typically used when the user needs to confirm their password before accessing an entire screen that is rendered by your application, such as a billing settings screen.
        // Modal based password authentication might be used when you would like the user to confirm their password before performing a specific action, such as when enabling two-factor authentication.
        // the route that will render the view that requires password confirmation and any routes that perform the confirmed actions are assigned the password.confirm middleware.
        // resources/js/Pages/Auth/ConfirmPassword.vue.
        // Once the user has confirmed their password, they will not be required to re-enter their password until the number of seconds defined by your application's auth.password_timeout configuration option has elapsed.
        // For Modal based: import ConfirmsPassword from './Components/ConfirmsPassword.vue'
        // <ConfirmsPassword @confirmed="enableAdminMode">
        // Customizing in boot: Fortify::confirmPasswordsUsing(function (User $user, string $password) {..
        // Customized Class: Fortify::confirmPasswordsUsing([new ConfirmPassword, '__invoke']);

        //* Two Factor Authentication:
        // When a user enables two-factor authentication for their account, they should scan the given QR code using a free TOTP authenticator application such as Google Authenticator.
        // In addition, they should store the listed recovery codes in a secure password manager such as 1Password.
        // Jetstream's two-factor authentication services are encapsulated within Jetstream and should not require customization
        // resources/views/profile/two-factor-authentication-form.blade.php or, resources/js/Pages/Profile/TwoFactorAuthenticationForm.vue.
        // Can disable by removing it from config.

        //* Browser sessions:
        // Using Illuminate\Session\Middleware\AuthenticateSession middleware to safely log out other browser sessions that are authenticated as the current user.
        // views/profile/logout-other-browser-sessions-form.blade.php, esources/js/Pages/Profile/LogoutOtherBrowserSessionsForm.vue.

        //* Teams:
        // Jetstream's team scaffolding and opinions may not work for every application. 
        // If you installed Jetstream using the --teams option, your application will be scaffolded to support team creation and management.
        // Jetstream's team features allow each registered user to create and belong to multiple teams.
        // If a user named "Sally Jones" creates a new account, they will be assigned to a team named "Sally's Team". After registration, the user may rename this team or create additional teams.
        // In actions, CreateTeam, UpdateTeamName, and DeleteTeam.
        // esources/js/Pages/Teams/CreateTeamForm.vue.

        // Accessing User Tea,m:
        // use HasTeams;
        // $user->currentTeam : Laravel\Jetstream\Team, $user->allTeams(), ->ownedTeams, ->teams, ->personalTeam(), ownsTeam(0, belongsToTeam(), teamRole(0, hasTeamRole(0, teampermission(), teamPermissions(), hasTeampermission().
        



    }    

    public function passport(){

    }
}