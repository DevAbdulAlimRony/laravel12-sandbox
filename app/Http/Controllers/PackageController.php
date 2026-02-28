<?php

namespace App\Http\Controllers;

class PackageController {
    // 1. Cashier- Stripe: Provides an expressive, fluent interface to Stripe's subscription billing services.
    // 2. Cashier- Paddle: Provides an expressive, fluent interface to Paddle's subscription billing services
    // 3. Dusk- Provides an expressive, easy-to-use browser automation and testing API.
    // 4. Fortify- Fortify registers the routes and controllers needed to implement all of Laravel's authentication features without frontent interface.
         // All of Laravel's application starter kits use Fortify and already provide a full authentication implementation.
         // On the other hand, Laravel Sanctum is only concerned with managing API tokens and authenticating existing users using session cookies or tokens.
         // Sanctum does not provide any routes that handle user registration, password reset, etc.
         // So, fortify and sanctum is not opposite of each other, we can use both in an application.
         // If you want all three pre-configured, use Laravel Jetstream. It uses Fortify for the logic, Sanctum for the security, and Inertia for the frontend.
    // 5. Folio: Laravel Folio is a powerful page based router designed to simplify routing in Laravel applications.
    // 6. Homestead:  Laravel Homestead is an official, pre-packaged Vagrant box that provides you a wonderful development environment without requiring you to install PHP, a web server, or any other server software on your local machine.
          // Its a legacy package now, no longer maintained. We can use saild as a modern alternative.
    // 7. Horizon: Laravel Horizon provides a beautiful dashboard and code-driven configuration for your Laravel powered Redis queues.
          // Horizon allows you to easily monitor key metrics of your queue system such as job throughput, runtime, and job failures.
    // 8. Laravel Mix is a legacy package that is no longer actively maintained. Vite may be used as a modern alternative.
    // 9. Octane: Laravel Octane supercharges your application's performance by serving your application using high-powered application servers
          // Octane boots your application once, keeps it in memory, and then feeds it requests at supersonic speeds.

    // 10. Laravel Passport provides a full OAuth2 server implementation for your Laravel application in a matter of minutes. 
           // We can do authentication system as well as oauth2 support. If our application need oAuth2 then rather than using sanctum, we can use oauth2.
    // 11. Pennat: Laravel Pennant is a simple and light-weight feature flag package, application new feature. A/B test new interface designs.
           // A/B testing is a marketing and UX technique, not a traditional "bug-finding" test. You show two versions of a page to different users to see which one performs better (e.g., more sign-ups or clicks).
    // 12. Pint: Laravel Pint is an opinionated PHP code style fixer for minimalists.
    // 13. Precognition: provide "live" validation for your frontend JavaScript application without having to duplicate your application's backend validation rules.
    // 14. Promts: Laravel Prompts is a PHP package for adding beautiful and user-friendly forms to your command-line applications, with browser-like features including placeholder text and validation.
           // Laravel Prompts is perfect for accepting user input in your Artisan console commands, but it may also be used in any command-line PHP project.
    // 15. Pulse: Laravel Pulse delivers at-a-glance insights into your application's performance and usage.
           // track down bottlenecks like slow jobs and endpoints, find your most active users, and more.
    // 16. Reverb
    // 17. Sail.
    // 18. Scout.
    // 17. Telescope: Telescope provides insight into the requests coming into your application, exceptions, log entries, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.
    // 18. Socialite.
    // 19. Valet: Laravel Valet configures your Mac to always run Nginx in the background when your machine starts. 
    // 20. Envoy.
    
    // Laravel Cloud, Laravel Vapor, Laravel Forge
    // Laravel Night Watch.
    
    public function envoy(){
        // Laravel Envoy is a tool for executing common tasks you run on your remote servers.
        // Using Blade style syntax, you can easily setup tasks for deployment, Artisan commands, and more.
        // Support on Linux Max and WSL2
        // php vendor/bin/envoy
        // Tasks define the shell commands that should execute on your remote servers when the task is invoked. 

        // composer require laravel/envoy --dev
        // php vendor/bin/envoy
        // In the roort level- Envoy.blade.php
        @servers(['web' => ['user@192.168.1.1'], 'workers' => ['user@192.168.1.2'], 'localhost' => '127.0.0.1'])
        @task('restart-queues', ['on' => 'workers'])
            cd /home/user/example.com
            php artisan queue:restart
            git pull origin {{ $branch }}
        @endtask

        // Full Documentation: https://laravel.com/docs/12.x/envoy#setup
    }

    //* Making Own Package
}