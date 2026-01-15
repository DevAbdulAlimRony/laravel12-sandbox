<?php
// Sleep class is a light-weight wrapper around PHP's native sleep and usleep functions, offering greater testability while also exposing a developer friendly API for working with time

use Illuminate\Http\Request;
use Illuminate\Support\Sleep;

// Pause for 500 milliseconds between each API call 
// to stay under the rate limit.
foreach ($products as $product) {
    $api->updateProduct($product);

    // Pause for 500 milliseconds between each API call 
    // to stay under the rate limit.
    Sleep::for(500)->milliseconds();
}

// Retrying Failed Jobs with "Exponential Backoff"
function handle()
{
    $attempts = 0;
    while ($attempts < 3) {
        try {
            return processExport();
        } catch (Exception $e) {
            $attempts++;
            // Sleep longer after each failed attempt
            Sleep::for($attempts * 2)->seconds();
        }
    }
}

// Preventing "Race Conditions" in Webhook Processing.
function handleWebhook(Request $request)
{
    // Wait 200ms to ensure the database record created by 
    // the redirect flow is fully committed.
    Sleep::for(200)->milliseconds();

    $order = Order::find($request->order_id);
}

// We use Sleep class rather than sleep(), because of Testability.

// Return a value after sleeping...
$result = Sleep::for(1)->second()->then(fn () => 1 + 1);

// Sleep while a given value is true...
Sleep::for(1)->second()->while(fn () => shouldKeepSleeping());

// Pause execution for 90 seconds...
Sleep::for(1.5)->minutes();

// Pause execution for 2 seconds...
Sleep::for(2)->seconds();

// Pause execution for 500 milliseconds...
Sleep::for(500)->milliseconds();

// Pause execution for 5,000 microseconds...
Sleep::for(5000)->microseconds();

// Pause execution until a given time...
Sleep::until(now()->plus(minutes: 1));

// Alias of PHP's native "sleep" function...
Sleep::sleep(2);

// Alias of PHP's native "usleep" function...
Sleep::usleep(5000);

Sleep::for(1)->second()->and(10)->milliseconds();
 