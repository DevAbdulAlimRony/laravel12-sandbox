<?php

namespace App\Http\Controllers;
use Illuminate\Support\Collection;

class CollectionController {
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