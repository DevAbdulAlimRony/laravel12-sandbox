<?php
// Timebox class ensures that given calllback takes a fixed amount of time to execute, even if its actual execution completes sooner. 
// Useful for cryptographic operations and user authentication checks, where attackers might exploit variations in execution time to infer sensitive information.

// If the execution exceeds the fixed duration, Timebox has no effect

// The call method accepts a closure and a time limit in microseconds, and then executes the closure and waits until the time limit is reached:
use Illuminate\Support\Timebox;

$timebox = new TimeBox();
$timebox->call(function ($takeTime) { }, 10000);

// If an exception is thrown within the closure, this class will respect the defined delay and re-throw the exception after the delay.

// Imagine a hacker is trying to guess which email addresses are registered in your "Warehouse A" staff portal.
// If the email "admin@warehouse.com" exists, the server takes 50ms to check the password hash. If the email "fake@user.com" doesn't exist, the server returns an error in 5ms.
// A hacker can measure this 45ms difference to confirm which emails are valid.
// Using timebox: Regardless of whether the user was found or the password was correct, the response always takes 500ms.
// This makes it impossible for an attacker to gain information by timing the response.

// If your code execution exceeds the time you specified in the timebox, nothing special happens to stop or throttle the code. The code simply finishes its task, and the function returns immediately without any additional "sleep" or delay.

// Another example- just show a loader for small task so that:
// users might not feel like the system didn't actually "do" any hard work 
// This is a known psychological phenomenon called the "Labor Illusion"

// Another Example: Rate Limiting "Expensive" Logic (Soft Protection)