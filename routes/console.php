<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

// Rather than making custom command class, we made a closure based command directly:
// Can type-hint any dependency in the callback. purpose is the description of the command which will be shown in command list and help.
// php artisan inspire.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//* Model Pruning:
Schedule::command('model:prune')->daily(); // By default, it will search prunable model into App\Models
// If model in different directory:
Schedule::command('model:prune', ['--model' => [Address::class, Flight::class],])->daily();
// Can use --except if want any prunable model to exclude.   
// Test: php artisan model:prune --pretend.