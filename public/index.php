<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// When you set up a Laravel site, you point the server's "Document Root" to the /public folder.
// By default, web servers are configured to look for a file named index.php or index.html as the entry point for any directory.
// In Laravel's public/.htaccess (for Apache) or the Nginx config, there is a rule that says: "If the user is looking for a file that doesn't exist (like /profile or /dashboard), send the request to index.php instead.

// microtime returns current Unix timestamp with microseconds.
// Packages like Laravel Debugbar or Telescope use this constant.
// They subtract LARAVEL_START from the current time at the end of the request to tell you exactly how many milliseconds the page took to load.
// You can use it anywhere in your app to show "Page loaded in X seconds" by doing: $executionTime = microtime(true) - LARAVEL_START;
// This constant becomes global. It is available in your Controllers, Models, Service Providers, and even your Blade views.

define('LARAVEL_START', microtime(true));


// Determine if the application is in maintenance mode...
// Laravel includes this check at the very top so it can "stop" the request before loading the entire framework (which saves CPU and Memory).
// How to activate it: You run the command php artisan down.
// This command creates that maintenance.php file in your storage folder.
// This file contains the logic to show your "503 Service Unavailable" page and then die(), preventing the rest of index.php from running.
// How to turn it off: Run php artisan up, which simply deletes that file.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
// It tells PHP where to find classes. When you say use Illuminate\Http\Request;, this file tells PHP: "You can find that class inside vendor/laravel/...."
// Using require ensures that if the vendor folder is missing, PHP will throw a Fatal Error and stop immediately.
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
// We use require_once because we only ever want one instance of the Laravel Application running.
// handleRequest(...): This takes the incoming web request (captured via Request::capture()) and sends it into the Laravel "Kernel" to find the right route.
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
