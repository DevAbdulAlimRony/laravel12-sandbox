<?php

use Illuminate\Support\Arr;

$array = [
    'products' => [
        'desk' => [
            'price' => 200,
            'description' => 'A mahogany desk.',
            'stock' => 5,
            'tags' => ['furniture', 'office'],
        ],
        'chair' => [
            'price' => 100,
            'description' => 'An ergonomic chair.',
            'stock' => 0,
            'tags' => ['furniture', 'ergonomic'],
        ],
        'lamp' => [
            'price' => 45,
            'stock' => 15,
            'tags' => ['lighting'],
        ],
    ],
    'meta' => [
        'location' => 'Warehouse A',
        'active' => true,
    ],
    'orders' => [101, 102, 103]
];

// Array is a primitive type of data, If we wanna apply any eloquent collection method, make it a collection:
// collect($array)->sort()

// Make a given value into an array:
$wrapIntoArray = Arr::wrap('Abdul Alim'); // [Abdul Alim]
// If already array, no modification
// If null, then return empty array

// Check if an array or instance of array accessible:
$isAccessible = Arr::accessible($array); // true
$isAccessible = Arr::accessible(new Collection); // true
$isAccessible = Arr::accessible('abc'); // false
$isAccessible = Arr::accessible(new stdClass); // false

// Check if given key exits
$ifKeyExists = Arr::exists($array, 'name'); // false

// Check if any given items exists of dot notation
$contains = Arr::has($array, 'products.desk.price');
$contains = Arr::has($array, ['product.price', 'product.discount']);

// Check if all given items exists of dot notation: Arr::hasAll()
// Check if Any: hasAny()
// Difference between has and hasAll(): No functional difference. Added to get flexibility, nothing else. 

// Check if a nested data is an array, if not throw InvalidArgumentException:
$isArray = Arr::array($array, 'meta'); // true

// Check if a nested value is a boolean, if not throw InvalidArgumentException:
$isBoolean = Arr::boolean($array, 'active'); // true
$isBoolean = Arr::boolean($array, 'Inactive'); // InvaidArgumentException.

// Check if a nested value is a float, if not then throw InvalidArgumentException:
$isFloat = Arr::float($array, 'products.desk.price');

// Check if a nested value is a integer, if not then throw InvalidArgumentException:
$isInteger = Arr::integer($array, 'products.desk.price');

// Arr::string()

// Check if associative array: Arr::isAssoc()
// Check if not assoc: Arr::isList()

// Returns the first element, if empty array return false:
$first = head($array);

// Returns the last element, if empty then false:
$last = last($array);
   
// Retrieves a value from a deeply nested array using dot notation:
$fetch = Arr::get($array, 'products.desk.price');
$discount = Arr::get($array, 'products.desk.discount', 0); // If not found, return 0.

// Retrives a value from nested array or object using dot notation:
$discount2 = data_get($array, 'products.desk.discount');
// Can give default value.
// Also accepts wildcards using asterisks: data_get($data, '*.name'), all names
// Retrieve the first or last items using placeholder: data_get($flight, 'segments.{first}.arrival');

// Sorts an array by its values:
$sorted = Arr::sort($array);

// Sort by a closure:
$sortedByOrders = Arr::sort($array, function(array $value){
    return $value['name'];
});

// Sort By Desc:
$sortedDesc = Arr::sortDesc($array); // Also can be given a closure.

// sortRecursive(), sortRecursiveDesc()

// Return a random value:
$random = Arr::random($array);
$random = Arr::random($array, 3); // Return three random items.

// Randomly shuffle the items in the array:
$shuffle = Arr::shuffle($array);

// Only return specific key value pair:
$getMetaOrders = Arr::only($array, ['meta', 'orders']);
// Return only values: onlyValues()

// Select an array of value from an array
$getSelectedData = Arr::select($array, ['products', 'orders']);

// Return a new array with a specified number of items:
$nums   = [0, 1, 2, 3, 4, 5];
$chunk1 = Arr::take($nums, 3); // [0, 1, 2]
$chunk2 = Arr::take($nums, -2); // [4, 5]

// Make css classes:
$isActive = false;
$hasError = true;
$css = ['p-4', 'font-bold' => $isActive, 'bg-red' => $hasError];
$classes = Arr::toCssClasses($css);

// $array = ['background-color: blue', 'color: blue' => $hasColor];
// toCssStyles($array)

// Divide an array into key array and value array:
[$keys, $values] = Arr::divide($array);

// Adds a key value pair into the array at last: add($array, $key, $value)
$addData = Arr::add($array, 'price', 100); // [....., 'price' => 100]

// Add a key value only if not present in the array or object:
$filled = data_fill($array, 'products.desk.discount', 10); // discount added into desk.
data_fill($data, 'products.*.price', 200); // Tried to add price of all products using  asterisks as wildcards, but already they have price, so dont modify. 

// Push an item onto the beggining of an array: prepend(array, value, key)
$pushData = Arr::prepend($array, 200, 'price');
$pushData = Arr::prepend($array, 'product.'); // All keys will get product. - product.product, product.chair...

// Sets a value within a deeply nested array using "dot" notation
$set = Arr::set($array, 'products.desk.price', 200);

// Sets a value within a nested array or object using "dot" notation
$set = data_set($array, 'products.desk.price');
// Can use default value, asterisk wildcard.
// By default, any existing values are overwritten.
// If we dont want to ovewrite existing: data_set($data, 'products.desk.price', 200, overwrite: false);

// Push an item using dot notation if key does not exist
$emptyArray = [];
Arr::push($emptyArray, 'office.furniture', 'Desk'); // ['office' => ['furniture' => ['Desk']]]

// Remove the key value pairs from array for the given key or keys.
$remove = Arr::except($array, ['orders']);

// Remove a key value pair from a deeply nested array using dot notation:
$remove = Arr::forget($array, 'products.desk.price');
// data_forget(): Can use wildcard asterisk also.

// Returns and removes a key / value pair from an array
$pull = Arr::pull($array, 'orders');
$pull = Arr::pull($array, 'orders', 'default-value'); // If key doesn't exist, default value will be shown.

// Removes items from an array using the given closure
$stockedProducts = Arr::reject($array['products'], function ($value) {
    return $value['stock'] <= 0;
});

//  Keys the array by the given key.
$prod = [
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
];
$keyed = Arr::keyBy($array, 'product_id');
/*
    [
        'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
        'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]
*/

// Retrive all of the values for the given key:
$furnitures = Arr::pluck($array, 'products.price'); // Output: [200, 100, 45]
$furnitures = Arr::pluck($array, 'products.price', 'product.description'); // Description will be keyed: ['A mahogany desk.' => 200, ...]

// Collapse an array of arrays or instance of Collections into a single array
$product = [
    'info' => ['name' => 'Desk', 'color' => 'Brown'],
    'pricing' => ['price' => 200, 'tax' => 20]
];
$mergedData = Arr::collapse($product);
/*
    Result:
    [
        'name' => 'Desk',
        'color' => 'Brown',
        'price' => 200,
        'tax' => 20
    ]
*/

// Flattens a multi dimensional array into a single level array:
$dotted = Arr::dot($product); 
/*
    Result:
    [
        'info.name' => 'Desk',
        'info.color' => 'Brown',
        'pricing.price' => 200,
        'pricing.tax' => 20
    ]
*/

// Undot a single dimensional array that uses dots into a multi dimensional array:
$user = [
    'user.name' => 'Kevin Malone',
    'user.occupation' => 'Accountant',
];
$undotted = Arr::undot($user);

// Converts the arry into query string:
$query = Arr::query($product); // info[name]=Desk&info[color]=Brown&pricing[price]=200&pricing[tax]=20

// Flattens a multi-dimensional array into a single level array:
$flattened = Arr::flatten($product); 
/*
    Result: ['Desk', 'Brown', 200, 20]
*/

// Join array elements using anything
$array2 = ['Tailwind', 'Alpine', 'Laravel', 'Livewire'];
$joined = Arr::join($array, ', '); // Tailwind, Alpine, Laravel, Livewire
$joined = Arr::join($array, ', ', ', and '); // Tailwind, Alpine, Laravel, and Livewire

// Cross join the given arrays, returning all possible permutations: Arr::crossjoins(...$array)
$sizes = ['Small', 'Large'];
$colors = ['Red', 'Blue'];
$variations = Arr::crossJoin($sizes, $colors); // Result: [['Small', 'Red'], ['Small', 'Blue'], ['Large', 'Red'], ['Large', 'Blue']]

// Verifies that all elements pass a given truth test: every($array, callback)
$allInStock = Arr::every($array['products'], function ($value, $key) {
    return $value['stock'] > 0;
}); // Output: false (because 'chair' has stock 0)
$hasPrice = Arr::every($array['products'], fn($product) => $product['price'] > 0); // Output: true

// Check if at leat one element pass the test- Check if any product is out of stock
$hasOutOfStock = Arr::some($array['products'], function ($value) {
    return $value['stock'] <= 0;
});

// Retrives a single value based on test, if multiple value return MultipleItemsFoundException:
$returnOnePositivePrice = Arr::sole($array['products'], fn($product) => $product['price'] > 0);

// If we want deconstruct the test passes elements and failed elements into separate arrys deconstructing:
[$inStock, $stockOut] = Arr::partition($array['products'], function ($value, $key) {
    return $value['stock'] > 0;
});

// Filters an array using a given closure test:
$filtered = Arr::where($array['products'], function ($value, $key) {
    return $value['price'] > 50;
});

// Removes all null values:
$nulled = [0, null];
$notNulled = Arr::whereNotNull($nulled);

// Verifies that first element pass a given truth test: Arr::first($array, function(){})
// Arr::first($array, function(){}, 'Failed'):  if no value passes the truth test, then return 'Failed'.
// Same goes for: Arr::last()

// Modify the array keys or/and values:
// The array value is replaced by the value returned by the callback
$mappedOrders = Arr::map($array['orders'], fn($value, $key) => "ORD-" . $value);
/*
    Output:
    [
        0 => "ORD-101",
        1 => "ORD-102",
        2 => "ORD-103"
    ]
*/

// Creating a spreadable structure: [[ID, Priority], [ID, Priority]]
$orderList = [[101, 'High'], [102, 'Low'], [103, 'Medium']];
$formattedOrders = Arr::mapSpread($orderList, fn($id, $priority) => "Order #$id is $priority priority.");
/*
    Output:
    [
        "Order #101 is High priority.",
        "Order #102 is Low priority.",
        "Order #103 is Medium priority."
    ]
*/

// Transform your products into a new format where you define both the key and the value
$stockStatus = Arr::mapWithKeys($array['products'], function ($details, $name) {
    return [strtoupper($name) => $details['stock'] > 0 ? 'In Stock' : 'Out of Stock'];
});

/*
    Output:
    [
        'DESK'  => 'In Stock',
        'CHAIR' => 'Out of Stock',
        'LAMP'  => 'In Stock',
    ]
*/