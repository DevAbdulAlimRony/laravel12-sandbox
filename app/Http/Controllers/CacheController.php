<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class CacheController {
    // By default, laravel uses database driver for caching
    // We can configure in config/cache.php. We have so many options memecached, redis, dynamodb etc.
    // array and null cache drivers are good for automated test.
    // For database driver: php artisan make:cache-table.
    // To use Redis driver: Install the PhpRedis extension via PECL or install predis/predis via composer. 
    // phpredis has the advantage that it allows you to connect to Redis from any PHP project that is executed in that computer, whereas predis, being a library, only allows to connect from the projects where it's installed.
    // Laravel sail, cloud and forge alreday have predis installed.
    // We can use Redis Insight in windows to monitor Redis server, keys, memory usage, performance etc. It is a GUI tool for Redis.
    // The failover cache driver provides automatic failover functionality when interacting with the cache.
    // To configure a failover cache store, specify the failover driver and provide an array of store names to attempt in order. 
    // Add failover as default in env: CACHE_STORE=failover
    // We can also create our own custom cache driver in App\Extensions implementing Store interface.
    // After installing things and configuring, clear config cache and optimize clear if need.

    // By Redis, The cache is stored in your server's RAM (Random Access Memory).
    // RAM is significantly faster than a Hard Drive or SSD. This is why Redis can handle hundreds of thousands of operations per second with almost zero "latency" (delay).
    // If you install Redis directly on your production web server, then 127.0.0.1 (which means "this local machine") will work in env configuration.
    // In professional production environments, Redis often sits on a separate server to save RAM on your web server. In that case, your configuration would need to change.

    Cache::has('key'); // Check if cache key exists.

    //* Storing items in Cache:
    Cache::put('key', 'value', $seconds = 10); // Can without expiration time also. Forevere.
    Cache::putMany(['key1' => 'value1', 'key2' => 'value2'], $seconds);
    cache(['key' => 'value'], now()->plus(minutes: 10)); // Using helper function. Same helper function to get but we provided value and time.
    Cache::add('key', 'value', $seconds); // Store if already not present, return true if added or false if not added.
    Cache::forever('key', 'value'); // Store forever, no expiration time.
    Cache::store('redis')->put('bar', 'baz', 600); // 10 Minutes in a driver.
    
    //* Retriving cache:
    Cache::get('key'); // If not found, null returned. Doesnt matter Cache expired or not.
    Cache::get('key', 'default');
    Cache::get('key', function() {return User::all();}) // Closure as default value.
    cache('key'); // Using helper function.
    
    //* Increment or decrement the integer value...
    Cache::increment('key'); // If not integer, nothing will happen.
    Cache::increment('key', $amount);
    // Same goes for decrement.

    //* Try to retrive from cache, if not exists, then put in cache:
    Cache::remember('users', $seconds, function () { return User::all(); });
    // Store forever if does not exist: rememberForever()
    // Using helper: cache()->remember().

    //* Stale while Revalidate (Use when time consuming query or data):
    // In a standard cache setup, when an item is expired, the very next user has to wait to recalculate.
    // If the calculation take 5 seconds and 100 users hit at the same time, all users will wait.
    // Cache::remember can slow down user experience if cache expired. flexible It do both(Get or Update) as remember do, but:
    // The flexible method accepts an array that specifies how long the cached value is considered "fresh" and when it becomes "stale".
    // If a request is made within the fresh period (before the first value), the cache is returned immediately without recalculation.
    // If a request is made during the stale period (between the two values), the stale value is served to the user.
    // A deferred function is registered to refresh the cached value after the response is sent to the user.
    Cache::flexible('users', [5, 10], function () {}); // Show old for 5 seconds, do task in background, then show fresh in 10 seconds.
    // Since Cache::flexible() is fast because it sends old data immediately, your Vue application can handle this by checking if the data is "stale" and silently updating the UI when the fresh data arrives.
    // But if you always want fresh data, then use remember.

    //* Cache Memoization (If same cache key is used within a single request):
    // Laravel's memo cache driver allows you to temporarily store resolved cache values in memory during a single request or job execution.
    // This prevents repeated cache hits within the same execution, significantly improving performance.
    Cache::memo()->get('key'); // Now if we again retrive cache in same request, it will not hit the cache, it will get from temporary memo.
    Cache::memo('redis')->get('key'); // If not default driver.
    Cache::memo()->put('name', 'Taylor'); // store cache and forget memo automatically, now if we get the cache it will hit the cache driver, not memo.

    //* Update Expiration Time:
    // Previously, if you wanted to extend the Time-To-Live (TTL) of a cached item, you had to retrieve the payload from the cache store and then PUT it back. 
    // This resulted in unnecessary memory usage and network transfer, especially for large cached objects.
    // Laravel 13 bypasses this completely by utilizing native cache store commands (like Redis EXPIRE or Memcached TOUCH).
    // Rather than get and put again:
    Cache::touch('heavy_analytics_dashboard', now()->addHours(6));
    Cache::touch('user_session:123', 3600);
    Cache::touch('permanent_report', null);

    //* Remove Items from Cache:
    Cache::pull('key', 'default'); // Retrive and delete, Use case: process data exactly once then remove from cache.
    Cache::forget('key');
    Cache::put('key', 'value', 0); // zero or negative expiration time will remove the cache actually.
    Cache::put('key', 'value', -5);
    Cache::flush(); // Clear the entire cache.

    //* Cache tags- Flush just certain keys
    // Tag related items in the cache and then flush all cached values that have been assigned a given tag.
    // Not supported for file, dynamodb or database driver.
    Cache::tags(['people', 'artists'])->put('John', $john, $seconds); // store tagged cache. people and artists are tag for it, if we flush any of them cache will be cleared.
    Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
    Cache::tags(['people', 'artists'])->get('John'); // Retrive tagged cache.
    Cache::tags(['people', 'authors'])->flush(); // Clear all people and authors tagged cache key.
    Cache::tags(['user:' . $user->id, 'users'])->flush();

    //* Caching Paginated Results:
    Cache::remember('users_page' . Request::input('page', 1), $seconds, function () {
        return User::paginate(15);
    });
    // You have to clear the cache for each observable event (created, updated, deleted) to keep the cache fresh. You can use model events or observers to automate this process.
    // You have to manually looping over all pages key starts with users_page_, and clear it or Can use Cache Tags.
    // Cache::tags(['user-pagination'])->put('user-page-1', $data).
    // But caching paginate data or complex dynamic data is not good.
    
    //* Handling Cache Busting:
    $cacheKey = "products:listing:v1.2"; // Include version to the cache key
    key = "user:{$user->id}:profile:{$user->updated_at->timestamp}"; // Model based cache key with timestamp.
    // Event-driven cache invalidation using Laravel’s event system.

    //* Cache Warming Strategies:
    // Cache warming (or priming) involves populating your cache before it’s needed, preventing initial slow responses. 
    foreach ($popularProducts as $product) {
        Cache::remember("product:{$product->id}", 60 * 60, function() use ($product) {
                return $product->load('categories', 'images', 'reviews');
        });
    } // Warm the most viewed products.
    // Now make a custom command to call that code, actually the method where this warmer code written.
    // Now, that command can be triggerized during deployment, as a schedule task or after significant data updates.

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
    // Cache::lock('users', 10)->get(function(){...refund});
    
    //* Lock Block:
    // Wait for specific seconds if loc not available using block:
    try{
        $lock->block(5);
        //.....
    }
    catch(LockTimeoutException $e){}
    finally{$lock->release();}

    // or, just using block callback:
    Cache::lock('foo', 10)->block(5, function () {}); // User2, -Wait up for 5 seconds for the lock to become free, User1 is trying at that time.
    // If in 5 seconds not free, then maybe say to the user Srver busy, try again.

    //* Lock across process
    // Acquire a lock in one process and release it in another process. Let's say we lock in controller, release in a queue job.
    // In queue Job class: 
    Cache::restoreLock('processing', $this->owner)->release(); 
    Cache::forceRelease();

    // The withoutOverlapping method provides a simple syntax for executing a given closure while holding an atomic lock.
    Cache::withoutOverlapping('foo', function () {
        // The lock will not be released until the closure finishes executing
        // Lock acquired after waiting a maximum of 10 seconds...
    }); // Can give lockFor: 120, waitFor: 5 arguments also.

    // High-scale apps often use a Cache::lock() to quickly bounce away duplicate requests at the front door, and then use lockForUpdate() inside the database for final security.

    //* Preventing Cache Stampedes:
    // Cache stampedes occur when many concurrent requests try to regenerate the same expired cache item simultaneously. 
    // Use atomic lock then Cache.
    Cache::lock('lock:expensive-calculation', 10)->block(5, function () {
        return Cache::remember('expensive-calculation', 60 * 60, function () {
            return $this->performExpensiveCalculation();
        });
    });

    //* Events:
    // CacheFlushed, CacheFlushing, CacheHit, CacheMissed, ForgettingKey, KeyForgetFailed, KeyForgotten, KeyWriteFailed, KeyWritten, RetrievingKey, RetrievingManyKeys, WritingKey, WritingManyKeys
    // To increase performance, we can disable those events if not necessary: In config->in driver-> 'events' => false.  

    //* Cache policy:
    // A Cache Policy is a set of rules that defines what data gets stored, how long it stays there (TTL - Time To Live), and when it should be deleted or updated (Invalidation). Short Term Memory.
    // Without a policy, your cache either becomes stale (showing old data) or fills up with useless junk that slows down your server.
    // Should determine your policy based on the Read-to-Write Ratio and Data Volatility.
    // If high volatility like stock prices, live sports scores then cache for 1-60 seconds, if low volatility like country lists, blog posts or site settings then cache for 24 hours or forever.
    // Is the data unique to a user? (Cache per user) or Global? (Cache for everyone).
    // Can a blog comment show up 30 seconds late? Yes. Can a bank balance be 30 seconds late? No.
    // Is the data expensive to generate? (e.g., a complex SQL query with 5 joins).
    // Do not cache high sensitive data, real time transactions, small or fast queries.

    //* Debugging and Monitoring:
    // The Redis command-line interface (redis-cli) provides powerful tools for inspecting and debugging your cache:
    // Connect to redis: redis-cli -h 127.0.0.1 -p 6379
    // List all keys matching a pattern: KEYS laravel_cache*
    // Get information about a specific key: TYPE laravel_cache:user:1, TTL laravel_cache:user:1, GET laravel_cache:user:1
    // MEMORY USAGE laravel_cache:user:1, INFO MEMORY
    // MONITOR, DEL laravel_cache:user:1
    // Can monitor using telescope and then enable TELESCOPE_CACHE_WATCHER
    // Can use Admin tool: composer require erik-dubbelboer/php-redis-admin, npm install -g redis-commander, redis-commander.
    // Can use Laravel horizon to monitor.
}

// We can make cache Query Builder then can use it:
class CachedQueryBuilder
{
    protected $minutes = 60;
    protected $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function get($columns = ['*'])
    {
        $query = $this->model->getQuery();
        $key = 'query:' . md5(serialize([
            $query->toSql(),
            $query->getBindings(),
            $columns
        ]));
        
        return Cache::remember($key, $this->minutes, function() use ($query, $columns) {
            return $query->get($columns);
        });
    }
}

// Usage
$builder = new CachedQueryBuilder(Product::where('active', true));
$products = $builder->get();

//* Laravel Telescope to monitor cache and other things.
class Telescope(){
        // Telescope provides insight into the requests coming into your application, exceptions, log entries, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.
        // Install: composer require laravel/telescope. 
        // If only need local, add: --dev and remove TelescopeServiceProvider from bootstrap/providers.php. To prevent auto discover, in composer.json: extra->laravel->dont-discover: ["laravel/telescope"].
        // php artisan telescope:install. Run migrate command.
        // Access the dashboard: /telescope.
        // config/telescope.php.
        
        // Data pruning: 
        // Schedule::command('telescope:prune')->daily();
        // Schedule::command('telescope:prune --hours=48')->daily();

        // Dashboard Authorization:
        // By default, we can access /telescope only in local.
        // To make author as non- local, customize the gate() method in TelescopeServiceProvider.
        Gate::define('viewTelescope', function (User $user) {});
        // Ensure APP_ENV as production, otherwise it will be publicly available.

        // We can filter which data we need in provider's register() using Telescope::filter() method. For example, we can filter out all cache hits and only log cache misses.
        // While the filter closure filters data for individual entries, you may use the filterBatch method to register a closure that filters all data for a given request or console command.

        // Adding custom tag:
        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'cache') {
                return ['cache'];
            }
        });

        // Available Watchers:
        // Telescope "watchers" gather application data when a request or console command is executed. 
        // We can customize or enable in config/telescope.php. 
        // 'watchers' => [  Watchers\CacheWatcher::class => true, ]
        // Batch watcher, Cache watcher, command watcher, dump watcher, event watcher, exception watcher, gate watcher, http client watcher, job watcher, log watcher, mail watcher, model watcher, notification, query, redis, request, schedule, view watcher.

        // Customize Displaying user avatar in Telescope Dashboard:
        // In register():
        Telescope::avatar(function (?string $id, ?string $email) {
            return ! is_null($id)
                ? '/avatars/'.User::find($id)->avatar_path
                : '/generic-avatar.jpg';
        });
}
