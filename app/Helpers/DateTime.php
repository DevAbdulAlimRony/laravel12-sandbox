<?php
use Illuminate\Support\Carbon;

$now = now(); // Carbon instance for the current time. Today's date with current time.
$today = today(); // Only current date, not time.
$now = Carbon::now();

now()->plus(minutes: 5);
now()->plus(hours: 8);
now()->plus(weeks: 4);

now()->minus(minutes: 5);
now()->minus(hours: 8);
now()->minus(weeks: 4);

// DateIntervals
use Illuminate\Support\Facades\Cache;
use function Illuminate\Support\{minutes};

Cache::put('metrics', $metrics, minutes(10));
