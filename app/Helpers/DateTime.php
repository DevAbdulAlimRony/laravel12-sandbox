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

// For migration, if we want to chnage the timestamp format in mysql:
class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
} // Then edit the app service provider's boot method: DB::connection()->setQueryGrammar(new \App\Database\Query\Grammars\MySqlGrammar);


//* Carbon Package:
// The Carbon class is inherited from the PHP DateTime class.
// You need to import the namespace to use Carbon without having to provide its fully qualified name each time.
// use Carbon\Carbon; or Laravel's Carbon Support.
Carbon::now();
CarbonImmutable::now();
// We can localize using  \Carbon\Translator etc.

//* Getters:
$dt = Carbon::parse('2012-10-5 23:26:11.123789');
$dt->year;
// month, day, hour, minutes, second, micro, dayOfWeek, dayOfWeekIso
// englishDayOfWeek- Friday, shortEnglishDayOfWeek, $dt->locale('de')->dayName..shortDayName..minDayName..
// englishMonth, shortEnglihMonth, locale('de')->monthName..shortMonthName
// dayOfYear, weekNumberInMonth, weekOfMonth, weekOfYear, daysInMonth, timestamp,
// getTimestamp(), getTimestampMs(), valueOf(), getPreciseTimestamp(), quarter
Carbon::createFromDate(1975, 5, 21)->age // calculated vs now in the same tz

