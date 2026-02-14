<?php
namespace App\Http\Controllers;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Bus;

class QueueController {
    // A Queue job is a task that is offloaded to be processed later so the user doesn't have to wait for it to finish.
    // Exmp: sending an email, processing a video, generating a report, database cleanup, automated billing etc.
    // Laravel queues provide a unified queueing API across a variety of different queue backends, such as Amazon SQS, Redis, or even a relational database.
    // config/queue.php
    // Queue drivers: sync, database, beanstalkd, sqs, redis, null
    // Synchronous driver will execute jobs immediately (for use during development or testing).
    // Process job for multiple queues: php artisan queue:work --queue=high,default

    // Database Queue: Hold the job in database 0001_01_01_000002_create_jobs_table.php
    // If table not available: php artisan queue:table
    // Redis Queue: Configure a Redis database connection in config/database.php.  Need dependencie: redis/predis ~2.0 or phpredis PHP extension.
    // Change default queue connection in .env: QUEUE_CONNECTION

    // Create Job: php artisan make:job ProcessPodcast
    // Job class will be created in app/Jobs/ProcessPodcast.php, implement shouldQueue interface and use Queueable trait.

    //* Dispatch or Call the Job:
    $podcast = Podcast::create();
    ProcessPodcast::dispatch($podcast);
    ProcessPodcast::dispatchIf($accountActive, $podcast);
    ProcessPodcast::dispatchUnless($accountSuspended, $podcast);

    // Delayed dispatch:
    ProcessPodcast::dispatch($podcast)->delay(now()->plus(minutes: 10)); // Should not be available for processing by queue worker until 10 minutes after it has been dispatched.
    // If any default delay have like Amazon SQS have max 15 mins delay, we can ignore it: ->withoutDelay().

    // Synchronous dispatching:
    ProcessPodcast::dispatchSync($podcast); // Dispatch a job immediately.
    // Job will not be queued and will be executed immediately within the current process.
    RecordDelivery::dispatch($order)->onConnection('deferred'); // Synchronous but in background after sending response to the user.
    RecordDelivery::dispatch($order)->onConnection('background'); // Processed in a separately spawned PHP process, allowing the PHP-FPM / application worker to be available to handle another incoming HTTP request.

    // Dispatch in database transaction:
    // Job may not process after a commit.
    // Add this in connection in queue config: 'after_commit' => true
    // Now we can dispatch a job in a database transaction. or set after commit directly:
    ProcessPodcast::dispatch($podcast)->afterCommit(); // ->beforeCommit()

    // Job Chaining:
    // Run in sequence after the primary job has executed successfully.
    // If one job in the sequence fails, the rest of the jobs will not be run. Use Bus Facade:
    Bus::chain([new ProcessPodcast, new OptimizePodcast, function(){
        // Can pass closue also. 
        // Should not use the $this variable within chain callbacks.
    }])->dispatch(); // Can use ->onConnection('redis')->onQueue('podcasts') then dispatch.
    // Chain Failures: ->catch(function (Throwable $e) {}
    // $this->delete() method within the job will not prevent chained jobs from being processed.
    // The chain will only stop executing if a job in the chain fails.
}