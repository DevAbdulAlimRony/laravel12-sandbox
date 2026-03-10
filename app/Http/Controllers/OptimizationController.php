<?php

namespace App\Http\Controllers;

class OptimizationController {
    //* Time Complexity:
    $users = User::all();
    // O(n) because Laravel will need to iterate over all n rows to retrieve them.
    User::find($id);
    // O(1) (constant time) because the find method is indexed by the primary key, so the database can locate the record directly without iterating over all rows.

    //* Space Complexity:
    // Space complexity refers to the amount of memory an algorithm uses relative to the input size.
    $count = User::count();
    // O(1) because you’re only storing a single integer (the count of users) in memory, regardless of how many users are in the table.

    //* Collection memory optimization:
    $users = User::all();
    $activeUsers = $users->filter(function ($user) {
        return $user->active;
    }); // Instead of doing this:
    $activeUsers = User::cursor()->filter(function ($user) {
        return $user->active;
    })->all();

    // Use lazy() for lazy collections (better for large exports).

    //* Process in Chunks:
    User::chunk(500, function ($users) {
         foreach ($users as $user) {}
         // Clear memory between chunks
         unset($users);
         gc_collect_cycles();
     });

     //* Advance Collection Optimization:
     // Use generators for memory-intensive operations: cursor() and yeild.

     //* Eloquent Memory Optimization:
     // Optimize eloquent queries and model hydration for minimal memory footprint.
     User::select('id', 'name', 'email', 'created_at')->get();  // Select only necessary fields
     User::findMany($userIds)->keyBy('id'); // Use findMany with specific IDs.
     // Avoid unnecessary relationships
     User::where('active', true)->pluck('name', 'id')->all(); // Use value retrieval instead of full models.  
     // Use chunkById for memory-efficient processing
     foreach (User::where('active', true)->cursor() as $user) {} // Use cursor for maximum memory efficiency.

     //* View Memory Optimization:
     return view('dashboard', [ 'comments' => Comment::where('user_id', $user->id)->cursor(),]);
     // Use view caching for expensive templates.

     // Implement lazy service provider registration

     //* Dependency Injection Optimization:
     // Optimize service container usage for minimal memory impact.
     $this->app->when(UserController::class)->needs(UserRepositoryInterface::class)>give(DatabaseUserRepository::class);
     // Use singleton only when necessary
     $this->app->instance(SharedService::class, new SharedService()); // Use instance binding for pre-instantiated objects.
     // Lazy service resolution
     // Conditional binding resolution
     // Contextual binding optimization

     //* Queue Worker Memory Management:
     // In app/Console/Commands/OptimizedQueueWorker.php:
     protected function runJob($job, $connection, $queue)
     {
        try {
            // Reset problematic singletons before each job
            $this->resetSingletons();

            // Process job
            parent::runJob($job, $connection, $queue);

            // Clear memory after job
            $this->clearMemory();
        } catch (\Exception $e) {
            $this->handleException($job, $e);
        }
    }
    protected function clearMemory()
    {
        gc_collect_cycles();

        if (memory_get_usage(true) > 128 * 1024 * 1024) { // 128MB
            // Signal worker to restart after current job
            posix_kill(getmypid(), SIGTERM);
        }
    }
    // Implement periodic worker restarts (--max-jobs, --max-time)
    // Use strategic garbage collection
    // Monitor memory usage with custom metrics

    //* Garbage Collection Tuning:
    // Optimize PHP's garbage collector for Laravel's specific patterns.
    // In config/bootstrap.php:
    gc_enable();  // Enable garbage collection
    gc_mem_caches(10); // Increase from default 2 to 10
    register_shutdown_function(function () {
        gc_collect_cycles(); // Register shutdown function for final cleanup
    });

    // In boot() of AppServiceProvider:
    public function boot()
    {
        if (app()->runningInConsole() && $this->isQueueWorker()) {
            $this->configureQueueWorkerGC();
        }
    }
    protected function isQueueWorker(): bool
    {
        return Str::contains(request()->server->get('argv')[1] ?? '', 'queue:work');
    }
    protected function configureQueueWorkerGC()
    {
        // Force GC collection every 100 jobs
        Octane::tick('gc-collect', function () {
            gc_collect_cycles();
        })->every(100);

        // Monitor memory before/after GC
        Octane::tick('gc-monitor', function () {
            $before = memory_get_usage();
            gc_collect_cycles();
            $after = memory_get_usage();

            Log::debug("GC: Before {$before}, After {$after}, Freed " . ($before - $after));
        })->every(500);
    }

    //* Memory Efficient API Responses:
    // Optimize API resource transformation for minimal memory usage.
    User::withCount(['posts', 'comments'])
         ->select('id', 'name', 'created_at')
         ->cursor()
         ->mapInto(UserResource::class)
         ->chunk(100)
         ->each(function ($chunk) {
            // Process chunk
        });

    
    $fields = $request->input('fields', []);
    $users = User::select($this->getSelectFields($fields))->with($this->getWithRelations($fields))->cursor();
    // return UserResource::collection($users);
    protected function getSelectFields(array $fields): array
    {
        $baseFields = ['id', 'name', 'created_at'];
        if (in_array('email', $fields)) {
            $baseFields[] = 'email';
        }
        return $baseFields;
    }

    //* Memory Optimization for Scheduled Tasks:
    // See here: https://dev.to/arasosman/laravel-memory-optimization-12-advanced-techniques-for-resource-efficiency-a7a.

    //* Memory Optimization for Testing: Same Link.

    //* Healthy memory usage for laravel requests:
    // Simple API requests: 8-16MB.
    // Standard web requests: 16-32MB.
    // Complex operations: 32-64MB.
    // Queue workers: 64-128MB (before restart).

    //* Eager Load, Use Select
    // Use Caching for Frequently Accessed Data.
    User::all()->take(10)->remember(now()->addHours(1))->get();
    // Use Redis for caching (it’s blazing fast).
    // Try model caching with packages like laravel-model-caching.

    //* Use database index for columns which need where clauses.
    // Use composite indexes for multi-column searches.
    // Indexes drastically improve performance for count(), sum(), avg(), groupBy().

    //* Avoid the get() Before Filtering:
    User::get()->filter(...); // Bad! Filters in PHP, not SQL  
    User::where('active', true)->get(); // Good!
    
    //* Use when() for conditional queries, Let the database do the heavy lifting.

    //* Use exists() Instead of count() When Checking Records:
    if (User::where('email', $email)->count() > 0){} // slow for large tables.
    if (User::where('email', $email)->exists()) { } // Faster, stops after first match.
    
    //* Avoid LIKE On Unindexed Columns, use full text search if possible.
    // Use Scout with Algolia/Meilisearch for blazing-fast searches.

    //* Avoid Leading Wildcards in LIKE Searches:
    // 	WHERE name LIKE '%john', prefer: WHERE name LIKE 'john%'.

    // Use whereIn() or bulk data retrieval instead of looping multiple queries.
    // Skip belongsTo Relationship Load If Only ID Needed, instead of $post->author->id, use $post->author_id.
    // Use Raw Queries for Complex Operations.
    // Optimize Relationships with has(): User::has('posts')->get();
    // Use Database Transactions for Batch Operations.
    // Avoid looping inserts, Use Bulk Inserts for Multiple Records using DB::table()->insert().
    // Invalidate Cache When Data Changes: Cache::tags(['users'])->flush().
    // Avoid Too Many Columns in a Single Table.
    // Normalization supports: faster reads, smaller row size → more rows per page.
    // Move Large Text / JSON Fields Into Separate Tables: posts(id, title), post_contents(post_id, content Text)
    // Count Rows Using Queries Instead of Collections: Instead of User::all()->count, better: User::count()

    //* Merge Similar Queries:
    $recentPosts = Post::where(‘status’, ‘published’)->take(10)->get();
    $draftPosts = Post::where(‘status’, ‘draft’)->take(10)->get();
    
    Post::whereIn(‘status’, [‘published’, ‘draft’])->take(10)->get(); // Merged.
    // Now apply condition on got data.

    // Use simplePaginate Instead of paginate if possible.
    // Avoid Using SQL Functions in the WHERE Clause: WHERE YEAR(created_at) = 2023, better: WHERE created_at BETWEEN ‘2023–01–01’ AND ‘2023–12–31’.

    // Logging, benchmarking, database profiling.
    // Using package like laravel telescope to monitor.

    // Caching route, config, view, cache etc in production.
    // avoid attaching session or authentication middleware to public-facing APIs that don’t require them.

    //* Use OpCache: 
    // OpCache is a PHP extension that caches compiled PHP code in memory, reducing the time it takes to load PHP files. 
    // add the following lines to your PHP configuration file:
    opcache.enable=1
    opcache.enable_cli=1

    //* Optimizing autoloading: 
    // composer dump-autoload --optimize --classmap-authoritative

    //* Offload Heavy Tasks with Queues.

    //* Optimize Composer: 
    // composer install --prefer-dist --no-dev -o.

    //* Reduce Package Usage.

    //* Always upgrade to latest version.

    //* Use the Deployment Tool to Appeal to All Commands.
    // Like Deployer.

    //* Use Lumen for Small Projects.

    //* Leverage JIT Compiler.
    // PHP is a server-side language that requires interpreters to translate the code into a bytecode, which the computer can understand.
    // This process takes a lot of time and consumes a lot of resources.
    // For the sake of efficiency, devs use the just-in-time (JIT) compiler to repeat that procedure just once.

    //* Choosing Right Hosting:
    // Ecommerce: Dedicated servers with Redis and CDN integration for traffic spikes and secure transactions.
    // SaaS Application: VPS hosting with scalable resources and support for queue workers.
    // CMS (Content Management System): Shared hosting with CDN for media delivery; upgrade as content and traffic grow.
    // Business Applications: VPS or dedicated hosting based on user count and data complexity.

    // eCommerce apps must load quickly (typically under 200ms) to reduce cart abandonment.
    // Performance-sensitive elements include product pages, checkout flows, and third-party payment gateways.
    // SaaS platforms benefit from efficient API rate limiting, scalable background jobs, and fast real-time interactions (e.g., chat, notifications).
    // CMS-based websites require optimized images, robust content caching, and low-latency search features to maintain SEO rankings and user experience.
    // Enterprise-grade tools like dashboards or analytics engines often perform database-intensive operations and require fast, concurrent access to large datasets.

    // Shared hosting is ideal for lightweight Laravel apps or early-stage projects.
    // If traffic increases or workloads become heavier, response times may degrade.
    // VPS hosting offers root access, which allows for custom PHP configurations, Redis integration, and performance tuning that is not possible on shared plans.
    // Dedicated servers are best for mission-critical Laravel applications.

    //* Laravel actually slow than other frameworks.
    // Out of the box, Laravel prioritizes ease of development over production performance.
    // For instance, default settings load unnecessary services and include verbose logging that’s helpful in development but costly in production.
    // .NET is the fastest, then noe.js, then Django, then maybe laravel.
    // But the gap can be closed using PHP 8.x with JIT compilation and tool like Laravel Octane.
    // A realistic baseline for many business applications is a sub-200ms response time.
    // Anything above that risks impacting conversions, user experience, and SEO.

    //* Server Level Optimization
}