<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Context;
use Illuminate\Log\Context\Repository;

class ConcurrencyContextController{

    public function concurrency(){
        //* Concurrency (Run multiple slow tasks together which do not depend on one another.)
        // Use this when you have multiple tasks that don't depend on each other, and you want to run them at the same time to save time.
        // Normally, PHP runs line-by-line (sequentially). If Task A takes 2 seconds and Task B takes 2 seconds, the total time is 4 seconds. With Concurrency, the total time is only 2 seconds.
        // Imagine a user searches for a flight on your website. Your app needs to check prices from three different airlines: Emirates, Qatar Airways, and Biman.
        // The Old Way: Check Emirates (wait 2s) → Check Qatar (wait 2s) → Check Biman (wait 2s). Total Wait: 6 seconds.
        // The Concurrency Way: Check all three at the same time. Total Wait: 2 seconds.
        // Laravel achieves concurrency by serializing the given closures and dispatching them to a hidden Artisan CLI command.
        // We can publish config file if need, typically not need: php artisan config:publish concurrency
        
        // The Concurrency facade supports three drivers: process (the default), fork, and sync.
        // The sync driver is primarily useful during testing 
        // Install fork driver: composer require spatie/fork

        [$userCount, $orderCount] = Concurrency::run([
            fn () => DB::table('users')->count(),
            fn () => DB::table('orders')->count(),
        ]);
        // Specify driver:  Concurrency::driver('fork')->run(...);

        //* Deferring Concurrent Tasks:
        // Use this when a task needs to happen, but the user doesn't need to wait for it to see the page. 
        // The task runs after the HTML response has been sent to the browser.
        // When a user buys a product, they want to see the "Success" page immediately. They don't want to wait for you to notify your marketing team or update your internal analytics.
        // The Old Way: Save Order → Send Email → Ping Slack → Show Success Page.
        // The Defer Way: Save Order → Show Success Page → (In the background) Send Email & Ping Slack.
        
        // 1. User sees "Success" immediately.
        // 2. AFTER the response, these 3 tasks start AT THE SAME TIME.
        Concurrency::defer([
            fn () => NotificationService::sendSms($order),     // Starts at 0s
            fn () => ExternalERP::syncOrder($order),           // Starts at 0s
            fn () => MarketingTool::trackConversion($order),   // Starts at 0s
        ]);
        // If user close the page or browser, it will still run in background.
    }

    public function context(){
        // Capture, retrieve, and share information throughout requests, jobs, and commands executing within your application.
        // It acts like a "shared bucket" for information that stays with a specific request from the moment it starts until it finishes.
        // It solves the problem of "How do I pass data deep into my app without passing it through every single function as a variable?"
        // Exmp: Using request id or user id, using static variables.
        // Laravel Context provides a clean, thread-safe way to store data that is automatically available everywhere: in your controllers, models, observers, and even logs.

        // Example: Imagine you run an e-commerce site. A user named Sarah tries to checkout, but it fails. She calls support. To find out what happened, you need to see every log entry related to her specific click.
        // Without Context, your logs look like a giant mess of data from 100 different users at once. With Context, you can "tag" every action Sarah's request takes.
        // See Middleware -> AddTraceContext class.

        //* Other Examples:
        // Multi-Tenant Account Switching: In a SaaS where a user can belong to multiple "Teams," you need to know which Team is active for the current request without querying the database in every controller.
        // API Versioning State: If you support multiple API versions (v1, v2), you might need to change how a specific Model calculates a value (like a "Price" or "Full Name") based on which version the user is calling.
        Context::add('api_version', 'v2');
        return [
            'id' => $this->id,
            'price' => Context::get('api_version') === 'v2' 
                   ? $this->price_with_tax 
                   : $this->base_price,
        ];
        // Passing "Context" to Background Jobs: If you send an email in the background, the "Job" doesn't know what IP address the user was using when they clicked "Register." Context handles this automatically.
        // Audit Trails (Who did what?): If you use Model Observers to record "Audit Logs," the Observer often doesn't know why a change happened (e.g., was it via the Web UI or an API script?). You can store the "source" in Context.
        Context::add('source', 'artisan-command');
        AuditLog::create([
            'user_id' => $user->id,
            'source'  => Context::get('source', 'web-request'), // Defaults to web
            'changes' => $user->getChanges()
        ]);

        //* Adding context:
        Context::add('key', 'value');
        Context::add(['first_key' => 'value', 'second_key' => 'value']);
        // The add method will override any existing value that shares the same key.
        Context::addIf('key', 'second'); // add only if context key already not added.
        Context::increment('records_added');
        Context::increment('records_added', 5); // Same goes for decrement.
        
        //* Conditional Context:
        Context::when(
            Auth::user()->isAdmin(), // Condition
            fn ($context) => $context->add('permissions', Auth::user()->permissions), // Run if condition is true
            fn ($context) => $context->add('permissions', []), // Run if false.
        );

        //* Scoped Context:
        // Modify context when a callback execute, back to the old context after execution finish.
        Context::scope(function(){})

        //* Stack: lists of data stored in the order they were added.
        Context::push('breadcrumbs', 'first_value');
        Context::push('breadcrumbs', 'second_value', 'third_value'); // If we get the context, all will be in a single array.
        // Stacks can be useful to capture historical information about a request, such as events that are happening throughout your application.
        Context::stackContains('breadcrumbs', 'first_value') // Check if valuein stack. Can take second argument like to take only startsWith a character maybe.

        //* Retrieving Context:
        Context::all();
        Context::get('key');
        Context::only(['first_key', 'second_key']);
        Context::except(['first_key']);
        Context::pop('breadcrumbs'); // pop from stack.
        Context::remember('user-permissions', fn () => $user->permissions); // Try to retrieve if not exists then set that closure's returned value.

        //* Checking:
        if (Context::has('key')) {} // a key with a null value will be considered present.
        if (Context::missing('key')) {}

        //* Removing Context:
        Context::forget('first_key');
        Context::forget(['first_key', 'second_key']);
        Context::pull('key'); // Retrive and immediately remove.

        //* Hidden Context:
        // This hidden information is not appended to logs, and is not accessible via the normal data retrieval methods .
        // For hidden Context: addHidden(), addHiddenIf(), pushHidden(), 
        // pullHidden(), popHidden(), onlyHidden(), exceptHidden(), 
        // allHidden(), hasHidden(), missingHidden(), forgetHidden()

        //* Events
        // Dehydrating: Whenever a job is dispatched to the queue the data in the context is "dehydrated" and captured alongside the job's payload.
        // The Context::dehydrating method allows you to register a closure that will be invoked during the dehydration process.
        // hould register dehydrating callbacks within the boot method of your application's AppServiceProvider class
        Context::dehydrating(function (Repository $context) {
            $context->addHidden('locale', Config::get('app.locale'));
        }); // should not use the Context facade within the dehydrating callback, so we injected Repository.
        // The Context::hydrated method allows you to register a closure that will be invoked during the hydration process.
    }
}