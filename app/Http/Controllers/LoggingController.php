<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class LoggingController {
    public function index(User $user){
        // To observe what is happenning withing the application.
        
        //* Configuration:
        // Laravel logging is based on "channels".
        // config/logging.php: By default, Laravel will use the stack channel when logging messages.
        // the single channel writes log files to a single log file, while the slack channel sends log messages to Slack.
        // Channels: custom, daily, errorlog, monolog, papertrial, single, slack, stack, syslog.
        // We can set deprecations to get log for deprecated warnings of php and library features to make our app updated. or can add another channel for deprecations.
        // For single and daily channel, options are bubble, permission and locking. Retention policy by LOG_DAILY_DAYS env variable or days config option.
        // The papertrail channel requires host and port configuration options. 
        // The slack channel requires a url configuration option.
        // We can customize monolog by adding 'tap' => [App\Logging\CustomizeFormatter::class] this type of option in the channel configuration and implement that class. or,
        // We can make a different channel with monolog driver and class as handler options.
        
        $message = "Jacchetay";

        Log::emergency($message);
        Log::alert($message);
        Log::critical($message);
        Log::error($message);
        Log::warning($message);
        Log::notice($message);
        Log::info($message);
        Log::debug($message);

        //* Adding Contextual Data:
        Log::info('User {id} failed to login.', ['id' => $user->id]);
        // Log::withContext() from middlewar's handler
        // Share Contextual info across all logging channels: Log::shareContext()

        //* Writting to specific channels rather than default:
        Log::channel('slack')->info($message);
        Log::stack(['single', 'stack'])->info($message); // for multiple channels
        Log::build(['driver' => 'single'])->info($message);// On demand channel. Make the channel and write log.
        // We can make a channel then can use it in Log::stack also.

        //* Pail
        // We may need to tail application's log in real time.
        // Laravel Pail is a package that allows you to easily dive into your Laravel application's log files directly from the command line. 
        // Laravel Pail requires the PCNTL PHP extension.
        // The pcntl extension is not supported on Windows. If you are trying to run Pail on Windows, you will need to use WSL2 (Windows Subsystem for Linux).
        // Install: composer require --dev laravel/pail
        // php artisan pail
        // php artisan pail -v (To increase the verbosity of the output and avoid truncation)
        // php artisan pail -vv (For maximum verbosity and to display exception stack traces)
        // php artisan pail --filter="QueryException"
        // php artisan pail --message="User created"
        // php artisan pail --level=error
        // php artisan pail --user=1
        // To stop: ctrl + c
    }
}