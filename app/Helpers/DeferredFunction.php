<?php
// Laravel's queued jobs allow you to queue tasks for background processing
// But,
// Sometimes simple tasks can arrive and we dont want logn running queue.

// Example:
// Writting log messgae in the background. Let's say we are writting log message after data insert, this will take a time. If we defer it, its written in background, so extra performance.
defer(function () {
    // Task to run after the response
    logger('Deferred task executed.');
});

// We can centralized background jobs into a defer.
// Since the tasks run after the response is sent, the user gets a faster experience while the server continues handling non-urgent operations in the background.
// Third Party API Communication: When your app needs to send data to an external service, such as an analytics platform, it can delay the response. Using defer(), you can offload this task.
defer(function () {
        Http::post('https://analytics.example.com/log', [
            'event' => 'purchase',
            'timestamp' => now(),
        ]);
});
// Benefit: The user immediately sees a confirmation while the analytics data is processed in the background.

// Sending emails can be using defer.
// Cleaning up temporary files after download. Like we delete a model, and delete model's images in background(If so much image like 300 high resolution image, then can use queue.).
// Doing complex calculations in the background.
defer(function () {
        // Simulate a heavy calculation
    sleep(5);
    logger('Report generation complete.');
});
return response()->json(['message' => 'Report generation started!']);


// Allows us to defer the execution of a closure until after the HTTP response has been sent to the user,
// After successful it will stop by default
// This means that deferred functions will not be executed if a request results in a 4xx or 5xx HTTP response. 
//  If you would like a deferred function to always execute, you may chain the always method onto your deferred function

// If you have the Swoole PHP extension installed, 
// Laravel's defer function may conflict with Swoole's own global defer function, leading to web server errors. 
// Make sure you call Laravel's defer helper by explicitly namespacing it: use function Illuminate\Support\defer

// If you need to cancel a deferred function before it is executed, 
// you can use the forget method to cancel the function by its name.

// When writing tests, it may be useful to disable deferred functions:
// $this->withoutDefer();
