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
    dispatch((new Job)->onQueue('high')); // Set priority using high low, can do in queue config file also.
    dispatch(function() use ($podcast) {})->name('Publish Podcast')->catch(function (Throwable $e) {}); // Dispatching callback instantly rather than job. name and catch optional.

    // Delayed dispatch:
    ProcessPodcast::dispatch($podcast)->delay(now()->plus(minutes: 10)); // Should not be available for processing by queue worker until 10 minutes after it has been dispatched.
    // If any default delay have like Amazon SQS have max 15 mins delay, we can ignore it: ->withoutDelay().

    // Synchronous dispatching:
    // Do not need to run worker for them, cause they process jobs within the current PHP process.
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

    // SQS FIFO Calling:
    ProcessOrder::dispatch($order)->onGroup("customer-{$order->customer_id}");

    //* Job Batching:
    // Execute a batch of jobs and then perform some action when the batch of jobs has completed executing.
    // php artisan make:queue-batches-table
    // Use Batchable trait in job class.
    // When running multiple queue workers, the jobs in the batch will be processed in parallel.
    $batch = Bus::batch([new ImportCsv(1, 100), new ImportCsv(101, 200), 
                new ImportCsv(201, 300)
    ])->before(function (Batch $batch){
        // The batch has been created but no jobs have been added...
    })->progress(function (Batch $batch) {
        // A single job has completed successfully...
    })->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->catch(function (Batch $batch, Throwable $e) {
        // Batch job failure detected...
    })->finally(function (Batch $batch) {
        // The batch has finished executing...
    })
    ->name('Import CSV')// Name will be used in Horizon, telescope etc.
    ->allowfailures(); // By default, When a job within a batch fails, Laravel will automatically mark the batch as "cancelled". allowFailures prevents this.
    // Can use callback: allowFailures(function (Batch $batch, $exception) 
    ->dispatch();
    // Do not use $this in callbacks, database statements that trigger implicit commits should not be executed within the jobs.
    // Can use: onConnection(), onQueue() etc. before dispatching.
    // Chaining: Bus::batch([..jobs], [..other jobs])
    // Bus::chain(new Job1, Bus::batch([new Job2()]), Bus::batch());
    // Inspecting Batch: $batch->id (uuid of the batch), ->name, ->totalJobs, ->pendingJobs, ->failedJobs, ->processedJobs(), ->cancel(), ->cancelled(), ->finished(), ->progress().

    // Retrying Failed Batch Jobs: php artisan queue:retry-batch 32dbc76c-4f82-4749-b610-a639fe0099b5

    //* Pruning Batches:
    // By default, all finished batches that are more than 24 hours old will be pruned. Customize it:
    // Schedule a command: Schedule::command('queue:prune-batches --hours=48')->daily();
    // Prune unfinished job which failed and never retries successfully: command('queue:prune-batches --hours=48 --unfinished=72')
    // Prune Cancelled Batches: command('queue:prune-batches --hours=48 --cancelled=72')

    //* Storing batches in DynamoDB:
    // The job_batches table should have a string primary partition key named application and a string primary sort key named id. 
    // For automatic batch pruning, may define ttl attribute.
    // Install the AWS SDK: composer require aws/aws-sdk-php
    // Set the queue.batching.driver configuration option's value to dynamodb.
    // Define key, secret, and region configuration options within the batching configuration array.

    //* Queue Worker:
    // A queue worker is a long-running process that listens to a specified queue and processes jobs.
    // Start a worker: php artisan queue:work
    // It will continue to run until it is manually stopped or you close your terminal.
    // Queue workers are long-lived process. During deployment, restart the worker: php artisan queue:restart
    // Include job id-connection-queue in the output: php artisan queue:work -v
    // Dont need reset the state but less efficient to run worker: php artisan queue:listen
    // Specifying connection: php artisan queue:work redis
    // Specify particular queue for a connection: php artisan queue:work redis --queue=emails
    // Process a single job: php artisan queue:work --once
    // Run 1000 the exit: php artisan queue:work --max-jobs=1000
    // Process all jobs hen exit (useful for docker container- shutdown the container after queue empty): php artisan queue:work --stop-when-empty
    // Process jobs for one hour and then exit: php artisan queue:work --max-time=3600
    // php artisan queue:work --sleep=3
    // Run worker in maintenance mode: php artisan queue:work --force
    // Priority- high queue jobs are processed before continuing to any jobs on the low queue: php artisan queue:work --queue=high,low
    // Job Expiration: Set retry_after in config file.
    // php artisan queue:work --timeout=60. Default is 60 always.
    // php artisan queue:work redis --tries=3 --backoff=3
    // The --timeout value should always be at least several seconds shorter than your retry_after configuration value.
    //  If your --timeout option is longer than your retry_after configuration value, your jobs may be processed twice.
    // Pause (Exmp in system maintenance): php artisan queue:pause database:
    // Resume: php artisan queue:continue database:default
    // May disable restart or pause polling individually by setting the static $restartable or $pausable properties on the Illuminate\Queue\Worker class.
    // When interruption polling is disabled, workers will not respond to queue:restart or queue:pause commands.

    //* Supervisor:
    // When we are dispatching the job, its just storing the job in the queue, but we need a process to run the worker to process the jobs.
    // Thats the wroker to process the job, but we need to make sure that the worker is always running, even if it crashes or the server restarts. 
    // Use Supervisor to keep php artisan queue:work running 24/7.
    // Supervisor is a process monitor for the Linux operating system, and will automatically restart your queue:work processes if they fail.
    // Install in Ubuntu: sudo apt-get install supervisor. Rather than manually manage, we can use Laravel Cloud also.
    // Create a configuration file for your queue worker: /etc/supervisor/conf.d/custom-name-worker.conf
    // Start supervisor: sudo supervisorctl reread, sudo supervisorctl update, sudo supervisorctl start custom-name-worker:*

    //* Clear Jobs in Queue: (only available for the SQS, Redis, and database queue drivers.)
    // php artisan queue:clear
    // php artisan queue:clear redis --queue=emails

    //* Monitoring Queues:
    // php artisan queue:monitor redis:default,redis:deployments --max=100
    // Scheduling this command alone is not enough to trigger a notification alerting you of the queue's overwhelmed status. 
    // In AppServiceProvider, listen to the Illuminate\Queue\Events\QueueBusy event.

    //* Testing: See in documentation.
}