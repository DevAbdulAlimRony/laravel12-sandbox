<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class SendShipmentNotification implements ShouldQueue, ShouldQueueAfterCommit, ShouldBeEncrypted, ShouldBeUnique
{
    use InteractsWithQueue; // To manually interact with queue delete and release
    // ShouldQueueAfterCommit is for while working with db transaction if queued listener should be dispatched after all open database transactions have been committed.
    // impplements ShouldBeEncrypted interface if need data security encryption and integretiy.

    public function __construct() {}

    // Here, we can perform any operation.
    public function handle(OrderShipped $event): void
    {
        // Access the order using $event->order...
        // If we want to stop propagation of OrderShipped event to other listeners, we can return false here.

        // Manually interact with queue job's delete and release methods: At first define trait InteractsWithQueue.
        if ($condition) {
            $this->release(30);
        }
    }

    //* Queue Listener:
    // For shouldQueue interface,  the listener will automatically be queued by the event dispatcher.
    // The queued job will automatically be deleted after it has finished processing.
    
    // Can customize default properties:
    public $connection = 'sqs';
    public $queue = 'listeners';
    public $delay = 60;
    public $tries = 5; // max attempts
    public $backoff = 3; // number of seconds to wait before retrying the queued listener.
    public $maxExceptions = 3;
    public $timeout = 120; 
    public $failOnTimeout = true; // Indicate if the listener should be marked as failed on timeout.

    // Define in Runtime:
    public function viaConnection(): string
    {
        return 'sqs';
    }
    public function viaQueue(): string
    {
        return 'listeners';
    }
    public function withDelay(OrderShipped $event): int
    {
        return $event->highPriority ? 0 : 60;
    }
    public function retryUntil(): DateTime
    {
        return now()->plus(minutes: 5);
    }
    public function backoff(OrderShipped $event): int
    {
        return 3;
        // return [1, 5, 10];
    }

    // Sometimes need to determine whether the listener should be queued based on condition:
    public function shouldQueue(OrderCreated $event): bool{
        return $event->order->subtotal >= 5000;
    }

    // Using queue middleware:
    public function middleware(OrderShipped $event): array{
        return [new RateLimited];
    }

    // Sometimes, we may want to ensure that only one instance of a specific listener is on the queue at any point in time.
    // Implement interface  ShouldBeUnique.
    public function __invoke(LicenseSaved $event): void{}
    // the listener will not be queued if another instance of the listener is already on the queue and has not finished processing.
    public $uniqueFor = 3600; // The number of seconds after which the listener's unique lock will be released.
    // Get the unique ID for the listener for atomic lock:
    public function uniqueId(LicenseSaved $event): string {
        return 'listener:'.$event->license->id;
        // So, any new dispatches of the listener for the same license will be ignored until the existing listener has completed processing. 
    }

    // There may be situations where you would like your listener to unlock immediately before it is processed weather its success or failed:
    // implements ShouldBeUniqueUntilProcessing interface.

    // when a ShouldBeUnique listener is dispatched, Laravel attempts to acquire a lock with the uniqueId key.
    // If the lock is already held, the listener is not dispatched. 
    // This lock is released when the listener completes processing or fails all of its retry attempts.
    // Use another driver rather than default to hold the lock key:
    public function uniqueVia(LicenseSaved $event): Repository{
        return Cache::driver('redis');
    }
    // If want concurrent processing of a listener, use the WithoutOverlapping job middleware rather than uniqueQueue.


    // Handling failed queued job event:
    public function failed(OrderShipped $event, Throwable $exception): void{}
}