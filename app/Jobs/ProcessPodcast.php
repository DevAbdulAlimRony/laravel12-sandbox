<?php

namespace App\Jobs;

use App\Jobs\TranscribePodcast;
use App\Models\Podcast;
use App\Services\AudioProcessor;
use DateTime;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Collection;
use Illuminate\Queue\Middleware\WithoutOverlapping;

// Expanded PHP attributes from Laravel 13:
#[Connection('redis')]
#[Queue('long_running_tasks')]
#[Tries(5)]
#[Timeout(120)]
#[Backoff([10, 30, 60])] // Retry after 10s, then 30s, then 60s
#[MaxExceptions(3)]

class ProcessPodcast implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    use Batchable; // Make a job as batchable.

    // Create a new Job Instance,
    public function __construct(
        // If different queue: $this->onQueue('processing'). Or call onQueue before dispatch in controller.
        // Same goes for connection: $this->onConnection('sqs')

        public Podcast $podcast, // Can pass eloquent model because of Queueable trait.
        // Eloquent models and their loaded relationships will be gracefully serialized and unserialized when the job is processing.
        // Can access the model's properties and relationships as usual when the job is processed. $podcast->title.
        // Binary data, such as raw image contents, should be passed through the base64_encode function before being passed to a queued job, if not serialization wont be perfect.
    ) 
    {
        // Because all loaded Eloquent model relationships also get serialized when a job is queued, the serialized job string can sometimes become quite large.
        // Dont load any relationship: $this->podcast = $podcast->withoutRelations();
        // If use constructor property pomotion without relationship, just above the Podcast $podcast property: #[WithoutRelations]
        // If we inject multiple model, we can call #[WithoutRelations] above ProcessPodcast class to serialize all models without relationships.
    }

    // Execute the Job
    // we are able to type-hint dependencies on the handle method of the job, automatic binding.
    public function handle(AudioProcessor $processor): void
    {
        // Implementation here...

        // Job Chaining: See Queue controller. 
        $this->prependToChain(new TranscribePodcast); // Run job immediately after current job
        $this->appendToChain(new TranscribePodcast);

        // When a job exception thrown, it will be released onto the queue until max attempts meet.
        // When you "release" a job, you are essentially telling the queue worker: "I can't do this right now. Put this ticket back on the pile and I'll try again later.
        // Manually release a job:
        $this->release();
        $this->release(10); // 10s before it will be attempted again.

        // Manually failing a job:
        $this->fail();
        $this->fail(new Exception('Something went wrong'));

        // Work with Batchable Job:
        if() {$this->batch()->cancel();}
        if($this->batch()->cancelled()){
            return;
        }
        
        // Can call another job belongs to the same job, useful when we need so many batches.
        // Scenario: You need to generate 1,000 PDF reports. Adding them one by one to a batch is slow; you can add them in bulk.
        $this->batch()->add(Collection::times(1000, function () {
            return new ImportContacts;
        })); // do 1000 jobs.
    }

    //* Unique Job: 
    // In a standard queue, if a user clicks a button 10 times, the queue will process that task 10 times.
    // Usually, that's fine. But sometimes, duplicate tasks cause "race conditions," data corruption, or wasted server resources.
    // A Unique Job is a specific type of Laravel queue job that ensures only one instance of that job is waiting in the queue at any given time.
    // If you try to dispatch the same job multiple times with the same parameters, Laravel will look at the queue and say, "Wait, I'm already planning to do this. I'll ignore this new request."
    // Unique jobs require a cache driver that supports locks. 
    // memcached, redis, dynamodb, database, file, and array cache drivers support atomic locks.
    //* use shouldBeUnique interface.
    // Can define duration:
    public $uniqueFor = 3600;
    // Can define uniqueness key:
    public function uniqueId(): string
    {
        return $this->podcast->id;
    }
    // Unique jobs are "unlocked" after a job completes processing or fails all of its retry attempts. 
    // Unlock immediately before it is processed: implements ShouldBeUniqueUntilProcessing interface.
    // If we want to handle unique job locking using another drive rather than default cache driver:
    public function uniqueVia(): Repository
    {
        return Cache::driver('redis');
    }

    //* Encrypted Job:
    // To ensure privacy and security, Laravel allows you to encrypt the data of your queued jobs. This means that the job's payload is encrypted before being stored in the queue and decrypted when the job is processed.
    // implements ShouldQueue, ShouldBeEncrypted

    //* Job Middleware:
    public function middleware(): array{
        // Rate Limiting:
        // Set Rate Limiter in a ServiceProvider, let's say backups.
        // Each time the job exceeds the rate limit, this middleware will release the job back to the queue with an appropriate delay based on the rate limit duration
        return [new RateLimited('backups')->releaseAfter(60)];
        // Can use tries, retryUntil method, and maxExceptions properties, releaseAfter( elapse before the released job will be attempted again)
        // If we dont want retries: ->dontRelease()
        // Fine tuned for redis: [new RateLimitedWithRedis('backups')
        // Can use ->connection('limiter') also to specify which redis connection.

        // Preventing Overlaps:
        // Helpful when a queued job is modifying a resource that should only be modified by one job at a time.
        // Let's say want to prevent credit score update job overlaps for the same user ID.
        return [new WithoutOverlapping($this->user->id)]; // Can use releaseAfter()
        // new WithoutOverlapping($this->order->id))->dontRelease(): Immediately delete any overlapping jobs so that they will not be retried.
        // If unexpectedly cant. atomic lock feature failed: ->expireAfter(180)
        // By default, it will only prevent overlapping jobs of the same class. . But we can do for multiple class:
        // (new WithoutOverlapping("status:{$this->provider}"))->shared()

        // Throttle:
        // Useful when: Throttling is a way to limit how many times an action can be performed within a specific timeframe.
        // Imagine a queued job that interacts with a third-party API that begins throwing exceptions again and again.
        return [new ThrottlesExceptions(10, 5 * 60)]; // implement retryUntil() method also
        // ->backOff(5): Number of minutes such a job should be delayed.
        // ->by('key'): share a common throttling "bucket" ensuring they respect a single shared limit
        // ->when(fc()): dont throw every exception, just what when specifies.
        // Delete the job entirely when a given exception occurs: ->deleteWhen(CustomerDeletedException::class)]
        // ->report(fn()): Report throttled exception using app's exception handler.
        // For Redis: new ThrottlesExceptionsWithRedis(10, 10 * 60). Can use connection() also.

        // Skipping Jobs:
        // Skip or Delete the Job when specific condition meets:
        return [Skip::when($condition)]; // Skip::unless(). Can pass callback for condition.

        return [new SkipIfBatchCancelled]; // Skip the job if its batch has been cancelled.

        // Failing Jobs on Specific Exceptions:
        return [new FailOnException([AuthorizationException::class])];
    }

    //* Max Attempts:
    // When a job is dispatched, it is pushed onto the queue. A worker then picks it up and attempts to execute it. This is a job attempt.
    // Job can be successful, can encounter exception, can release, can fails or withOutOverlappings, can be timed out.
    // We can specify how many times or for how long a job may be attempted.
    // php artisan queue:work --tries=3
    // If a job exceeds its maximum number of attempts, it will be considered a "failed" job.
    public $tries = 5; // Max attempts before it fails. Can also define retryUntil() method to specify how long a job may be attempted.
    // or get dynamic control:
    public function tries(): int{
        return 5;
    }
    public function retryUntil(): DateTime{
        return now()->addMinutes(30);
    }
    // If tries() and retryUntil() both exist, retryUntil() will take precedence.
    // If you run the command php artisan queue:work, it defaults to 0, which technically means "keep trying forever" if the job keeps failing.
    // So, we should give how many tries when necessary.

    public $maxExceptions = 3; // Should fail if the retries are triggered by a given number of unhandled exceptions.
    
    // By default, the timeout value is 60 seconds, how long you expect your queued jobs to take.
    // Custom timeout: php artisan queue:work --timeout=30. or,
    public $timeout = 120;
    // The PCNTL PHP extension must be installed in order to specify job timeouts.
    // For IO blocking processes such as sockets or outgoing HTTP connections  like Guzzle, should always attempt to specify a timeout using their APIs as well.
    
    // By default, when a job times out, it consumes one attempt and is released back to the queue (if retries are allowed). 
    public $failOnTimeout = true; // Mark as failed job if timeout. Or if need more complex logic:
    
    public function backoff(): int
    {
        return 3;

        // 1 second for the first retry, 5 seconds for the second retry, 10 seconds for the third retry, and 10 seconds for every subsequent retry if there are more attempts remaining
        return [1, 5, 10];
    }

    public $backoff = 3; // The number of seconds to wait before retrying the job.

    //* SQS FIFO:
    // Laravel supports Amazon SQS FIFO (First-In-First-Out) queues, allowing
    // Strict Ordering: Jobs are processed exactly in the order they were sent.
    // Exactly-Once Processing: It automatically removes duplicate jobs (de-duplication) within a 5-minute window.
    // Imagine user deposit 100Taka and withdraw 50 taka. If we have multiple workers, withdraw might be start processing first and it will be problematic.
     public function deduplicationId(): string
     {
        return "renewal-{$this->subscription->id}";
     }
     // When call(This is where SQS FIFO called): ->onGroup('invoices')->withDeduplicator(fn () => 'invoices-'.$invoice->id);
     // For FIFO event listener, define messageGroup(), duplicationId() in listener.

     //* Failover:
     // If one connection failed, another connection will be used, this ensures high availability in production.
     // In config/queue.php, specify the failover driver  and provide an array of connection names to attempt in order.
     // if failover deafault, In .env, QUEUE_CONNECTION=failover.
     // php artisan queue:work redis, php artisan queue:work database.

     //* Handling failed job:
     // Laravel automatically have a migration file for failed job table, if job failed, it will stored in that table. If migration not have:
     // php artisan make:queue-failed-table
     public function failed(?Throwable $exception): void
     {
        // Send user notification of failure, etc...
     } // Based on criterias, MaxAttemptsExceededException and timeoutExceededException instance will be thrown.
    // List of failed jobs in database: php artisan queue:failed
    // php artisan queue:retry ce7bb17c-cdd8-41f0-a8ec-7b4fef4e5ece
    // php artisan queue:retry --queue=name
    // php artisan queue:retry all
    // Delete failed job: php artisan queue:forget 91401d2c-0784-4f43-824c-34f94a33c24d
    // Delete all failed jobs: php artisan queue:flush, php artisan queue:flush --hours=48
    public $deleteWhenMissingModels = true; //  Delete the job if its models no longer exist.
    // We can prune failed jobs, by default it takes 24 hours to prune.
    // We can store failed jobs in Dynamodb.
    // Discard failed jobs without storing: In env: QUEUE_FAILED_DRIVER=null
    // We can make an event for failed job and register in Service Provider's boot: Queue::failing(function (JobFailed $event) {});
}