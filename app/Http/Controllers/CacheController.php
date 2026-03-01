<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class CacheController {
    // By default laravel use database driver for caching
    // We can configure in config/cache.php. We have so many options memecached, redis, dynamodb etc.
    // array and null cache drivers are good for automated test.
    // For database driver: php artisan make:cache-table.
    // To use Redis driver: Install the PhpRedis extension via PECL or install predis/predis via composer. Laravel sail, cloud and forge alreday have predis installed.
    // The failover cache driver provides automatic failover functionality when interacting with the cache.
    // To configure a failover cache store, specify the failover driver and provide an array of store names to attempt in order. 
    // Add failover as default in env: CACHE_STORE=failover
    // We can also create our own custom cache driver in App\Extensions implementing Store interface

    Cache::has('key'); // Check if cache key exists.
    
    //* Retriving cache:
    Cache::get('key'); // If not found, null returned.
    Cache::get('key', 'default');
    Cache::get('key', function() {return User::all();}) // Closure as default value.
    cache('key'); // Using helper function
    Cache::pull('key', 'default'); // Retrive and delete.

    //* Increment or decrement the integer value...
    Cache::increment('key');
    Cache::increment('key', $amount);
    // Same goes for decrement.

    //* Storing items in Cache:
    Cache::put('key', 'value', $seconds = 10); // If no time provided, store indefinitely.
    cache(['key' => 'value'], now()->plus(minutes: 10)); // Using helper function. Same helper function to get but we provided value and time.
    Cache::add('key', 'value', $seconds); // Store if already not present, return true if added or false if not added.
    Cache::forever('key', 'value'); // Store forever, no expiration time.
    Cache::store('redis')->put('bar', 'baz', 600); // 10 Minutes in a driver

    //* Remove Items from Cache:
    Cache::forget('key');
    Cache::put('key', 'value', 0); // zero or negative expiration time will remove the cache actually.
    Cache::put('key', 'value', -5);
    Cache::flush(); // Clear the entire cache.

    //* Try to retrive from cache, if not exists, then put in cache:
    Cache::remember('users', $seconds, function () { return User::all(); });
    // Store forever if does not exist: rememberForever()
    // Using helper: cache()->remember()

    //* Stale while Revalidate:
    // Cache::remember can slow down user experience if cache expired.
    // The flexible method accepts an array that specifies how long the cached value is considered "fresh" and when it becomes "stale".
    // If a request is made within the fresh period (before the first value), the cache is returned immediately without recalculation.
    // If a request is made during the stale period (between the two values), the stale value is served to the user.
    // A deferred function is registered to refresh the cached value after the response is sent to the user.
    Cache::flexible('users', [5, 10], function () {});

    //* Cache Memoization:
    // Laravel's memo cache driver allows you to temporarily store resolved cache values in memory during a single request or job execution.
    //  This prevents repeated cache hits within the same execution, significantly improving performance.
    Cache::memo()->get('key'); // Now if we again retrive cache in same request, it will not hit the cache, it will get from temporary memo.
    Cache::memo('redis')->get('key'); // If not default driver.
    Cache::memo()->put('name', 'Taylor'); // store cache and forget memo automatically, now if we get the cache it will hit the cache.

    //* Cache tags:
    // Tag related items in the cache and then flush all cached values that have been assigned a given tag.
    // Not supported for file, dynamodb or database driver.
    Cache::tags(['people', 'artists'])->put('John', $john, $seconds); // store tagged cache. people and artists are tag for it, if we flush any of them cache will be cleared.
    Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
    Cache::tags(['people', 'artists'])->get('John'); // Retrive tagged cache.
    Cache::tags(['people', 'authors'])->flush(); // Clear all people and authors tagged cache key.

    //* Atomic Lock:
    // Let's say a user doing refund, while loading same user or another admin clicks again refund, that will be problematic. We can lock the loading.
    // Supported on memcached, redis, dynamodb, database, file or array.
    // Atomic locks allow for the manipulation of distributed locks without worrying about race conditions. 
    $lock = Cache::lock('refund_user_123', 10); // Lock name, expires in 10 seconds
    if ($lock->get()) {
        // ---- CRITICAL SECTION START ----
        // Only ONE process can be in here at a time.
        $paymentService->refund($order);
        // ---- CRITICAL SECTION END ----
        
        $lock->release();
    } else {
        return "Please wait, another admin is already processing this refund.";
    }
    // get() can take callback. get(function(){}): After the closure is executed, Laravel will automatically release the lock.
    
    // Wait for specific seconds if loc not available using block:
    try{
        $lock->block(5);
        //.....
    }
    catch(LockTimeoutException $e){}
    finally{$lock->release();}

    // or, just using block callback:
    Cache::lock('foo', 10)->block(5, function () {});

    // Lock across process
    // Acquire a lock in one process and release it in another process. Let's say we lock in controller release in a queue job.
    // In queue Job class: Cache::restoreLock('processing', $this->owner)->release(); forceRelease().

    // The withoutOverlapping method provides a simple syntax for executing a given closure while holding an atomic lock.
    Cache::withoutOverlapping('foo', function () {
        // The lock will not be released until the closure finishes executing
        // Lock acquired after waiting a maximum of 10 seconds...
    }); // Can give lockFor: 120, waitFor: 5 arguments also.

    //* Events:
    // CacheFlushed, CacheFlushing, CacheHit, CacheMissed, ForgettingKey, KeyForgetFailed, KeyForgotten, KeyWriteFailed, KeyWritten, RetrievingKey, RetrievingManyKeys, WritingKey, WritingManyKeys
    // To increase performance we can disable those events if not necessary: In config->in driver-> 'events' => false.  

    //* Cache policy:
    // A Cache Policy is a set of rules that defines what data gets stored, how long it stays there (TTL - Time To Live), and when it should be deleted or updated (Invalidation). Short Term Memory.
    // Without a policy, your cache either becomes stale (showing old data) or fills up with useless junk that slows down your server.
    // Should determine your policy based on the Read-to-Write Ratio and Data Volatility.
    // If high volatility like stock prices, live sports scores then cache for 1-60 seconds, if low volatility like country lists, blog posts or site settings then cache for 24 hours or forever.
    // Is the data unique to a user? (Cache per user) or Global? (Cache for everyone).
    // Can a blog comment show up 30 seconds late? Yes. Can a bank balance be 30 seconds late? No.
    // Is the data expensive to generate? (e.g., a complex SQL query with 5 joins).
    // Do not cache high sensitive data, real time transactions, small or fast queries.
}
