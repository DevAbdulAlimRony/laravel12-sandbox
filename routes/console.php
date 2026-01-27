<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

// console.php stores closure based commands
// Though they are not any http route, but entry point.

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//* Model Pruning:
Schedule::command('model:prune')->daily(); // By default, it will search prunable model into App\Models
// If model in different directory:
Schedule::command('model:prune', ['--model' => [Address::class, Flight::class],])->daily();
// Can use --except if want any prunable model to exclude.   
// Test: php artisan model:prune --pretend.