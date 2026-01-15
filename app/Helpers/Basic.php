<?php

use Illuminate\Support\Literal;

// Get the service container instance:
$container = app();
$api = app('HelpSpot\API');

// Resolve a class or interface: resolve()

// Returns all traits used by the class , also by its parents
$traits = class_uses_recursive(App\Models\User::class);
// trait_uses_recursive() function returns all traits used by a trait

// Config: Gets the value of a configuration variable.
$value = config('app.timezone');
config(['app.debug' => true]);

// Value of an env variable:
$env = env('APP_ENV');
$env = env('APP_ENV', 'production');

// View instance
return view('auth.login');

// Redirect to previous location:
return back();
return back($status = 302, $headers = [], $fallback = '/');

// Redirect:
return redirect($to = null, $status = 302, $headers = [], $secure = null);
return redirect('/home');
return redirect()->route('route.name');

// Current request instance:
request();
request('key', $default);

// Check a value is blank or not
blank(''); // true
blank('   '); // true
blank(null); // true
blank(collect()); // true
blank(0); // false
blank(false); // false

// Check given value is not blank:
filled('');

// Create a collection:
collect(['Taylor', 'Abigail']);

// Validator:
$validator = validator($data, $rules, $messages);

// Get value from the cache:
$value = cache('key');
$value = cache('key', 'default');
cache(['key' => 'value'], 300); // key value pairs, wait for 30 seconds.

// Create a new cookie instance:
$cookie = cookie('name', 'value', $minutes);

// Broadcasting an event:
// broadcast(), broadcast_if(), broadcast_unless()

// Push the job onto the queue: dispatch(new App\Jobs\SendEmails);
// To sync queue: dispatch_sync(new App\Jobs\SendEmails);
// Dispatch the given event to its listener: event(new UserRegistered($user))

// Retrive a policy instance for a given class: $policy = policy(App\Models\User::class);

// Gets the value from the current context:
context('trace_id');
context('trace_id', $default);
context(['trace_id' => Str::uuid()->toString()]);

