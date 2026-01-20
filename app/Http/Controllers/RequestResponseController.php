<?php
use Illuminate\Http\Request;

class RequestResponse {
   //* Request class provides an object-oriented way to interact with the current HTTP request.
   //* If we type-hint the Request class on route closure or controller method, the incoming request instance will be automatically injected by laravel's service container.
   public function request(Request $request): void{
        
        //* Return path url
        $request->path(); // If  http://example.com/admin/index, outputis admin/index
        $request->url(); // Url without query string
        $request->fullUrl(); // Full url with query string.
        $request->fullUrlWithQuery(['type' => 'phone']); // Attach a query string.
        $request->fullUrlWithoutQuery(['type']); // only query string, no parameter.

        //* Return host, IP
        $request->host();
        $request->httpHost();
        $request->schemeAndHttpHost();
        $request->ip(); // IP address of the client that made the request
        $request->ips(); // An array of IP addresses, including all of the client IP addresses that were forwarded by proxies.

        //* Return header
        $request->header('X-Header-Name'); // Return header 'Name', if not then null
        $request->header('X-Header-Name', 'default'); // If not return default value
        // Example:
        if ($request->header('X-App-Version') < '2.0.0') {
            return response()->json(['error' => 'Please update your app!'], 426);
        }
        $request->bearerToken(); // Autorization Token: grabs a security key (token) sent by a user to prove they are allowed to access your data.
        // Output: "sY9xP2jK..."
        
        //* Content Negotiation
        $request->getAcceptableContentTypes(); // An array containing all of the content types accepted by the request
        $request->prefers(['text/html', 'application/json']); // Determine which content type out of a given array of content types is most preferred by the request, if not then null.

        //* psr-7 Request Instance
        // Some heavy-duty libraries are built entirely on PSR-7 standards.
        // Rather than using Laravel's request instance we can install and use psr-7's ServerRequestInterface $request if need.
        // composer require symfony/psr-http-message-bridge, composer require nyholm/psr7

        //* Input:
        $request->all(); // Incoming request's input data as an array.
        $request->collect(); // All of the incoming request's input data as a collection.
        $request->collect('users')->each(function (string $user) {}); // Subset of a incoming request's collection.
        $request->input(); // All the input values as an associative array.
        $request->input('name'); // Retrive the user input.
        $request->input('name', 'Abdul'); // Abdul is the default value if input value is not present.
        $request->input('products.0.name'); // Use "dot" notation to access the array input.
        $request->input('products.*.name'); // All product's name.
        // Input method retrieves values from the entire request payload (including the query string)
        // The query method will only retrieve values from the query string:
        $request->query(); // All query string values as an associative array.
        $request->query('name');
        $request->query('name', 'Default');
        $request->string('name')->trim(); // Instead of primitive string, now the data is an istance of Stringable. So we now can chain stringable's other method.
        $request->integer('per_page'); // Cast to integer, if not castable take default value. Useful for pagination.
        $request->boolean('archived'); // Returns true for 1, "1", true, "true", "on", and "yes". All other values will return false
        $request->array('versions'); // If not, empty arry.
        $request->date('birthday'); // dates / times may be retrieved as Carbon instances.
        $request->date('elapsed', '!H:i', 'Europe/Madrid'); // Make the date formatted and time zone.
        // If the input value is present but has an invalid format, an InvalidArgumentException will be thrown
        // Tt is recommended that you validate the input before invoking the date method.
        $request->enum('status', FileType::class); // Retrive enum values.
        $request->enum('status', FileType::class, FileType::Reciept); // Default case's value.
        $request->enums('products', FileType::class); // Array of values that correspond to enum.

        //* Access user input using dynamic properties.
        $request->name;
        //  Laravel will first look for the parameter's value in the request payload. If it is not present, Laravel will search for the field in the matched route's parameters.
        $request->only(['username', 'password']);
        $request->only('username', 'password');
        $request->except(['credit_card']);
        $request->except('credit_card');

        //* Check Request, Return Boolean
        $request->is('admin/*'); // If rquest matches a pattern.
        $request->routeIs('admin.*'); // If match a named route pattern.
        $request->hasHeader('X-Header-Name') // If request has a header.
        $request->accepts(['text/html', 'application/json']);  // If any content types are accepted by the request.
        $request->expectsJson(); // If expects a JSON response.
        $request->has('name'); // If the key name is present on the request
        $request->has(['name', 'email']); // Check if all of the given keys are present.
        $request->hasAny(['name', 'email']);
        $request->whenHas('name', function (string $input) {}); // Execute closure if the key present.
        $request->whenHas('name', function (string $input) {}, function(){}); // Last function will be executed if request value not present.
        $request->missing('name'); // If a given key is absent from the request
        $request->whenMissing('name', function () {}, function(){});

        //* Important Necessary Checking
        $request->filled('name'); // Check if value is presnt and is not empty string.
        $request->anyFilled(['name', 'email']); 
        $request->isNotFilled('name'); // Check if value is missing or an empty string.
        $request->isNotFilled(['name', 'email']); // Check all of the values into the array.
        $request->whenFilled('name', function (string $input) {}); // Second closure also can be passed if value is not filled.

        //* Merge additional key with existing keys, if present then overwrite.
        $request->merge(['votes' => 0]);
        $request->mergeIfMissing(['votes' => 0]);

        //* Old Input:
        // Laravel allows us to keep input from one request during the next request.
        // No need if we use Validation,  Laravel's built-in validation facilities will call them automatically.
        $request->flash(); // flash the current input to the session so that it is available in next request.
        $request->flashOnly(['username', 'email']);
        $request->flashExcept('password');
        redirect('/form')->withInput(); // Flash and redirect
        redirect()->route('user.create')->withInput();
        redirect('/form')->withInput($request->except('password'));
        $request->old('username'); // Retrive old input.
        // Can use global old() helper to repopulate the form in blade template
        // <input type="text" name="username" value="{{ old('username') }}">

        //* Retrive Cookie:
        $request->cookie('name');

        //*Input trimming and normalization
        // Laravel automatically convert empty string to null for all request using global ConvertEmptyStringsToNull middleware.
        // It also trim all incoming string fields using TrimStrings middleware globally.
        // If we want to disable those default middleware stack, in bootstrap/app.php's withmiddleware:
        //  $middleware->remove([ConvertEmptyStringsToNull::class,  TrimStrings::class]);
        // Disable only for subset of requests: $middleware->convertEmptyStringsToNull(except: [fn (Request $request) => $request->is('admin/*')])

        //* Search input sanitization:
        $normalized = str(request('search'))
                  ->squish()         // Removes leading/trailing whitespace AND converts internal double spaces to single
                  ->limit(100)       // Prevents long-string DOS attacks
                  ->value();
        // or, can validate at first: 'search' => 'nullable|string|max:255' then call squish and value.
        // If interacts with database, handle special characters:
        // preg_replace('/\s+/', ' ', $value ?? ''), or,
        // str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $normalized)
        // If dataset grows, can use laravel scout.

        //* File:
        $request->photo; // or,
        $request->file('photo');
        $request->photo->path(); // Fully qualified path of the file.
         $request->photo->extension(); // Extension of the file.
        // The file method returns an instance of the Illuminate\Http\UploadedFile class, which extends the PHP SplFileInfo class and provides a variety of methods for interacting with the file.
        $request->hasFile('photo'); // true
        $request->file('photo')->isValid(); // Verify that there were no problems uploading the file
        // Properties of UploadFileInstance: bool test, string originalName, string mimeType, int error
        // Other Methods of UploadFile Instance: 
        // getClientOriginalName(): string, etClientOriginalExtension(): string, getClientMimeType(): string
        // guessClientExtension(): ?string, getError(): int, move(string $directory, string $name = null): File
        // getMaxFilesize(): int|float, parseFilesize(string $size): int|float, getErrorMessage(): string
        
        //* Store file:
        $request->photo->store('images'); // File name will be generated automatically
        $request->photo->store('images', 's3');
        $request->photo->storeAs('images', 'filename.jpg');
        $request->photo->storeAs('images', 'filename.jpg', 's3');
    }
    
    public function response(){
        // The most basic response is returning a string
        // If we return array, it will be json response automatically
        // If we return eloquent collections, they will automatically be converted to JSON.
        return User::all();

        //* Returning Illuminate\Http\Response instance
        // A Response instance inherits from the Symfony\Component\HttpFoundation\Response class, which provides a variety of methods for building HTTP responses.
        // Returning a full Response instance allows us to customize the response's HTTP status code and headers.
        // Most response methods are chainable
         return response('Hello World', 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-Header-One', 'Header Value')
                ->withHeaders(['X-Header-One' => 'Header Value', 'X-Header-Two' => 'Header Value']) // Multiple Hedaers sent with response
                ->cookie('name', 'value', $minutes) // Send cookie for a value
                 // cookie can take more arguments: $path, $domain, $secure, $httponly
                ->withoutCookie('name'); // Remove the cookie by expiring it
                // If response instance is not ready yet:
                // Cookie::queue('name', 'value', $minutes)- This cookie will be attached to the outgoing response.
                // or use helper: $cookie = cookie('name', 'value', $minutes); then attach with response chain- ->cookie($cookie);

        //* Returning Illuminate\Http\RedirectResponse instance:       
        return redirect('/home/dashboard'); // using global helper
        return back()->withInput(); // Return to the previous location utilizing session. 
        // withInput() will flash the current inputs
        // If we call redirect helper with no parameters, we can chain other methods:
        return redirect()->route('profile', ['id' => 1]);
        return redirect()->route('profile', [$user]); // Id of eloquent will be extracted automatically: /profile/{id}
        return redirect()->action([BasicController::class, 'index']); // Redirect to a controller's method. Can pass route parameter as the second argument.
        return redirect('/dashboard')->with('status', 'Profile updated!'); // Redirect with flashed success or any message.
        // Now can use that flashed message in view: {{ session('status') }}

        //* Other Response Types:
        // When the response helper is called without arguments, Illuminate\Contracts\Routing\ResponseFactory contract is returned
        return response()->view('hello', $data, 200)->header();
        return response()->json(['name' => 'Abigail']); // Return Type JsonResponse.
        // json()  will automatically set the Content-Type header to application/json , also,
        // convert the given array to JSON using the json_encode PHP function.

        //* JSONP Response:
        // JSONP (JSON with Padding) is a technique used to request data from a server in a different domain—something that standard AJAX requests normally block due to the Same-Origin Policy (SOP).
        return response()->json(['name' => 'Abigail', 'state' => 'CA'])->withCallback($request->input('callback'));

        //* File:
        return response()->file($pathToFile, $headers); // Display a file such as image or pdf directly in the browser.
        return response()->download($pathToFile); // Force the user browser to download the file
        return response()->download($pathToFile, $name, $headers); // name which is seen by the user downloading the file.

        //* We can make our own custom macro response in any service provider's boot method. See App ServiceProvider.

        //* Streaming Response:
        // A Streamed Response is used when you need to send data to the client bit-by-bit rather than waiting for the entire payload to be ready.
        // We can reduce memory usage and improve performance using streamed responses.
        // Exmp: Large CSV/Excel Exports(100,000 rows), AI Text Generation (ChatGPT style), Real-time Log Monitoring, Large media download like video. 
        // response()->stream(function(){ .. Here we can use ob_flush() for output buffering, sleep(), flush() })
        // Can use OpenAI::client()->chat()->createStreamed(...);
        // Can use in vue: npm install @laravel/stream-vue
        // return response()->eventStream(function ()
        // response()->streamDownload(function () {echo GitHub::api('repo')
        
        //* Stream JSON:
        // Useful for large datasets that need to be sent progressively to the browser in a format that can be easily parsed by JavaScript.
        return response()->streamJson(['users' => User::cursor()]);
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
}