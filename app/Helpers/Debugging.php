<?php
// Dumps the given variables
dump($value);
dump($value1, $value2, $value3);

// Dumps the given variables and ends the execution of the script
dd($value);
dd($value1, $value2, $value3);

// Throw an HTTP exception:
abort(404);
abort(403, 'Unauthorized.', $headers);
abort_if(! Auth::user()->isAdmin(), 403);
abort_unless(Auth::user()->isAdmin(), 403);

// Report an exception using exception handler:
report($e);
report('Something went wrong.');
report_if($shouldReport, 'Something went wrong.');
report_unless($reportingDisabled, $e);

// Throws the given exception if a given boolean expression evaluates to true
throw_if(! Auth::user()->isAdmin(), AuthorizationException::class, 'You are not allowed to access this page.');
// throw_unless()

// Use abort() when the issue is related to the User's Request. It is designed to talk to the browser.
// Use throw_if() when the issue is related to Business Logic or Data Integrity.
// throw_if(
//     $array['products']['chair']['stock'] <= 0, 
//     OutOfStockException::class, 
//     "Order failed: Item went out of stock during checkout."
// );

// Think of Abort as a way to stop the user, and Report as a way to notify the developer.
// Use abort to immediately stop the execution of the request and return an HTTP error response to the user.
// Use report To log an error or send it to an external monitoring service (like Sentry or Flare) without stopping the current request.
// Let's if mail sending failed, just notify the dev team using report, and keep website running.

// Write Log
info('Some helpful information!'); // Info Log
info('User login attempt failed.', ['id' => $user->id]);
logger('User has logged in.', ['id' => $user->id]); // Debug level messages.
logger()->error('You are not allowed here.');