<?php
// To execute a calbacks based on a set of given odds.
// Useful when we need to execute code for a percentage of incoming request.

use App\Models\User;
use Carbon\CarbonInterval;

$user = User::all();
Lottery::odds(1, 20)
    ->winner(fn () => $user->won())
    ->loser(fn () => $user->lost())
    ->choose();

// Real life use case:
// Send error report for the slow queries:
use Illuminate\Support\Lottery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

DB::whenQueryingForLongerThan(
    CarbonInterval::seconds(2),
    Lottery::odds(1, 100)->winner(fn () => report('Querying > 2 seconds.')),
);
// This setup will report only 1% of queries that take longer than 2 seconds

// Show the new feature to 10% of users, allowing you to test it with a small subset of your user base.
if (Lottery::odds(1, 10)->choose()) {
    return view('new-feature');
} else {
    return view('old-feature');
}

// Error Reporting

class Handler extends ExceptionHandler
{
    public function report(Throwable $e)
    {
        if (App::environment('production')) {
            Lottery::odds(1, 100)->winner(function () use ($e) {
                parent::report($e);
            });
        } else {
            parent::report($e);
        }
    }
}
// This setup will report only 1% of errors in production, while still reporting all errors in other environments.

// When dealing with big data, you might want to process only a sample of your dataset:
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        Lottery::odds(1, 100)->winner(function () use ($user) {
            ProcessUserData::dispatch($user);
        });
    }
});

// Testing Lottery:
// Lottery will always win...
Lottery::alwaysWin();

// Lottery will always lose...
Lottery::alwaysLose();

// Lottery will win then lose, and finally return to normal behavior...
Lottery::fix([true, false]);

// Lottery will return to normal behavior...
Lottery::determineResultsNormally();