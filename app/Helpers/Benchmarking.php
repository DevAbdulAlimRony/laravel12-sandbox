<?php
// Test the performance of certain parts of your application.
// Measure the number of milliseconds it takes for the given callbacks to complete.

use App\Models\User;
use Illuminate\Support\Benchmark;

Benchmark::dd(fn () => User::find(1));

Benchmark::dd([
    'Scenario 1' => fn () => User::count(), // 0.5 ms
    'Scenario 2' => fn () => User::all()->count(), // 20.0 ms
]);

// By default the callback will be executed once.

// To invoke a callback more than once, you may specify the number of iterations that the callback should be invoked as the second argument to the method.
// When executing a callback more than once, the Benchmark class will return the average number of milliseconds it took to execute the callback across all iterations:
Benchmark::dd(fn () => User::count(), iterations: 10); // 0.5 ms

// Return both value and execution time:
[$count, $duration] = Benchmark::value(fn () => User::count());