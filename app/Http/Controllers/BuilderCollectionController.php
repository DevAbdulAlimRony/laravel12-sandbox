<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;

class BuilderCollectionController {
    public function index(){
        Flight::all(); // all models.
        $flight = Flight::where('status', 1)->get(); // We can chain any method of query builder.

        //* Each Eloquent model serves as a query builder.
        // So we can add or chain additional constraints to queries and invoke the get method.

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
        // for new, to save database, we have to use save(). create automatically save into databse.

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
        $flight = Flight::create(['name' => 'London to Paris']); // or just call $request->all()
        // If already have model instance, can use fill:
        $flight->fill(['name' => 'London to Paris']);
        // For json columns,  each column's mass assignable key must be specified in fillable.
        // If attribute is not included in $fillable, it will be silently discarded which is good for production.
        // If we wanna show error in local- invoke exception in AppServiceProvider's boot method.
        
        //* Using Insert:
        // Unlike create(), it bypasses the Eloquent Model layer and talks directly to the database.
        // This makes it incredibly fast, but it means you lose "magic" features like automatic timestamps and observers.
        DB::table('flights')->insert([
            'flight_number' => 'KLM123',
            'destination' => 'Amsterdam',
            'created_at' => now(), // Manually required
            'updated_at' => now(), // Manually required
        ]); // If duplicate entry, will throw error. If dont want error, use: insertOrIgnore()
        //* Mass Bulk Insert: Can pass multiple rows and it will perform using single query, thats the real use case. Exmp: Importing from excel.
        // Insert and immediately get the primary Key:
        $id = DB::table('orders')->insertGetId([
            'total' => 99.99,
            'user_id' => 1
        ]);
        // If 10,000 rows, can get error- use chunk/lazy and insert combined.

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

        // Update an array key casted from json column:
        $user->update(['options->key' => 'value']);

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

    public function builderMethods(){

    }

    //* Can apply only after getting the collection:
    public function collectionMethods(){
        // Collection class provides a fluent, convenient wrapper for working with arrays of data.
        // So that we can chain up other methods of collection instance.
        // In general, collections are immutable, meaning every Collection method returns an entirely new Collection instance.
    
        $collection  = collect([1, 2, 3]); // Return a Collection instance.
        // The results of Eloquent queries are always returned as Collection instances.

        // Collections are macroable, we can add additional custom methods of our own. 
        // We should declare a collection macro in a service provider's boot, see AppServiceProvider.

        //* Available Methods:
        // All of these methods may be chained to fluently manipulate the underlying array.
        // Almost every method returns a new Collection instance, allowing us to preserve the original copy of the collection when necessary.
        $collection->all(); // Returns the underlying array represented by the collection: [1, 2, 3]
        collect([['foo' => 10], ['foo' => 10]])->avg('foo'); // Average for the given key. can use average() also.
        $collection->after(3); // 4. Item after a item, if not return null.
        $collection->after(3, strict: true);
        $collection->after(function(int $item, int $key){return $item >5}); // Search items greater than 5 and then return the next item of them.
        // Same goes for before.
    }
}