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

// Check if associative array: Arr::isAssoc()
// Check if not assoc: Arr::isList()

// Retrieves a value from a deeply nested array using dot notation:
$fetch = Arr::get($array, 'products.desk.price');
$discount = Arr::get($array, 'products.desk.discount', 0); // If not found, return 0.

// Adds a key value pair into the array: add($array, $key, $value)
$addData = Arr::add($array, 'price', 100); // [....., 'price' => 100]

// Remove the given key value pairs from array for the given key or keys.
$remove = Arr::except($array, ['orders']);

// Remove a key value pair from a deeply nested array using dot notation:
$remove = Arr::forget($array, 'products.desk.price');

// Divide an array into key array and value array:
[$keys, $values] = Arr::divide($array);

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