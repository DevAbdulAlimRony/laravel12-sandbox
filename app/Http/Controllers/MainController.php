<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MainController {
    public function index(){
        Flight::all(); // all models.
        $flight = Flight::where('status', 1)->get(); // We can chain any method of query builder.

        //* Each Eloquent model serves as a query builder.
        // So we can add or chain additional constraints to queries and invoke the get method.

        //* Getting only authenticated user's data:
        // $request->user()->categories()->paginate()

        //* Single Model:
        Flight::find(1); // Retrieve by its primary key.
        Flight::where('active', 1)->first(); // Return first model matching the query constraints
        Flight::firstWhere('active', 1);
        Flight::findOr(1, function () {}); // Run the callback if no results found.
        Flight::where('legs', '>', 3)firstOr(function () {});
        Flight::findOrFail(1); // If not found then ModelNotFoundException.
        Flight::where('legs', '>', 3)->firstOrFail();
        // If the ModelNotFoundException is not caught, a 404 HTTP response is automatically sent back to the client.
        Flight::firstOrCreate(['name' => 'London to Paris']); // Retrieve flight by name or create it if it doesn't exist.
        Flight::firstOrNew(['name' => 'London to Paris']); // Retrieve flight by name or make a new model instance, it if it doesn't exist.
        // for new, to save database, we have to use save(). create automatically save into database.

        //* Retrieving Aggregates:
        Flight::where('active', 1)->count();
        Flight::where('active', 1)->max('price');
        // min('price'), avg('price'), sum('price').

        //* Re-retrieve the model from the database, $flight will not be affected.
        $newFlight = $flight->fresh(); 
        
        $flight->number = 'FR 456';
        $flight->refresh(); // Re-hydrate the existing model with fresh data from database.

        //* As get(), all(), first() etc. those mthods return collection instance, so we can apply any collection methods on it.
        // So, Flight::where()->map(): Its not valid, But- Flight::where()->get()->map() - this is valid. Same goes for all collection methods.

        //* Since all of Laravel's collections implement PHP's iterable interfaces, we may loop over collections as if they were an array.
        foreach(Flight::all() as $flight){
            echo $flight;
        }

        //* Perform operation without updating updated_at:
        Flight::withoutTimestamps(fn () => $flight->status = 0);

        //* Chunking:
        // To load large datasets like tens of thousands of records, raher than get or all, we should use chunk to prevent memory leak.
        Flight::chunk(200, function (Collection $flights){
            foreach($flights as $flight){
                echo $flight->name;
            }
        });
        // If filtering based on column that will also be updating, use: chunkById()
        Flight::where('departed', true)->chunkById(200, function (Collection $flights){
            $flights->each->update(['departed' => false]);
        }, column: 'id');

        //* Chunk with Lazy Collection:
        // It was introduced to give you the memory efficiency of chunking but with the clean, readable syntax of a single loop.
        foreach(Flight::lazy() as $flights){} // chunk size is 1000 by default, we can customize: lazy(200)
        Flight::where('departed', true)->lazyById(200, column: 'id')->each->update(['departed' => false]);
        // lazyByIdDesc(): filter the results based on the descending order of the id.

        //* Cursor: It uses PHP generator behind the scene.
        // Use chunk() if you are updating the data you are iterating over.
        // Use cursor() when you are performing "read-only" tasks (like exporting a massive CSV) or when memory is extremely limited, as it is faster and more memory-efficient than chunking.
        // The cursor method will only execute a single database query.
        // cursor cant eager load relationship, if want to eager load use lazy().
        foreach(Flight::where('destination', 'baghail')->cursor() as $flight){} // returns a LazyCollection instance.
        // LazyCollection can take methods of normal Collection instance  while only loading a single model into memory at a time.
        User::cursor()->filter(function (User $user){ return $user->id > 500;});
        // But cursor can run out of memory also, if large number of records, using lazy() is safe.

        //* Advance Subquery for Realted Model:
        // We need user model, but we have to run query on user's flight model also:
        $users = User::addSelect(['last_flight_number' => Flight::select('flight_number')
                                                                ->whereColumn('user_id', 'users.id') // Link the flight to the user
                                                                ->orderByDesc('created_at') // Get the latest one
                                                                ->limit(1) // Ensure only one value is returned
                        ])->get();
        // If need sorting on User also, raher than addSelect(), use: User::orderByDesc(['last_flight_number'...)

        // Now we can access it as a regular attribute:
        foreach ($users as $user) {
            echo $user->name . " last flew on: " . $user->last_flight_number;
        }

        //* Compare Models:
        // Check if two models have same primary key, table and database connection:
        if ($model1->is($model2)) {}
        if ($model1->isNot($model2)) {}
        // Real use cases:
        if ($post->author->id === $user->id) {
            // This loads the entire 'user' object from the database just to check an ID 
        }
        if ($post->author()->is($user)) {
            // Laravel just looks at the 'author_id' foreign key stored on the $post object
        }
        // Check in category tree, sub category is not same as its parent category.

        //* Pagination:
        User::where()->paginate(15); // Can pass default value.
        User::where()->simplePaginate(15); // Just previous and next
        User::where('votes', '>', 100)->cursorPaginate(15); // Useful for performance gaining, infinite scrolling.
        User::where('votes', '>', 100)->paginate($perPage = 15, $columns = ['*'], $pageName = 'users') // If multiple paginate in a page, to avoid conflict.
        // paginate and simplePaginate use offset, but cursorPaginate use where clause so it is the best performant.
        // Cursor-based pagination places a "cursor" string in the query string: http://localhost/users?cursor=eyJpZCI6MTUsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0
        // paginate(): select * from users order by id asc limit 15 offset 15
        // cursorPaginate(): select * from users where id > 15 order by id asc limit 15
        // Cursor pagination can only be used to display "Next" and "Previous" links and does not support generating links with page numbers.
        // Cursor equires that the ordering is based on at least one unique column or a combination of columns that are unique. Columns with null values are not supported.
        // Query expressions with parameters are not supported in cursor paginate.
        User::paginate(15)->withPath('/admin/users'); // Custom link to show rather than default route
        User::paginate(15)->appends(['sort' => 'votes']); // Appending query string values.
        User::paginate(15)->withQueryString(); // Append all current requests query string
        User::paginate(15)->fragment('users'); // Appended #users after the route link.
        // {{ $users->onEachSide(5)->links() }} : How many additional page will be displayed in view.
        // {{ $paginator->links('view.name') }}
        // To customize pagination view easily: php artisan vendor:publish --tag=laravel-pagination
        // resources/views/vendor/pagination directory. The tailwind.blade.php file : edit to get your design.
        // If we want another view for pagination, define it in Service provider.
        // We can create pagination manually with LengthAwarePaginator.
        
        // Paginator Instance: $paginator->count(), currentPage(), firstItem(), lastPageUrl() etc.
        // Cursor Paginator Instance: $paginator->count() etc.

        //* Serialization:
        // Convert models and relationships to arrays or JSON.
        User::with('roles')->first()->toArray(); // All relationships and attributes will be an array
        User::first()->attributesToArray(); // Only attributes, not relationship
        User::find(1)->toJson(); // toJson(JSON_PRETTY_PRINT)
        // If we cast a collection to string, it will be converted to json automatically.
        (string) User::find(1);
        // For api, we can do serialization using API Resources

        //* API Resources:
        // A resource class represents a single model that needs to be transformed into a JSON structure.
        // Its a transformation layer that sits between Eloquent models and the JSON responses.
        // Transform model collections into JSOM expressively. Ex: subset of users and not others, certain attributes or relationships.
        // php artisan make:resource UserResource. Check app\Http\Resources.. class extends JsonResource.
        // Resource Collection: php artisan make:resource User --collection, php artisan make:resource UserCollection
        // Resource collection includes multiple model rather than single model.
        //The resource accepts the underlying model instance via its constructor:
        return new UserResource(User::findOrfail($id));
        return User::findOrFail($id)->toResource(); // Automatically discover model's resource based on name.
        // We can define default resouce above model class if inconvenient name when using toResource: #[UseResource(CustomUserResource::class)]
        // or, call like that: ->toResource(CustomUserResource::class)
        return UserResource::collection(User::all()); // For collection resource. or,
        return User::all()->toResourceCollection(); // or can do exactly as we do for toResource.
        return new UserCollection(User::paginate()); // paginated, we will get data, links and meta wrapper.
        return User::paginate()->toResourceCollection();
        new UserResource($user->loadCount('posts')); // Count conditional relationship
        User::find(1) ->toResource()->response()->header('X-Value', 'True'); // Customize resource or do it in resource class.

        //* Full Text Search:
        // A LIKE search for "running" won't find a record containing "run", and results aren't ranked by relevance.
        // Full-Text Search (FTS) is a sophisticated way of searching through large amounts of text data. 
        // FTS looks for keywords, phrases, and even similar-sounding words across one or multiple columns.
        // Traditional way WHERE content LIKE '%apple%' is slow on large datasets, fts knows exactly which rows contains it.
        // Support on MariaDB, MySQL, PostgreSQL.
        // Add a full-text index to the columns want to search, and then use the whereFullText query builder method to search against them.
        // Add Index in Scema of migration file: $table->fullText(['title', 'body']);
        // Can specify language on Postgres: $table->fullText('body')->language('english');
        Article::whereFullText('body', 'web developer')->get(); // Running the query
        // Mysql and mariaDB will do auto ordering, if want in postgres also consider using scoutsearch.
        Article::whereFullText(['title', 'body'], 'web developer')->get(); // orWhereFullText()

        //* Semantic or Vector Search:
        // For AI-powered semantic search that matches results by meaning rather than exact keywords.
        // Vector search requires PostgreSQL with the pgvector extension and the Laravel AI SDK.
        // An embedding is a high-dimensional numeric array (typically hundreds or thousands of numbers) that represents the semantic meaning of a piece of text. 
        Str::of('Napa Valley has great wine.')->toEmbeddings(); // generating an embedding.
        Embeddings::for([ 'Napa Valley has great wine.', 'Laravel is a PHP framework.',])->generate(); // generating embading for multiple.
        // Ensure the extension exists in migration: Schema::ensureVectorExtensionExists()
        // Indexing vectors in migration schema: $table->vector('embedding', dimensions: 1536)->index();
        //  1536 for OpenAI's text-embedding-3-small model), how many matches.
        // In model, cast the vector column, embedding, as array.
        Document::query()->whereVectorSimilarTo('embedding', $queryEmbedding, minSimilarity: 0.4)->->limit(10)->get();
        // ->whereVectorSimilarTo('embedding', 'best wineries in Napa Valley')
        // Lower level control: whereVectorDistanceLessThan(), selectvectorDistance(), orderByvectorDistance()

        //* Reranking Results:
        // Reranking is a technique where an AI model reorders a set of results by how semantically relevant each result is to a given query. 
        // Full-text search to quickly narrow thousands of records down to the top 50 candidates, and then use reranking to put the most relevant results at the top.
        Reranking::of('Django is a Python web framework.', 'Laravel is a PHP ')->rerank('PHP frameworks');
        Article::all()->rerank('body', 'Laravel tutorials');

        //* Laravel scout Search:
        // composer require laravel/scout
        // php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
        // Searchable trait that automatically keeps search indexes in sync with Eloquent models.
        // Laravel Scout offers both a built-in database engine and drivers for third-party services like Algolia, Meilisearch, and Typesense.
        // Scout's built-in database engine performs full-text and LIKE searches against your existing database — no external service or extra infrastructure required.
        // Go to the Flight model to see. After doing in model:
        Article::search('Laravel')->get();
        // Its using now database engine, but we can use Algolia, Meilisearch, Typesense engine to get advantages what they provide.
        // If we use other engine rather than database or collection, we should make 'queue' => true in scout config file.

        //* Search by Combining Techniques:
        // Full-Text Retrieval + Reranking: speed of full text with accuracy of AI powered relavance scoring:
        Article::query()->whereFullText('body', $request->input('query'))->limit(50)->get()->rerank('body', $request->input('query'), limit: 10);
        // Vector Search + Traditional Filters: Meaning-based search but need to restrict results by ownership, category, or any other attribute:
        Document::query()->where('team_id', $user->team_id)->whereVectorSimilarTo('embedding', $request->input('query'))->limit(10)->get();
    }

    public function accessRelationships(Flight $flight){
         $flight->counties()->where('active', 1)->get();
         User::find(1)->phone;
         User::find(1)->comments;
         foreach ($comments as $comment) {}
         User::with('comments')->get(); // Eager Loaded

         //* Chaperone: if defined in relationship, then dont need here.
         foreach ($post->comments as $comment) {
            // WITHOUT chaperone(): This line triggers a DB query for EVERY comment.
            // WITH chaperone(): This line uses the Post already in memory.
            echo $comment->post->author_name; 
        }
         $posts = User::with([
            'comments' => fn ($comments) => $comments->chaperone(),
        ])->get();

        Post::whereBelongsTo($user)->get(); // Equivalent to: Post::where('user_id', $user->id)->get()
        // Will determine relatioship based on var $user, but can specify:
        Post::whereBelongsTo($user, 'author')->get();   
        Post::whereBelongsTo(User::where('vip', true)->get())->get(); // Passing collection.

        // Many to Many Relationship Pivot Table
        // foreach ($user->roles as $role) { echo $role->pivot->created_at; }
        // Access morph relation: $post->image, finding parent: $image->imageable
        // foreach ($post->tags as $tag), $tag->posts as $post, $tag->videos as $video

        //* Dynamic Relationship: Run relationship at runtime
        // Not recommended, but can be useful in package developemnt:
        Order::resolveRelationUsing('customer', function (Order $orderModel) {
            return $orderModel->belongsTo(Customer::class, 'customer_id');
        });

        //* Querying Relations:
        // All types of Eloquent relationships also serve as query builders, allowing you to continue to chain constraints onto the relationship query.
        // We can use any query builder on relationship like where, orWhere, logical groups
        Post::has('comments')->get(); // Retrieve all posts that has at least one comment
        Post::has('comments', '>=', 3)->get();
        Post::has('comments.images')->get(); // at leat one comment with images.
        User::whereHas('posts', function ($query) { $query->where('title', 'like', '%Laravel%')})->get(); // Retrive user which have at least one post with that condition
        User::whereAttachedTo($role)->get(); // Many to Many existence.
        Post::whereRelation('comments', 'created_at', '>=', now()->minus(hours: 1))->get();
        // orWhereRelation, whereMorphRelation, orWhereMorphRelation
        Post::doesntHave('comments')->get(); // All blog posts that don't have any comments.
        Post::whereDoesntHave('comments', function (Builder $query) {})->get();
        Comment::whereHasMorph('commentable',[Post::class, Video::class],function (Builder $query) {})->get(); // Get comments of post and video that meet the condition.
        // whereDoesntHaveMorph(). In callback, can pass $type after Builder $query. Instead of passing Post,Video- can pass * wildcard to match al morph class.
        Comment::whereMorphedTo('commentable', $post)->orWhereMorphedTo('commentable', $video)->get();

        //* Aggregating Relations:
        $posts = Post::withCount('comments')->get();
        foreach ($posts as $post) {  echo $post->comments_count; }
        Post::withCount(['votes', 'comments' => function (Builder $query) {}]);
        // Can alias when same type count: withCount(['comments', 'comments as pending_comments_count'...
        Book::first()->loadCount('genres'); // Deferred Count: Count after retrieving parent.
        // loadCount(['reviews' => function (Builder $query) { condition...})
        // If we have select, call withCount after the select.
        //* withMin, withMax, withAvg, withSum - same functionality.
        // withExists (has at least one comment or not)
        // Morph Count: morphWithCount(), loadMorphCount()

        //* Eager Loading:
        // When accessing Eloquent relationships as properties, the related models are "lazy loaded".
        // This means the relationship data is not actually loaded until you first access the property. 
        // Eager loading alleviates the "N + 1" query problem.
        foreach (Book::all() as $book) {
            echo $book->author->name;
            // This loop will execute one query to retrieve all of the books
            // Another query for each book in order to retrieve the book's author.
            // If we have 25 books, the code above would run 26 queries: one for the original book, and 25 additional queries to retrieve the author of each
        }
        // Solve:
        Book::with('author')->get();
        // Only two queries will be executed - one query to retrieve all of the books and one query to retrieve all of the authors for all of the books
        foreach ($books as $book) {
            echo $book->author->name;
        }
        // We can eager load multiple relationship, specific columns, also nested with . notation or nested array.
        // with(['user', 'author:id,name,book_id', 'user.posts', 'user => ['posts' => ['comments']]'])
        // Should always include the id column and any relevant foreign key columns in the list of columns
        
        // Morph Eager Load
        ActivityFeed::query()->with(['parentable' => function(MorphTo $morphTo){
            $morphTo->morphWith([Photo::class => ['tags'],]);
        }])->get();
        
        // Constraining eager load:
        User::with(['posts' => function ($query) {$query->where('is_published', true);}])->get();
        // withWhereHas()
        // for morph: ...$morphTo->constrain()...
        // if(Book::all){Book::all()->load('author')} - Lazy Eager Load.
        // load(['books' => function ($query) {}, book->loadMissing('author'): Load if not already loaded.
        ActivityFeed::with('parentable')->get()->->loadMorph('parentable', [Event::class => ['calendar']]);

        // Auto Load Relation:
        User::where()->withRelationshipAutoloading();
        // Still in Beta Version.

        //* Insert Update:
        $post->comments()->save($comment);
        $post->comments()->saveMany([
            new Comment(['message' => 'A new comment.']),
            new Comment(['message' => 'Another new comment.']),
        ]); 
        // refresh(), push(), pushQuietely(), create(), createMany(), createQuietly(), createManyQuietly()
        // findOrNew(), firstOrNew(), firstOrCreate(), updateOrCreate()
        $user->account()->associate($account); // account has a user_id, associate will provide that data to save
        $user->account()->dissociate();
        $user->roles()->attach($roleId);
        $user->roles()->attach($roleId, ['expires' => $expires]);
        $user->roles()->detach($roleId); // Detach a single role from the user
        $user->roles()->detach(); // Detach all roles from the user...
        // Attach and detach can take id also.
        $user->roles()->sync([1, 2, 3]); // Many to many association.
        $user->roles()->sync([1 => ['expires' => true], 2, 3]);
        $user->roles()->syncWithPivotValues([1, 2, 3], ['active' => true]);
        $user->roles()->syncWithoutDetaching([1, 2, 3]);
        $user->roles()->toggle([1, 2, 3]);
        $user->roles()->toggle([
            1 => ['expires' => true],
            2 => ['expires' => true],
        ]);
        $user->roles()->updateExistingPivot($roleId, ['active' => false]);
    }

    public function store(Request $request){
        //* Insert Data using Model instance:
        $flight = new Flight; // Create a new instance
        // Now, Assign the name field from the incoming HTTP request to the name attribute of the App\Models\Flight model instance.
        // created_at and updated_at timestamps will automatically be set when the save method is called
        $flight->name = $request->name;
        $flight->save();

        //* Insert using mass assignale:
        // In Laravel, Mass Assignment is the process of sending an array of data directly into a model to create or update a record in one go, rather than setting each property one by one.
        // While it is very convenient, it is also a potential security vulnerability, which is why Laravel forces you to be explicit about which fields can be filled this way.
        // At first, specify either a fillable or guarded property on model class.
        // Mass assignable is standard way for inserting data in laravel.
        $flight = Flight::create(['name' => 'London to Paris']); // or just call $request->all().
        // If already have model instance, can use fill:
        $flight->fill(['name' => 'London to Paris']);
        // For json columns,  each column's mass assignable key must be specified in fillable.
        // If attribute is not included in $fillable, it will be silently discarded which is good for production.
        // If we wanna show error in local- invoke exception in AppServiceProvider's boot method.

        // Using relationship: $request->user()->flight()->create($flightData)
        // return to_route('flight.index')->with('success', 'succesfully created');
        
        //* Using Insert:
        // Unlike create(), it bypasses the Eloquent Model layer and talks directly to the database.
        // This makes it incredibly fast, but it means you lose "magic" features like automatic timestamps and observers.
        DB::table('flights')->insert([
            'flight_number' => 'KLM123',
            'destination' => 'Amsterdam',
            'created_at' => now(), // Manually required
            'updated_at' => now(), // Manually required
        ]); // If duplicate entry, will throw error. If dont want error, use: insertOrIgnore()
        //* Mass Bulk Insert: Can pass multiple rows and it will perform using single query, thats the real use case. Example: Importing from excel.
        DB::table('users')->insert([
            ['email' => 'picard@example.com', 'votes' => 0],
            ['email' => 'janeway@example.com', 'votes' => 0],
        ]);
        // Insert and immediately get the primary Key:
        $id = DB::table('orders')->insertGetId([
            'total' => 99.99,
            'user_id' => 1
        ]);
        // If 10,000 rows, can get error- use chunk/lazy and insert combined.
        // insertUsing(): Insert while using a subquery as condition.

        //* Replicating models:
        // Particularly useful when you have model instances that share many of the same attributes.
        // Example: Same model instances for old and new values, poultry and cattle farmer which have same attributes.
        $shipping = Address::create(['type' => 'shipping', 'city' => 'Victorville',]);
        $billing  = $shipping->replicate()->fill(['type' => 'billing']); // same city, just type changed.
        $flight->replicate(['last_flown','last_pilot_id']); // Just replicate those two attributes.
    }

    public function update(){
        //* Update using model instance:
        $flight = Flight::find(1);
        $flight->name = 'Paris to London';
        $flight->save();
        // light::updateOrCreate(): If model found then update, insert if not found.
        if ($flight->wasRecentlyCreated){} // Check if model created.

        //* Mass Updates:
        Flight::where('active', 1)
                ->update(['delayed' => 1]); // Return number of affected rows.
        // saving, saved, updating, and updated model events will not be fired when mass update, because models never actually retrieved.

        //* Using Upsert:
        // Should use upsert() when you need to perform high-volume "update or create" operations in a single database query.
        Flight::upsert([
            ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99],
            ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150]
            ], uniqueBy: ['departure', 'destination'], update: ['price']);
        // MariaDB and MySQL database drivers ignore the second argument of the upsert method and always use the "primary" and "unique" indexes of the table to detect existing records.

        // updateOrInsert(): will attempt to locate a matching database record using the first argument's column and value pairs.
        // If the record exists, it will be updated with the values in the second argument. 
        // ->updateOrInsert( ['email' => 'john@example.com', 'name' => 'John'],  ['votes' => '2']);
        DB::table('users')->updateOrInsert(
            ['user_id' => $user_id],
                fn ($exists) => $exists ? [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ] : [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'marketable' => true,
            ],
        );

        // Update an array key casted from json column:
        $user->update(['options->key' => 'value']);

        // Increment Decrement:
        DB::table('users')->increment('votes');
        DB::table('users')->increment('votes', 5);
        DB::table('users')->decrement('votes', 5);
        DB::table('users')->increment('votes', 1, ['name' => 'John']); // Increment vote and update name
        DB::table('users')->incrementEach(['votes' => 5, 'balance' => 100,]); // multiple columns. decrementEach()


        //* Examining Attribute Changes:
        $flight->isDirty(); // If any of the model's attributes have been changed since the model was retrieved. 
        $flight->isDirty('title');
        $flight->isClean(); // If an attribute has remained unchanged since the model was retrieved. 
        $flight->isClean(['title', 'another']);
        $flight->wasChanged(); // If any attributes were changed when the model was last saved within the current request cycle. 
        $flight->wasChanged(['title', 'slug']);
        $flight->getOriginal(); // Array of original attributes...can give attribute name.
        $flight->getPrevious(); // An array containing the original attribute values before the model was last saved
        $flight->getChanges(); // Array of changed attributes.
        // Though Eloquent relationship methods are defined using "camel case" method names, a relationship's JSON attribute will be "snake case".
    }

    public function delete(){
        //* Usinf model retrieving:
        $flight = Flight::find(1);
        $flight->delete();

        //* Using primary key
        // Rather than retrieving first, can delete by primary key directly:
        Flight::destroy(1);
        Flight::destroy(1, 2, 3);
        Flight::destroy(collect([1, 2, 3]));

        //* Using Queries:
        Flight::where('active', 0)->delete();
        light::query()->delete(); // Delete all models in a query.

        //* Soft Delete:
        // If soft delete enabled, and want permanent delete:
        $flight->forceDelete();
        $flight->history()->forceDelete();
        Flight::forceDestroy(1);
        //* If a given model instance has been soft deleted
        $flight->trashed()

        //* Restoring soft deletd:
        $flight->restore();
        $flight->history()->restore(); // with relationship table.

        //* Querying Soft Delete:
        Flight::withTrashed()->where('airline_id', 1)->restore();
        $flight->history()->withTrashed()->get();
        Flight::onlyTrashed()->get();
    }

    //* Clearing confusion about when to use builder and when to collection methods:
    //# Rule 1: Filter in the Builder, Format in the Collection.
    // Bad: User::all()->where('active', 1); (You downloaded every user, even inactive ones, just to throw them away in PHP).
    // Good: User::where('active', 1)->get(); (The database only sent you the active ones).
    //# Rule 2: Use Collections for "Heavy Lifting" Logic:
    // Some things are hard to do in SQL but easy in PHP. If you need to check a complex PHP permission or format a string using a Laravel helper, use a Collection.
    // User::where('role', 'admin')->get()->filter(return $user->hasSpecialPermission();)

    // User:: (You are in Builder mode- database level).
    // ->where()->limit() (Still in Builder mode).
    // ->get() (The "Bridge"—you just jumped from Builder to Collection- php level).
    // ->map()->groupBy() (You are now in Collection mode).

    // When methods act as builder, it Converts to SQL and runs on the DB.. When collection instance, Runs as PHP code on an array.
    // Query builder handles millions of rows easily, but collection is limited by the server RAM.

    // We can use these methods in only database level.
    // If you try to use these on a Collection after you've already fetched the data, your code will crash.
    public function builderMethods(){
       //* Migrations: See users table migration file.

       //* DB Facade:
       // To write raw sql query we can use DB facade or DatabaseManager injcetion , $db->make()
       // DB::select(), DB::scalar(),  DB::selectResultSets, DB::insert(), DB::update(), DB::delete(), DB::statement(), DB::unprepared()
       // Using multiple connection: DB::connection('sqlite')->select(/* ... */);
       // PDO Instance: DB::connection()->getPdo();
       // Listening for Query Events: DB::listen()

       //* Transaction:
       // We can use DB::transaction(function(){}) for multiple transactions, If one failed whole transaction will fail.
       // don't need to worry about manually rolling back or committing while using the transaction method
       DB::transaction(function () {
          DB::update('update users set votes = 1');
          DB::delete('delete from posts');
       });

       //* Handling Deadlocks:
       DB::transaction(function () {
          DB::update('update users set votes = 1');
          DB::delete('delete from posts');
       },  attempts: 5);
       // If any deadlock happen, it will retry again, Maximum 5 times attempt.

       // If we want complete control over transaction commit and rollback, we can use:
       DB::beginTransaction();
       DB::rollBack();
       DB::commit();
       // The DB facade's transaction methods control the transactions for both the query builder and Eloquent ORM.
              
        DB::table('users')->get(); // first(), firstOrFail(), find()
        // chunk(), chunkById(), lazy(), lazyById()
        // count(), max(), min(), avg(), sum()

        // Checking Existence:
        // One way is using count. 
        DB::table('orders')->where('finalized', 1)->exists();
        DB::table('orders')->where('finalized', 1)->doesntExist();

        // Specific Value and Column
        DB::table('users')->where('name', 'John')->value('email'); // Raher than entire row, only email.
        DB::table('users')->pluck('title'); // Retrive collection instance, Only values of a single column.
        DB::table('users')->pluck('title', 'name'); // name will be the key of the collection. foreach ($data as $name => $title)

        // Select:
        DB::table('users')->select('name', 'email as user_email')->get(); // If already have a query, then $query->addSelect('name')
        DB::table('users')->distinct()->get(); // Return distict results.

        // Raw Expressions:
        // Laravel cannot guarantee that any query using raw expressions is protected against SQL injection vulnerabilities.
        DB::table('users'->select(DB::raw('count(*) as user_count, status'))->get(););
        // selectRaw(), whereRaw(), orWhereRaw(), havingRaw(), orHavingRaw(), orderByRaw(), groupByRaw()

        // Join:
        DB::table('users')->join('contacts', 'users.id', '=', 'contacts.user_id')->get();
        // leftJoin(), rightJoin(), crossJoin()
        // Advance Join: ->join('contacts', function (JoinClause $join) {})
        // Subquery Join: joinSub()
        // Lateral Join: joinLateral(), leftJoinLateral()- supported by PostgreSQL, MySQL >= 8.0.14, and SQL Server

        // Union:
        DB::table('users')->where()->union(DB::table('users')->where())->get(); // uniqueAll(): Duplicates wont be deleted.

        // where clauses:
        // MySQL and MariaDB automatically typecast strings to integers in string-number comparisons.
        // Non-numeric strings are converted to 0, It can bring false result when you check condition.
        // To avoid this, ensure all values are typecast to their appropriate types before using them in queries.
        DB::table('users')->where('votes', '=', 100) ->where('age', '>', 35)->get();
        DB::table('users')->where('votes', 100)->get(); // If we directly provide value, automatically check equality.
        DB::table('users')->where(['votes' => 100, 'rating' => 5])->get(); // Associative array to multiple column quick check
        DB::table('users')->where([['status', '=', '1'],['subscribed', '<>', '1'],])->get(); // Array of conditioned columns
        // Operators: <=, >=, <> etc.
        DB::table('users')->whereColumn('first_name', 'last_name')->get(); // If two columns are qual.
        DB::table('users')->whereColumn('updated_at', '>', 'created_at')->get(); // Can pass array of columns using array also.
        DB::table('users')->where('name', 'like', 'T%')->get(); // Find every user whose name starts with the letter T, followed by anything (or nothing) else.
        DB::table('users')->where('votes', '>', 100)->orWhere('name', 'John')->get(); // Normally, where chains work as and operator, If we want or
        DB::table('users')->where('votes', '>', 100)->orWhere(function(Builder $query){
            $qyery->where()->where();
        })->get(); // select * from users where votes > 100 or (name = 'Abigail' and votes > 50)
        DB::table('users')->whereNot(function (Builder $query){})->get(); // orWhereNot.
        DB::table('users')->whereAny(['name', 'emal'], 'like', 'o%')->get(); // Condition for multiple columns.
        // ->whereAll(['name', 'emal'], ->whereAll(['none', 'none']
        // Querying json column: ->where('preferences->dining->meal', 'salad')
        // ->whereJsonContains('options->languages', 'en'),  ->whereJsonDoesntContain('options->languages', 'en')
        // whereJsonContainsKey(), whereJsonDoesntContainKey(), whereJsonLength()
        DB::table('users')->whereLike('name', '%John%')->get();
        DB::table('users')->whereLike('name', '%John%', caseSensitive: true)->get();
        // orWhereLike(), whereNotLike(), orWhereNotLike()
        DB::table('users')->whereIn('id', [1, 2, 3])->get(); // whereNotIn()
        DB::table('comments')->whereIn('user_id', DB::table('users')->select('id')->where('is_active', 1))->get();
        // Large array of Integer: whereIntegerInraw(), whereIntegerNotInRaw()
        DB::table('users')->whereBetween('votes', [1, 100])->get(); // orWhereBetween(), whereNotBetween()
        DB::table('patients')->whereBetweenColumns('weight', ['minimum_allowed_weight', 'maximum_allowed_weight'])->get(); // a column's value is between the two values of two columns in the same table row
        // whereNotBetweenColumns()
        DB::table('products')->whereValueBetween(100, ['min_price', 'max_price'])->get(); //  whereValueNotBetween()
        DB::table('users')->whereNull('updated_at')->get(); // whereNotNull(), orWhereNull(), orWhereNotNull()
        DB::table('users') ->whereDate('created_at', '2016-12-31')->get();
        // whereMonth('created_at', '12'), whereDay(), whereYear(), whereTime()
        // wherePast('due_at'), whereFuture(), whereNowOrPast(), whereNowOrFuture()
        // whereToday(), whereBeforeToday(), whereAfterToday(), whereTodayOrBefore (), whereTodayOrAfter()
        DB::table('users')->whereExists(function(Builder $query){}); // Write condition in the closure or pass a query rather than callback.
        User::where(function (Builder $query){$query->select('type')->from('membership')->whereColumn('membership.user_id', 'users.id')->first();}, 'pr')->get();
        User::where('income', '<=', function (Builder $query){})->get();
        DB::table('users')->whereFullText('bio', 'web developer')->get(); // supported in mariadb, mysql, postgres.

        // Ordering:
        DB::table('users')->orderBy('name', 'desc')->get();
        // Can sort by multiple columns using orderBy() again.
        // Sort direction is optional, ascending by default. Thats why there is orderByDesc(), but not orderByAsc.
        // Json Column: ->orderBy('location->state')
        DB::table('users')->inRandomOrder()->get(); // Randomly.
        DB::table('users')->orderBy('name')->reorder('name', 'desc')->get(); // Remove previos order.
        DB::table('users')->orderBy('name')->reorder('name', 'desc')->get(); // Remove previos order and add new.
        // reorderDesc()

        // Grouping:
        DB::table('users')->groupBy('account_id')->having('account_id', '>', 100)->get();
        // havingBetween('number_of_orders', [5, 15])
        // Multiple Column: groupBy('first_name', 'status')
        $users = DB::table('users')->offset(10)->limit(5)->get();
        // Offset: Skip the first 10 rows of the result set.
        // Limit: Maximum of 5 rows after the offset has been applied.

        // Conditional where
        DB::table('users')->when($role, function (Builder $query, string $table){
            $query->where('role_id', $role);
        }); // If $role is true then execute the closure.
        // We can pass third argument as closure, when role will be false that closure will be called.

        // Pessimistic Locking:
        // Pessimistic Locking is a concurrency control strategy based on the assumption that "something will probably go wrong"—specifically, that multiple users will try to update the same piece of data at the exact same time.
        // sharedLock(): A shared lock prevents the selected rows from being modified until your transaction is committed
        // lockForUpdate(): A "for update" lock prevents the selected records from being modified or from being selected with another shared lock
        // Example: There is only one physical seat, but thousands of people are clicking "Buy" at the exact same moment.
        // While the system is checking your account and calculating the price, another process (like an admin) might try to "deactivate" that seat for maintenance.
        // sharedLock apply: Both can read, but admin cant delete or update until your price calculation is finished.
        // Both user doing payment for the ticked. lockForUpdate apply():
        // When Alice’s request hits the server, the database "grabs" Seat 42A and puts it in a vault. When Bob’s request arrives a millisecond later, the database tells him: "Wait. This row is being modified."
        DB::transaction(function () use ($seatId, $userId) {
            
            // 1. USE LOCK FOR UPDATE
            // We lock the seat immediately so no one else can start a purchase for it.
            $seat = Seat::where('id', $seatId)
                        ->lockForUpdate() 
                        ->first();

            // 2. Check if it was already sold while we were waiting for the lock
            if ($seat->status === 'sold') {
                throw new \Exception("This seat is no longer available.");
            }

            // 3. USE SHARED LOCK
            // We check the user's balance. We don't want their balance to change 
            // (e.g., from another purchase) while we are processing this.
            $user = User::where('id', $userId)
                          ->sharedLock()
                          ->first();

            if ($user->balance < $seat->price) {
                throw new \Exception("Insufficient funds.");
            }

            // 4. Perform the logic
            $user->decrement('balance', $seat->price);
            $seat->update(['status' => 'sold', 'user_id' => $userId]);

            return "Ticket purchased successfully!";
        });

        // Debugging:
        DB::table('users')->where('votes', '>', 100)->dd();
        // dump(), dumpRawSql(), ddRawSql()

        // Reusable Query:
        // If we have same query applied in multiple places, we can make a central place for it using tap and pipe.
        // See Scopes/DestinationFilter and finally use it anywhere:
        // tap return the query build, if we want to extract an object  that executes the query and returns another value, use pipe.
        DB::table('flights')->tap(new DestinationFilter($destination))->pipe(new Paginate)->orderByDesc('id')->get(); 
        // Tap: Pass the query to this class, let it do something, but then give me back the query so I can keep going.
        // Pipe: Pass the query to this class, and whatever that class returns is the new value of this chain.
        // If we use tap for Paginate, and if call get() now, will get all the flights from the database, not a paginated list.
    }

    // User::all(): Runs SELECT * and returns a Collection.
    // $collection->all(): Returns the underlying array inside the collection.

    // The "Memory-Only" Methods (Collection):
    // These methods are for high-level logic. SQL (the database language) is very fast but "dumb"—it doesn't know how to run complex PHP functions.
    // These methods all are entirely for collection level, not for database query. We have to make data as collection at first.
    public function collectionMethods(){
        // Collection class provides a fluent, convenient wrapper for working with arrays of data.
        // So that we can chain up other methods of collection instance.
        // In general, collections are immutable, meaning every Collection method returns an entirely new Collection instance.
    
        $collection  = collect([1, 2, 3]); // Return a Collection instance.
        // The results of Eloquent queries are always returned as Collection instances.
        Collection::make([1, 2, 3]); // Same as collect() helper function.
        Collection::wrap('John Doe'); // ['John Doe']. wrap in a collection.
        
        $collection->toArray(); // Convert collection into plain php array.
        Collection::unwrap(collect('John Doe')); // ['John Doe']

        // Collections are macroable, we can add additional custom methods of our own. 
        // We should declare a collection macro in a service provider's boot, see AppServiceProvider.

        // Checking Type:
        $collection->ensure(User::class);
        $collection->ensure([User::class, Customer::class]); // Any of type from the array.
        $collection->ensure('int');
        // If not that type, throw UnexpectedValueException.

        $collection->isEmpty(); // isNotEmpty()
        $collection->has('product'); // Check if key exists.
        $collection->has(['product', 'amount']);
        $collection->hasAny(['product', 'price']);
        collect(['1'])->hasSole(); // true, only one item.
        collect([1, 2, 3])->hasSole(fn (int $item) => $item === 2);
        collect([])->hasMany(); // false, true if contains multiple items.
        collect([1, 2, 3])->hasMany();
        // hasMany(fn ($item) => $item['age'] === 2)
    
        // All of these methods may be chained to fluently manipulate the underlying array.
        // Almost every method returns a new Collection instance, allowing us to preserve the original copy of the collection when necessary.
        $collection->all(); // Returns the underlying array represented by the collection: [1, 2, 3]. Like filter()->all(), if only filter it will return collecton instance.
        $collection->only(['name', 'price']); // Return only those keys.
        $collection->except(['price', 'discount']); // Return except those keys.
       
        // reverse(): Reverse preserving the original key.
        // shuffle(): Randomly shuffle the items.
        
        Collection::fromJson($json); // Create collection from json string.
        collect(['name' => 'Desk', 'price' => 200])->toJson(); // '{"name":"Desk", "price":200}'
        $collection->toPrettyJson(); // Convert into fromatted Json string using JSON_PRETTY_PRINT
        
        $collection->get('name');
        $collection->get('age', 34); // Second argument is default value if not found.
        $collection->get('email', function () { return 'taylor@example.com';}); // Callback as the defult value.
        $collection->keys(); // Return all keys.

        $collection->value('price'); // Take given value of the first items by specified key.
        $collection->values(); // Return a new collection with key reset.

        collect([0, 1, 2, 3, 4, 5])->take(3); // [0, 1, 2]
        $collection->take(-2) // [4, 5]
        collect([1, 2, 3, 4])->takeUntil(3); // [1, 2]
        collect([1, 2, 3, 4])->takeUntil(function (int $item) {
            return $item >= 3;
        }); // [1, 2]. If not true or given value not found, all values return.
        collect([1, 2, 3, 4])->takeWhile(function(int $item){
            return $item < 3;
        }); // [1, 2]

        // pluck method retrieves all of the values for a given key: (same as select() or get() selected column)
        $collection->pluck('name'); // ['Desk', 'Chair']
        $collection->pluck('name', 'product_id'); // Can speficy how values will be keyed: ['prod-100' => 'Desk', 'prod-200' => 'Chair'].
        // If duplicate keys exist, the last matching element will be inserted into the plucked collection.
        $collection->pluck('speakers.first_day'); // Nested values with dot notation.

        collect([1, 1, 2, 2, 3, 4, 2])->unique(); // Remove duplicates: [1, 2, 3, 4]
        $collection->unique('brand')->values()->all(); // Dealing with nested arry, specify key.
        $collection->unique(function (array $item) {
             return $item['brand'].$item['type']; // which value should determine an item's uniqueness.
        });
        // For strict comparison: uniqueStrict().

        $collection->search(4);
        // search('4', strict: true)
        // search(function (int $item, int $key){})

        $collection->after(3); // 4. Item after a item, if not return null.
        $collection->after(3, strict: true);
        $collection->after(function(int $item, int $key){return $item >5}); // Search items greater than 5 and then return the next item of them.
        // Same goes for before.
        
        collect([1, 2, 3, 4, 5])->prepend(0); // [0, 1, 2, 3, 4, 5]. Can give key: prepend(0, 'zero'): 'zero' => 0
        $p = collect(['product_id' => 'prod-100', 'name' => 'Desk']);
        $p->pull('name'); // 'Desk'
        $p->all(); // ['product_id' => 'prod-100'].

        collect([1, 2, 3, 4, 5])->shift(); // Removes and returns the first item from the collection: 1
        // shift(3): Remove and Return first 3.
        collect([1, 2, 3, 4, 5])->pop(); // Remove last item and return: [5]. and [1,2,3,4] will return if we perform all on the collection.

        $collection->forget('name'); // Remove item by key.
        collect([1, 2, 3, 4, 5, 6, 7, 8, 9])->forPage(2, 3); // [4, 5, 6]. 2 is the page number, 3 items per page.

        collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->skip(4)->all(); // [5, 6, 7, 8, 9, 10]
        collect([1, 2, 3, 4])->skipUntil(3): // [3, 4] 
        collect([1, 2, 3, 4])->skipUntil(function (int $item) {return $item >= 3;})->all(); // [3, 4]
        // skipWhile().
        
        // push(): If we push then call all on that collection, items will be push at last.
        collect(['product_id' => 1, 'name' => 'Desk'])->put('price', 100); // 'product_id' => 1, 'name' => 'Desk', 'price' => 100] 

        collect([1, 2, 3, 4, 5, 6, 7])->chunk(4); // [[1, 2, 3, 4], [5, 6, 7]]
        // Can use in blade views to make responsive grid system.
        // chunkWhile()

         collect([ [1, 2, 3],  [4, 5, 6],])->collapse(); // [1, 2, 3, 4, 5, 6]
         // collapseWithKeys(): ['first'  => [1, 2, 3], 'second' => [4, 5, 6]]
         collect(['products' => ['desk' => ['price' => 100]]])->dot(); // ['products.desk.price' => 100]
         // $dottedCollection->undot();

         LazyCollection::make(function () { yield 1; yield 2; })->collect()->all();
         // The collect method is especially useful when you have an instance of Enumerable and need a non-lazy collection instance. 
         
         collect(['name', 'age'])->combine(['George', 29]); // ['name' => 'George', 'age' => 29]
         collect(['John Doe'])->concat(['Jane Doe'])->concat(['name' => 'Johnny Doe']); // ['John Doe', 'Jane Doe', 'Johnny Doe']
         collect(['name' => 'Taylor', 'framework' => 'Laravel'])->flip(); // ['Taylor' => 'name', 'Laravel' => 'framework']
         
         collect(['name' => 'Desk', 'price' => 100])->contains('Desk');  // Alias: some().
         // Can pass callback: contains(function (int $value, int $key) {}
         // Can pass key value pair: contains('product', 'Bookcase')
         // Contains do loose comparison, can use: containsStict()
         // Reverse: doesntContain(), doesntContainStrict()
         collect([])->containsManyItems(); // false. when aray contains multiple items then true.
         collect(['a', 'b', 'a', 'c', 'b'])->duplicates(); // [2 => 'a', 4 => 'b']. 
         // Can pass key: duplicates('emails'). duplicatesStrict().
         
         collect([['foo' => 10], ['foo' => 10]])->avg('foo'); // Average for the given key. can use average() also.
         // count(): Total Number of Items.
         // countBy(): Counts the occurrences of values in the collection.
         collect([1, 2, 2, 2, 3])->countBy(); // [1 => 1, 2 => 3, 3 => 1]
         // Can pass closure. countBy(function (string $email). Output:  ['gmail.com' => 2, 'yahoo.com' => 1]
         // crossJoin()

         $collection->dd(); // dump(). dd stops the execution, dump dont.

         collect(['a', 'b', 'c', 'd', 'e', 'f'])->nth(4); // Take first index, and nth index.
         // nth(4, 1): ['b', 'f']. The Offset (1): The method skips index 0 ('a') and starts immediately at index 1, which is 'b'. This is your first captured item. The Interval (4): It then counts 4 positions forward from 'b'.
         collect([1, 2, 3, 4, 5])->diff([2, 4, 6, 8]); // Compare with collection or array. [1, 3, 5]
         // diffAssoc(), diffAssocUsing(), diffkeys()

         $collection->each(function (int $item, int $key) {}); // Return false for any condition if want to stop the execution.
         // foreach is core feature, each is collection feature in object oriented way that provides chaining.
         collect([['John Doe', 35], ['Jane Doe', 33]])->eachSpread(function (string $name, int $age) {}); // For nested values.
        
         $collection->every(function (int $value, int $key){ return $value > 2;}); // If all elements pass the test.
         // If collection is empty, every returns true.
         
         $collection->first(function (int $value, int $key){ return $value > 2;}); // First item that passes the test.
         // Can call just first() to get the first element, if no element null returnted.
         // Same goes for firstOrFail(): Throws ModelNotFoundException if no element found.
         $collection->firstWhere('status', 'active'); // First item where key value match
         $collection->firstWhere('age', '>=', 18);
         $collection->firstWhere('age'); // If value is truthy.
         
         collect([1, 2, 3, 4])->sole(function (int $value, int $key) { return $value === 2;}); // First element if just one element pass the truth test.
         // Key value pair: $collection->sole('product', 'Chair')
         // Without argument $collect->sole(): Get the first element in the collection if there is only one element.
         // ItemNotFoundException if no items found to return.
         
         // last(): Returns the last element in the collection that passes a given truth test.

         $collection->filter(function (int $value, int $key) { return $value > 2;}); // Return only those items that pass the test.
         // If no callback is supplied, all entries of the collection that are equivalent to false will be removed
         collect([1, 2, 3, null, false, '', 0, []])->filter()->all(); // [1, 2, 3]
         // reject(): Opposite of filter.

         // Separate elements that pass a truth test:
         [$underThree, $equalOrAboveThree] = collect([1, 2, 3, 4, 5, 6])->partition(function (int $i) {
            return $i < 3;
         });
         $underThree->all(); // [1, 2]
         $equalOrAboveThree->all(); // [3, 4, 5, 6]

          // Reduces the collection to a single value:
         collect([1, 2, 3])->reduce(function(?int $carry, int $item){
            return $carry + $item;
         }); // Output: 6.
         // The value for $carry on the first iteration is null, but
         // Can specify: reduce(function(){}, 4), Output will be 10.
         // using key: reduce(function (int $carry, int $value, string $key) use ($ratio)...

         // reduceSpread() allows you to carry multiple.
         [$odds, $evens] = collect([1, 2, 3, 4, 5, 6])->reduceSpread(function ($oddList, $evenList, $number) {
            if ($number % 2 === 0) {$evenList[] = $number;}
            else{$oddList[] = $number;}
         }, [], []); // $odds: [1, 3, 5], $evens: [2, 4, 6].
         // Similar to partition, but partition give only two , we can spread multiple here.

         $collection->percentage(fn (int $value) => $value === 1); // Give percentage if pass a truth test. 33.33
         // By default rouded to two decimal places. Can provide- precision: 3
         
         collect([['name' => 'Sally'],['school' => 'Arkansas'],['age' => 28]])->flatMap(function (array $values) {return array_map('strtoupper', $values);}); // Map and flatten one level- ['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28']
         $collection->flatten(); // Flatten multi-dimensional collection into a single level.
         $collection->flatten(1); // Flatten only one level of depth.

         $collection->groupBy('account_id'); // Groups the collection's items by a given key
         $collection->groupBy(function (array $item, int $key) {return substr($item['account_id'], -3);}); // Group by last three characters of account_id.
         $data->groupBy(['skill', function (array $item) {return $item['roles'];}], preserveKeys: true); // Multiple level grouping.

         //* Higher Order Messages:
         // Performing common actions on collections
         // They allow you to call methods directly on the collection as if We were calling them on each individual item inside that collection.
         // Usually, if you want to perform an action on every object in a collection, you’d use a closure. Higher Order Messages replace that closure with a property-like call.
         $users = User::where('votes', '>', 500)->get();
         $users->each->markAsVip(); // Rather than using closure- each(function ($vipUser) { $vipUser->markAsVip(); });
         $users->sum->votes;
         // Supported Methods: average, avg, contains, each, every, filter, first, flatMap, groupBy, keyBy, map, max, min, partition, reject, skipUntil, skipWhile, some, sortBy, sortByDesc, sum, takeUntil, takeWhile, and unique.
         // Example: $users->max->age, $users->contains->is_admin, $employees->every->is_on_vacation, $posts->filter->is_published, $tasks->reject->is_completed, $orders->unique->customer_id
         // $books->sortBy->release_date, $tickets->sortByDesc->priority_level, $students->groupBy->graduating_year, $logs->skipUntil->is_error, $uploads->takeWhile->is_small_file etc.

         collect([['account_id' => 1, 'product' => 'Desk'], ['account_id' => 2, 'product' => 'Chair']])->implode('product', ', '); // 'Desk, Chair'
         // Can pass callback: implode(function (array $item, int $key) {  return strtoupper($item['product']); }, ', ' }

         $collection->intersect(['Desk', 'Chair', 'Bookcase']);
         // Use callback: intersectUsing()
         // For associative array: intersectAssoc(), intersectAssocUsing(), intersectBykeys()
         collect([['name' => 'User #1', 'email' => 'user1@example.com']])->multiply(3); // Three copies of same data.

         collect(['product_id' => 1, 'price' => 100])->merge(['price' => 200, 'discount' => false]); // ['product_id' => 1, 'price' => 200, 'discount' => false]
         collect(['product_id' => 1, 'price' => 100])->mergeRecursive(['product_id' => 2,'price' => 200, 'discount' => false]); // ['product_id' => [1, 2], 'price' => [100, 200], 'discount' => false]
         // While a standard merge is "shallow" and simply replaces the old value with the new one, recursiveMerge drills down into the sub-arrays to combine them.

         collect(['Chair', 'Desk'])->zip([100, 200]); // [['Chair', 100], ['Desk', 200]].

         collect([1 => ['a'], 2 => ['b']])->union([3 => ['c'], 1 => ['d']]); // [1 => ['a'], 2 => ['b'], 3 => ['c']]
         // union method adds the given array to the collection. If the given array contains keys that are already in the original collection, the original collection's values will be preferred.

         collect(['Taylor', 'Abigail', 'James'])->replace([1 => 'Victoria', 3 => 'Finn']); // ['Taylor', 'Victoria', 'James', 'Finn']. same as merge, but replace with numeric keys also.
         // replacerecursive(['ok', 2 => [1 => 'King']]): For multi step array.
         
         collect(['a', 'b', 'c'])->join(', ', ', and '); // 'a, b, and c'
         collect(['a'])->join(', ', ' and '); // 'a'
         collect([])->join(', ', ' and '); // ''
         collect(['A', 'B', 'C'])->pad(5, 0); // ['A', 'B', 'C', 0, 0]. Fill the arry with 0 until it have 5 elements.
         // Add at start: pad(-5, 0)

         $collection->keyBy('product_id'); // [ 'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'], ...]
         // If multiple keys then last one will appear.
         // Can pass callback.

         collect([1, 2, 3, 4])->lazy()->where('country', 'FR')->all(); // Returns a lazy collection.

         // map(): New collection instance based on callback.
         collect(['USD', 'EUR', 'GBP'])->mapInto(Currency::class); // ['USD' => new Currency('USD'), 'EUR' => new Currency('EUR'), 'GBP' => new Currency('GBP')], pass the values into constructor of the class.
         collect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9])->chunk(2)->mapSpread(function (int $even, int $odd) {  return $even + $odd; })->all(); // [1, 5, 9, 13, 17]. 
         // mapToGroups(): Map the collection into groups based on the given callback. 
         collect(['Alice', 'Bob', 'Charlie'])->mapWithKeys(function (string $name) { return [strtolower($name) => strlen($name)]; })->all(); // ['alice' => 5, 'bob' => 3, 'charlie' => 7]

         // Transform does same thing as map, but transform modifies the collection itself where map return a new collection.
         $collection->transform(function (int $item, int $key) {
            return $item * 2;
         });

         // The tap method passes the collection to the given callback, allowing you to "tap" into the collection at a specific point and do something with the items while not affecting the collection itself.
         collect([2, 4, 3, 1, 5])->sort()->tap(function(Collection $collection){
            Log::debug('Values after sorting', $collection->values()->all());
         }); // 1, 2, 3, 4, 5
         // map is for changing collectionitself, tap is not for change.
         User::find($id)->tap(function ($user) {
            $user->update(['status' => 'active']);
            Log::info("User {$user->id} is now active.");
          });

         collect([1, 1, 2, 4])->median(); // 1.5. avg(), min(), max(), sum() etc.

         collect([1, 2, 3])->pipe(function (Collection $collection) {
            return $collection->sum();
         }); // 6. Pipe exists for when you need to perform custom logic or external processing while staying inside the "fluent" chain.
         // Real Life Example: collect([10, 20, 30])->sum()->pipe(fn($sum) => $myAccountingService->calculateTax($sum))
         // Pipeline pattern: collect($userData)->pipe(new GenerateUserStats)->pipe(new FormatForExcel)->pipe(new ApplyCompanyBranding).
         
         collect([1, 2, 3])->pipeInto(ResourceCollection::class); // Now [1,2,3] will be passed into ResourceCollection's connstructor.
         // It is same as: new ResourceCollection(collect([1, 2, 3])). So why need it?
         // Example: $report = $orders->filter(fn($o) => $o->active)->values()->pipeInto(MonthlyFinancialReport::class); $report->calculateTax();. corresponding to new MonthlyFinancialReport($orders->filter(fn($o) => $o->active)->values())- Better readability using pipeInto.
         // While pipe takes a closure (a function), pipeInto takes a Class Name.
        
         // pipeThrough method passes the collection to the given array of closures and returns the result of the executed closures:
         collect([1, 2, 3])->pipeThrough([function(Collectiion $c){}, function(Collection $c){}]); // Logic should be sequential. Reordering can give different result.

         //  $collection->random(), random(3)- Take three random elements, if elements not present that much, throw InvalidArgumentException.
         collect()->range(3, 6); // [3, 4, 5, 6]
         collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->slice(4); // [5, 6, 7, 8, 9, 10]
         // slice(4, 2): Slice from index 4, Take 2 items.
         
        $collection = collect([1, 2, 3, 4, 5]);
        $chunk = $collect->splice(2)->all(); // [3, 4, 5]. 
        $collection->all(); // [1, 2].
        // Defining size: splice(2, 1).
        // New items to replace the removed items in original collection: splice(2, 1, [10, 11]), Output: $collection->all():  [1, 2, 10, 11, 4, 5]
        
        collect([1, 2, 3, 4, 5])->split(3); // [[1, 2], [3, 4], [5]], Focuses on the number of groups.
        collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->splitIn(3); // [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]], Focuses on filling the groups.

         collect([1, 2, 3, 4, 5])->sliding(2); // [[1, 2], [2, 3], [3, 4], [4, 5]], slide like window.
         // sliding(2, step: 2): [1, 2], [2, 3]

        // Real life use case of sliding with eachSpread:
        $transactions = collect([
            (object) ['amount' => 100, 'total' => 0], // Monday
            (object) ['amount' => 50,  'total' => 0], // Tuesday
            (object) ['amount' => -30, 'total' => 0], // Wednesday
            (object) ['amount' => 200, 'total' => 0], // Thursday
        ]);
        // You want to calculate a "Running Balance" (how much money is in the account after each transaction).
        // Window 1: [Monday, Tuesday], Window 2: [Tuesday, Wednesday], Window 3: [Wednesday, Thursday].
        $transactions->sliding(2)->eachSpread(function ($previous, $current) {
            $current->total = $previous->total + $current->amount;
        });
        // [amount: 100, total: 100], [amount: 50, total: 150], [amount: -30, total: 120], [amount: 200, total: 320]

        // Sorting:
        collect([5, 3, 1, 2, 4])->sort()->values()->all(); // sort method do not preserve the keys, so we used values(). [1, 2, 3, 4, 5]
        // Can pass callback to make own sorting algorithm.
        $collection->sortBy('price')->values()->all();
        // collection->sortBy('title', SORT_NATURAL)
        // sortBy(function (array $product, int $key) {})->values()->all()
        // Sort using multiple values: sortBy([['name', 'asc'], ['age', 'desc'],])
        // sortKeys():  sorts the collection by the keys of the underlying associative array.
        // $collection->sortKeysUsing('strnatcasecmp')
        // In opposite orders: sortDesc(), sortByDesc(), sortKeysDesc().

        Collection::times(10, function(int $num){
            return $number * 9;
        })->all(); // [9, 18, 27, 36, 45, 54, 63, 72, 81, 90]

        $collection->unless(true, function (Collection $collection, bool $value) {
            return $collection->push(4);
        }); // Can pass second callback, if true then second callback will work.
        // unlessEmpty(), unlessNotEmpty()

        // Conditions:
        // when(), whenEmpty(), whenNotEmpty().
        // where(), whereStrict(), whereBetween(), whereNotbetween(), whereIn(), whereNotIn(), whereInStrict(), whereNotInStrict().
        // whereInstanceOf(), whereNull(), whereNotNull(), 
    }

    // Real life examples:
    // https://github.com/charindu77/readings/blob/main/Laravel%20Collections:%2015%20Open-Source%20Examples%20of%20%22Chained%22%20Methods.md

    public function lazyCollectionMethods(){
         // If you try to process a 500MB CSV or 100,000 Eloquent records, your PHP script will likely hit the memory limit and crash.
         // the LazyCollection class leverages PHP's generators to allow US to work with very large datasets while keeping memory usage low.
         // They achieve this by loading and processing items only as they are needed, rather than loading.
         $lazy = LazyCollection::make(function() {
            $handle = fopen('huge_production.log', 'r');
            while (($line = fgets($handle)) !== false) {
                yield $line;
            }
         }); // Now can chain up collection methods on it.
         $lazy->filter()->each(function ($line) {
            // Process the line...
         });

         // LazyCollection::times(INF): Creating an infinite mathematical sequence.
         $infinite = LazyCollection::times(INF)->map(function ($number) {
                return $number * 9;
          });
          // It doesn't actually try to create an infinite array in memory (which would crash your server instantly)
          // Instead, it creates a generator that will keep spitting out numbers (1,2,3,4,…) forever, or until you manually tell it to stop.
         
                  // Real life Use Cases of Lazy Collection: Processing massive log files, Large Database Exports, Huge CSV Imports
        // Aggregating Data from an External API:
        $allOrders = LazyCollection::make(function () {
            $page = 1;
            while ($page !== null) {
                $response = Http::get("https://api.service.com/orders?page={$page}")->json();
        
                foreach ($response['data'] as $order) {
                    yield $order;
                    // The foreach loop starts. When it hits yield $order;, it "pauses" the loop and hands that one order to the sum() method.
                    // sum() adds that order's price to its total and asks for the next item.
                }

                $page = $response['next_page_url'] ? $page + 1 : null;
                // Once the first 50 orders are processed, the foreach ends. The while loop continues, increments the page, and makes the next API call for Page 2.
            }
        });
        // The API is only called as you iterate
        $totalValue = $allOrders->sum('price');
         
         // The query builder's cursor or lazy method returns a LazyCollection instance.
         // Imagine, need to iterate through 10,000 Eloquent models:
         foreach (User::cursor() as $user) {
                // Process the user...
         }
         
          // We can use normal collection's almost every methods on LazyCollection instance.
          // Additionally, LazyCollection provides some methods that are specifically designed for working with large datasets:
         
         // takeUntilTimeout(): Allows the collection to keep processing items as fast as possible, but the moment the clock hits your limit, it gracefully stops the loop and lets the script finish.
         // You might have a background job that is allowed to run for exactly 60 seconds before the server kills the process or the queue times out.
         // Imagine you have 100,000 customers to email. Your server has a strict 5-minute execution limit for cron jobs.
         // If you use a standard foreach, the server might kill the script at the 5-minute mark exactly while it's in the middle of talking to the email provider, potentially causing errors or double-sending.
         User::lazy()
               ->takeUntilTimeout(Carbon::now()->addMinutes(4))
               ->each(function ($user) {
                    $user->sendDailyReport();
        });

        // tapEach: tapEach() allows you to perform an action (like logging, debugging, or updating a progress bar) for every single item in the LazyCollection as it is being iterated, without changing the item itself or affecting the rest of the chain.
        $lazy->tapEach(fn ($row) => Log::info("Processing: " . $row['id']))->filter(fn ($row) => $row['price'] > 100)->tapEach(fn ($row) => Log::info("Passed filter: " . $row['id']))->collect();
        // each() terminates the chain but tapEach() not, ecah() to perform the final action while tapEach() to peek or perform side effcts, eah() runs emmidiately when tapEach() run only when the data is finally requested.
        
        // throttle: seful for situations where you may be interacting with external APIs that rate limit incoming requests
        // User::where()->cursor()->throttle(seconds: 1)

        // remember():
        $users = User::cursor()->remember(); // No query has been executed yet...
        $users->take(5)->all(); // Query executed, The first 5 users are hydrated from the database...
        $users->take(20)->all(); // First 5 users come from the collection's cache, rests are hydrated from database.

        // withHearBeat: Useful for long-running operations that require periodic maintenance tasks, such as extending locks or sending progress updates
        // A Heartbeat is a signal sent at regular intervals to indicate that a program is still performing its task and hasn't crashed or frozen.
        // Imagine you are running a background job to sync thousands of products to an external Marketplace API.
        User::lazy()->withHeartBeat(seconds: 30, function () {
            // Extend a lock, send a progress update, or perform any other periodic task...
        })->each(function ($user) {
            // Sync the user to the external service...
        });

        // chunk(): Not a LazyCollection, return type boolean, Multiple queries (Batches), Memory usgae Low (Current batch), Can not chain.
        // lazy(): return LazyCollection, Multiple queries (Batches), memory usage Low (Current batch), Chainable.
        // cursor(): return LazyCollection instance, Single query (Streamed), Memory usgae Lowest (1 Model), Can chain.
        // Use chunk() if you just need to update records and don't care about returning or chaining a collection.
        // Use cursor() if you have a massive table, you aren't doing other DB queries inside the loop, and you want the absolute minimum memory footprint.
        // Use lazy() if you want the best of both worlds: memory safety via chunking, but the ability to use Higher Order Messages and chain methods like ->filter() and ->map().
        // LazyCollection::make() is the "manual" version used for everything else. You use it when your data source is not a Laravel model.    
    }

    public function eloquentCollection(){
        // Laravel’s Base Collection is for general data (like arrays of numbers or strings), while the Eloquent Collection is a specialized version specifically engineered to handle Database Models.
        // While the Eloquent Collection inherits everything from the Base Collection, it adds methods that understand the "DNA" of a database record.
        // If you are just manipulating an array of API data, you don't want to dig through methods like load() or makeVisible() that only apply to database models.
        // The Base Collection is part of the Illuminate\Support package (a general utility), while Eloquent Collections are part of Illuminate\Database.
        // Some methods (like unique) actually behave differently in Eloquent.
        // While most Eloquent collection methods return a new instance of an Eloquent collection,
        // the collapse, flatten, flip, keys, pluck, and zip methods return a base collection instance.
        $user = User::all();

        $users->find(1); // Returns the model that has a primary key matching the given key.
        $users->findOrFail(1); // If not found, ModelNotFound exception.
        $users->contains(1); // 1 is the primary key, if contains that model.
        $users->contains(User::find(1));
        $users->append('team'); // append team in every model in collection.
        $users->append(['team', 'is_admin']);
        $users->setAppends(['is_admin']); //  temporarily overrides all of the appended attributes on each model in the collection.
        $users->withoutAppends(); // Temporarily removes all of the appended attributes on each model in the collection.
        $users->except([1, 2, 3]); // all of the models that do not have the given primary keys.
        $users->only([1, 2, 3]);
        $users->diff(User::whereIn('id', [1, 2, 3])->get()); // all that are not present in given collection.
        $users->intersect(User::whereIn('id', [1, 2, 3])->get()); // returns all of the models that are also present in the given collection.
        $users->fresh('comments'); // retrieves a fresh instance of each model in the collection eager loaded only the specified relation.
        $users->load(['comments', 'posts']); // Eager load given relations.
        $users->load('comments.author');
        $users->load(['comments', 'posts' => fn ($query) => $query->where('active', 1)]);
        $users->loadMissing(['comments', 'posts']); // Eager load if already not loaded.
        $users->modelKeys(); // Primary keys of all models.
        $users->makeVisible(['address', 'phone_number']); // Make attributes visible which is hidden in model.
        $users->makeHidden(['address', 'phone_number']);
        $users->mergeVisible(['middle_name']); // makes additional attributes visible while retaining existing visible attributes.
        $users->mergeHidden(['last_login_at']);
        $users->setVisible(['id', 'name']); // temporarily overrides all of the appended attributes on each model in the collection.
        $users->setHidden(['email', 'password', 'remember_token']);
        // MakeVisible overrides the current visibility, mereVisible just merge with current.
        $partition = $users->partition(fn ($user) => $user->age > 18); // $partition, $partition[0], $partition[1].
        $users->toQuery()->update(['status' => 'Administrator',]); // Returns an eloquent query builder instance containing a whereIn() constraint on the collection's primary keys.
        $users->unique(); // Return all unique models of the collection.

        // If we want custom Collection object for a model, specify above model: #[CollectedBy(UserCollection::class)]. or,
        // Implement newCollection() method in the model.
        // If you would like to use a custom collection for every model in your application, you should define the newCollection method on a base model class.
        // In a real-life application, you use them to move "collection-level logic" (logic that involves multiple rows) out of your User model and into a dedicated class.
        // Imagine you have a collection of Invoice models. You want to calculate the total tax, the total amount due, and the average discount across all those invoices.
        // Without Custom Collections: You’d put this logic in your Controller or a Service class, making it hard to reuse.
        // With Custom Collections: You add a totalDue() method directly to your InvoiceCollection.
    }

    public function fileSystem(Request $request){
        // Laravel provides a powerful filesystem abstraction  using Flysystem PHP package by Frank de Jonge.
        // Configuration: config/filesystems.php. Each disk represents a particular storage driver and storage location. 
        // Can configure as many disks as we like and may even have multiple disks that use the same driver.
        // May wish to sanitize your file paths before passing them to Laravel's file storage methods.

        //* Symbolic Link:
        // If your public disk uses the local driver and you want to make these files accessible from the web, you should create a symbolic link from source directory:
        // Create symbolic link: php artisan storage:link
        asset('storage/file.txt');
        // We can configure additional symbolic link in config file: 'links' =>[public_path('images') => storage_path('app/images')]
        // Unlink: php artisan storage:unlink

        //* Storing Files:
        Storage::put('avatars/1', 'Contents'); // Put content on default disk. 
        if (! Storage::put('file.jpg', $contents)) {} // If unable to write, return false.
        // In in cofig file, we use: 'throw' => true, throw an instance of League\Flysystem\UnableToWriteFile when write operations fail.
        Storage::disk('local')->put('example.txt', 'Contents'); // Store in storage/app/private/example.txt
        Storage::build(['driver' => 'local', 'root' => '/path/to/root'])->put('image.jpg', 'Contents'); // On demnd disk with necessary configurations.
        // Public Disk: storage/app/public, public disk uses the local driver.
        Storage::prepend('file.log', 'Prepended Text'); // write to the beginning.
        Storage::append('file.log', 'Appended Text');
        
        //* Retrieving Files:
        Storage::disk('s3')->exists('file.jpg'); // Check existence.
        Storage::disk('s3')->missing('file.jpg'); // Check if not exists.
        Storage::get('file.jpg'); // Get content of the file.
        Storage::json('orders.json'); // retriev json content and decode.
        Storage::url('file.jpg'); // Get url of the file.
        Storage::files($directory); // Get all files of a directory.
        Storage::allFiles($directory); // Get all files and sub directories of a directory.
        // directories(), allDirectories(), makeDirectory(), deleteDirectory().
        
        Storage::download('file.jpg', $name, $headers); // Forces the browser to download at given path, $name will show in user's device.
        Storage::copy('old/file.jpg', 'new/file.jpg');
        Storage::move('old/file.jpg', 'new/file.jpg');

        //* Temporary Url:
        // Your files are locked in a safe (Private Storage), but you give someone a key that only works for 5 minutes.
        // Imagine you are building a SaaS platform where users can download their monthly invoices (PDFs).
        // The Risk: If you store invoices in public/storage/invoices/1.pdf, anyone could guess the URL and see another user's financial data by just changing the 1 to a 2.
        // The Solution: You store all invoices in a private S3 bucket or a private local folder.
        Storage::temporaryUrl('file.jpg', now()->plus(minutes: 5)); // Last arg is expiration time of url, can pass s3 request parameters as third arg in [].
        // If want to change url by Storage facade to show in cofig: 'url' => env('APP_URL').'/storage'.
        ['url' => $url, 'headers' => $headers] = Storage::temporaryUploadUrl('file.jpg', now()->plus(minutes: 5));
        // Temporary upload URL is useful in serverless environments that require the client-side application to directly upload files to a cloud storage system such as Amazon S3.
        
        //* File Metadata: Information about the files.
        Storage::size('file.jpg'); // Get the size of the file.
        Storage::lastModified('file.jpg');
        Storage::mimeType('file.jpg');
        Storage::path('file.jpg'); // return absolute path of the file if local driver, relative path if s3 driver.

        //* Automatic Streaming:
        // Streaming files to storage offers significantly reduced memory usage.
        // Streaming is the process of moving a file in small "chunks" (pieces) rather than loading the entire file into your computer's memory (RAM) all at once.
        // Laravel opens a tiny "pipe." It reads 5MB, moves it to the storage, deletes it from RAM, and repeats. The server only ever uses a few megabytes of RAM, no matter how big the file is.
        // Necessary when video uploads, database expots, high resolution images.
        Storage::putFile('photos', new File('/path/to/photo')); // Automatically generate a unique ID for filename
        Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg'); // Manually specify a filename
        Storage::putFile('photos', new File('/path/to/photo'), 'public'); // visibility seful if you are storing the file on a cloud disk such as Amazon S3 and would like the file to be publicly accessible via generated URLs.

        //* File Upload:
        $file = $request->file('avatar');
        $file->store('avatars'); // will generate a unique ID to serve as the filename.
        Storage::putFile('avatars', $file);
        $file->storeAs('avatars', $request->user()->id);
        Storage::putFileAs('avatars', $file, $request->user()->id);
        $file->store( 'avatars/'.$request->user()->id, 's3'); // spcify disk.
        
        $file->getClientOriginalName(); // other info.
        $file->getClientOriginalExtension();
        // But they are unsafe, because name and extension could have maliscious script or thing. Rather than:
        $file->hashName(); // Generate a unique, random name
        $file->extension(); // Determine the file's extension based on the file's MIME type.
        // storePublicly(), storePubliclyAs()

        //* File Visibility:
        // An abstraction of file permissions across multiple platforms.
        Storage::put('file.jpg', '$contents', 'public');
        Storage::getVisibility('file.jpg');
        Storage::setVisibility('file.jpg', 'public');

        //* Deleting Files:
        Storage::delete('file.jpg');
        Storage::delete(['file.jpg', 'file2.jpg']);
        Storage::disk('s3')->delete('path/file.jpg');

        //* S3 Driver:
        // Install Filesystem S3 Package: composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies.
        // Configure credentials in config file with env variables.
        // S3 compatible filesystems such as rustFS, Cludfare, digitalOcean spaces etc: 'endpoint' => env('AWS_ENDPOINT', 'https://rustfs:9000'),

        //* FTP Driver:
        // Install Package: composer require league/flysystem-ftp "^3.0"
        // Configure credentials.

        //* SFTP Driver:
        // composer require league/flysystem-sftp-v3 "^3.0"

        //* Scoped and Read Only Filesystem:
        // Allows us to define a filesystem where all paths are automatically prefixed with a given path prefix.
        // "Read-only" disks allow you to create filesystem disks that do not allow write operations. 
        // composer require league/flysystem-path-prefixing "^3.0", composer require league/flysystem-read-only "^3.0"
        // Configure 'driver' => 'scoped',  'read-only' => true.

        //* Testing:
        // https://laravel.com/docs/12.x/filesystem#testing

        //* Making custom filesystem:
        // We can use any adapter like dropbox or google drive for our file system.
        // https://laravel.com/docs/12.x/filesystem#custom-filesystems

        //* Packages:
        // intervention/image, spatie media library.
    }

    public function mongoRedis(){
        //* MongoDB:
        // MongoDB is one of the most popular NoSQL document-oriented database, used for its high write load (useful for analytics or IoT) and high availability.
        // Can easily do horizontal scaling.
        // MongoDB database is a document described in BSON, a binary representation of the data.
        // Supports documents, arrys, embedded documents and binary data types.
        // To use, install at first: composer require mongodb/laravel-mongodb
        // Install driver if already not present: pecl install mongodb
        // Set MONGODB_URI and MONGODB_DATABASE in env file.
        // For hosting MongoDB in the cloud, consider using MongoDB Atlas.
        // Configure in database configuration file.
        // MongoDB Documentation: https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/quick-start/

        //* Redis: Redis is an open source, advanced key-value store.
        // To use, install PhpRedis extension or predis/predis package or just use sail.
        // Configure in database configuration file.
        // If we use cluster, we have to configure cluster options.
        // Redis clustering is a great default option, as it gracefully handles failover.
        // All configurations as per documentation when need.
        // To interact with Redis, we use Redis Facade:
        Redis::set('name', 'Taylor');
        Redis::get('user:profile:'.$id);
        Redis::lrange('names', 5, 10);
        Redis::command('lrange', ['name', 5, 10]); // Command to the server.
        Redis::connection('connection-name'); // If we have multiple connection.
        Redis::transaction(function (Redis $redis){$redis->incr('user_visits', 1);});
        // If we need atomic operation and also want to interact with and inspect Redis key values, rather than using transaction,
        // We can use lua scripts using eval method since Redis is written in Lua programming language.
        Redis::eval(<<<'LUA'
                local counter = redis.call("incr", KEYS[1])
                if counter > 5 then
                    redis.call("incr", KEYS[2])
                end
                return counter
            LUA, 2, 'first-counter', 'second counter'
        );
        Redis::pipeline(function(Redis $r){
            for ($i = 0; $i < 1000; $i++) {
                $pipe->set("key:$i", $i);
            } // Execute dozens of Redis commands.
        });
        
        //* Redis PUB SUB:
        // We can publish messages to the channel fom another application, even from another programming language to communicae between application and processes.
        // Step 1: Set up a channel listener using subscriber method in App\Console\Commands
        // Step 2: Can publish message from controller using Redis::publish()
        // Catch all messages on all channels: Redis::pubscribe(['*'], function(){})
    }
}