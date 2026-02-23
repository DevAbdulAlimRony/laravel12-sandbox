<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Rather than making custom command class, we made a closure based command directly:
// Can type-hint any dependency in the callback. purpose is the description of the command which will be shown in command list and help.
// php artisan inspire.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//* Model Pruning:
Schedule::command('model:prune')->daily(); // By default, it will search prunable model into App\Models
// If model in different directory:
Schedule::command('model:prune', ['--model' => [Address::class, Flight::class],])->daily();
// Can use --except if want any prunable model to exclude.   
// Test: php artisan model:prune --pretend.

//* Task Schedular:
// In software engineering, a Cron job is a time-based job scheduler in Unix-like operating systems.
// It allows developers to automate tasks so they run automatically at specific intervals (minutes, days, or months) without any manual intervention.
// Ex: Every night at 2:00 AM, a script runs to delete any carts older than 24 hours.
// A script runs once an hour to check: "Is it anyone's billing date right now?" If yes, it triggers the payment gateway.
// Every Sunday at 4:00 AM, the system automatically compresses all files and uploads them to a secure cloud bucket.
// In Laravel, the Task Scheduler is a clean, expressive way to manage scheduled tasks (Cron jobs) directly within your application code.
// The scheduler allows you to fluently and expressively define your command schedule within your Laravel application itself.
// When using the scheduler, only a single cron entry is needed on your server. Just configure this cron in serve: schedule:run. Can use Laravel Cloud for easy setup.
// See all schedules: php artisan schedule:list
// If prefer to reserve this file only for command definitions, we can call schedule in bootstrap/app.php using withSchedule().
Schedule::call(function () {DB::table('recent_users')->delete();})->daily(); // scheduling closure
Schedule::call(new DeleteRecentUsers)->daily(); // Scheduling invokable object.
// Invokable objects are simple PHP classes that contain an __invoke method.
Schedule::command('emails:send Taylor --force')->daily(); // scheduling command.
Schedule::command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();
Schedule::job(new Heartbeat)->everyFiveMinutes(); // Schedule queued job.
Schedule::job(new Heartbeat, 'heartbeats', 'sqs')->everyFiveMinutes(); // On sqs connection.
Schedule::exec('node /home/forge/script.js')->daily(); // Scheduling shell command. Shell Command as a "backdoor" or a "direct hotline" to your computer's operating system.

Schedule::command('emails:send')->withoutOverlapping(); // will be run every minute if it is not already running. 
// Useful if you have tasks that vary drastically in their execution time, preventing you from predicting exactly how long a given task will take.
// It utilizes your application's cache to obtain locks.
// Clear those cache: php artisan schedule:clear-cache 
Schedule::command('emails:send')->withoutOverlapping(10); // how many minutes must pass before the "without overlapping".
// runInBackground(): By default sequnetially run multiple, If you have long-running tasks, this may cause subsequent tasks to start much later than anticipated. 
// Schedule will not run when the application is in maintenance mode, if want: ->evenInMaintenanceMode()

//* Schedular frequency options: https://laravel.com/docs/12.x/scheduling#schedule-frequency-options
// chianing: ->weekly()->mondays()->at('13:00'), ->weekdays(), ->hourly(), ->timezone('America/Chicago') ->between('8:00', '17:00').
// weekdays(), weekends(), sundays() to saturdays(), days(), between(), unlessbetween(), environment($env), environments(['staging', 'production'])
// days([0, 3]), days([Schedule::SUNDAY, Schedule::WEDNESDAY])
// when(closure()): the result of a given truth test. 
// skip(): Inverse of when.
// When using chained when methods, the scheduled command will only execute if all when conditions return true.
// Timezone scheduling is not recommended,  When daylight saving time changes occur, your scheduled task may run twice or even not run at all for some time zones.
// 'schedule_timezone' => 'America/Chicago' in env, no need specify in timezone() method now.
// onOneServer(): If we have multiple server, and we want atomic lock. Exmp, generate report daily. But same report can be generated in multiple servers.
// Schedule::useCache('database');

//* Dispatch same job with different parameters using name():
Schedule::job(new CheckUptime('https://laravel.com'))->name('check_uptime:laravel.com')->onOneServer();
// Schedule closure can use name also: Schedule::call()->name()

//* Schedule Groups:
Schedule::daily()->onOneServer()->timezone('America/New_York')->group(function () {
    Schedule::command('emails:send --force');
    Schedule::command('emails:prune');
});

//* Sub Minutes Scheduled Tasks:
// On most operating systems, cron jobs are limited to running a maximum of once per minute. 
// Laravel's scheduler allows you to schedule tasks to run at more frequent intervals, even as often as once per second.
// The schedule:run command will continue running until the end of the current minute instead of exiting immediately.
// It is recommended that all sub-minute tasks dispatch queued jobs or background commands to handle the actual task processing.
Schedule::command('users:delete')->everyTenSeconds()->runInBackground(); // or Schedule::job()
// Interrupt the command when deploying: php artisan schedule:interrupt.

//* Run schedule in background: php artisan schedule:work

//* Working with Task Output:
// Only for command and exec methods-
Schedule::command('emails:send')->daily()->sendOutputTo($path);
// appendOutputTo(), emailOutputTo(), emailOutputOnFailure()

//* Task Hooks:
Schedule::command('emails:send')->daily()->->before(function () {});
// after(), onSuccess(), onFailure()
// If output is available from your command,, can type-hint Stringable: onSuccess(function (Stringable $output)

//* Pinging URLs:
// Useful for notifying an external service, such as Envoyer, that your scheduled task is beginning or has finished execution.
// pingBefore($url), thenPing($url), pingOnSuccess($successUrl), pingOnFailure($failureUrl), pingBeforeIf($condition, $url), thenPingIf,pingOnSuccessIf, and pingOnFailureIf()

//* Schedule Events:
// ScheduledTaskStarting, ScheduledTaskFinished, ScheduledBackgroundTaskFinished, ScheduledTaskSkipped, ScheduledTaskFailed