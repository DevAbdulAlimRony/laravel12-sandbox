<?php

namespace App\Http\Controllers;

use Inertia\Inertia;


class InertiaController extends Controller
{
    // Approach to build server driven web apps.
    // Inertia allows you to create fully client-side rendered, single-page apps, without the complexity that comes with modern SPAs. We can use server side patterns.
    // Inertia has no client-side routing, nor does it require an API. Simply build controllers and page views.
    // Inertia isn’t a framework, nor is it a replacement for your existing server-side or client-side frameworks. Rather, it’s designed to work with them. 

    // Install: composer require inertiajs/inertia-laravel
    // Root Template for first visit page: resources/views/app.blade.php, see that into the files.
    // By default, Inertia’s Laravel adapter will assume your root template is named app.blade.php. You may change this using the Inertia::setRootView() method.

    // Register Middleware: php artisan inertia:middleware
    // Now, add the middleware in bootsrap/app.php
    // $middleware->web(append: [ HandleInertiaRequests::class])
    // Those are all setup we need for backend, now we can work.

    public function index(Event $event)
    {
        // return inertia('Welcome');

        return Inertia::render('Event/Show', ['event' => $event->only('id')]);
    }
}