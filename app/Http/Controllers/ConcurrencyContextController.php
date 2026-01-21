<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;

class ConcurrencyContextController{

    public function index(){
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
}