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

//* Constants:
Carbon::SUNDAY; // 0
Carbon::MONDAY; // 1

var_dump(Carbon::YEARS_PER_CENTURY); // int(100)
var_dump(Carbon::YEARS_PER_DECADE); // int(10)
var_dump(Carbon::MONTHS_PER_YEAR); // int(12)
var_dump(Carbon::WEEKS_PER_YEAR); // int(52)
var_dump(Carbon::DAYS_PER_WEEK); // int(7)
var_dump(Carbon::HOURS_PER_DAY); // int(24)
var_dump(Carbon::MINUTES_PER_HOUR); // int(60)
var_dump(Carbon::SECONDS_PER_MINUTE); // int(60)

//* Getters:
$dt = Carbon::parse('2012-10-5 23:26:11.123789');
$dt->year;
// month, day, hour, minutes, second, micro, dayOfWeek, dayOfWeekIso
// englishDayOfWeek- Friday, shortEnglishDayOfWeek, $dt->locale('de')->dayName..shortDayName..minDayName..
// englishMonth, shortEnglihMonth, locale('de')->monthName..shortMonthName
// dayOfYear, weekNumberInMonth, weekOfMonth, weekOfYear, daysInMonth, timestamp,
// getTimestamp(), getTimestampMs(), valueOf(), getPreciseTimestamp(), quarter
Carbon::createFromDate(1975, 5, 21)->age // calculated vs now in the same tz
// createFromTimestamp(), :createFromTimestampUTC()

// Indicates if day light savings time is on
var_dump(Carbon::createFromDate(2012, 1, 1)->dst); // bool(false)
var_dump(Carbon::createFromDate(2012, 9, 1)->dst); // bool(false)
var_dump(Carbon::createFromDate(2012, 9, 1)->isDST()); // bool(false)

// Indicates if the instance is in the same timezone as the local timezone
var_dump(Carbon::now()->local);             // bool(true)
var_dump(Carbon::now('America/Vancouver')->local); // bool(false)
var_dump(Carbon::now()->isLocal());         // bool(true)
var_dump(Carbon::now('America/Vancouver')->isLocal()); // bool(false)
var_dump(Carbon::now()->isUtc());           // bool(true)
var_dump(Carbon::now('America/Vancouver')->isUtc()); // bool(false)
var_dump(Carbon::now()->utc);
var_dump(Carbon::parse('2018-10-01', 'Europe/London')->utc);

// Gets the DateTimeZone instance
echo get_class(Carbon::now()->timezone);   // Carbon\CarbonTimeZone
echo get_class(Carbon::now()->tz);         // Carbon\CarbonTimeZone
Carbon::now()->timezoneName;               // UTC
Carbon::now()->tzName;                     // UTC

// You can get any property dynamically too:
$unit = 'second';
Carbon::now()->get($unit); // int(56)
Carbon::now()->$unit;
Carbon::singularUnit('seconds');
var_dump(Carbon::pluralUnit('century'));    // string(9) "centuries"
var_dump(Carbon::pluralUnit('millennium')); // string(9) "millennia"

//* Setters:
$dt->year = 1975;
$dt->month = 13;
$dt->month = 5;
$dt->day = 21;
$dt->hour = 22;
$dt->minute = 32;
$dt->second = 5;
$dt->setYear(2001);
$dt->timestamp = 169957925;
$dt->setTimestamp(169957925);
$dt->timestamp(169957925);
$dt->tz = new \DateTimeZone('Europe/London');
$dt->tz = 'Europe/London';
$dt->year(2002);
$dt->set('year', 2003);
$dt->dayOfYear(35)->format('Y-m-d');    // 2003-02-04

//* Week Methods:
// Week methods follow the rules of the current locale (for example with en_US, the default locale, the first day of the week is Sunday, and the first week of the year is the one that contains January 1st). 
$en = CarbonImmutable::now()->locale('en_US');
$ar = CarbonImmutable::now()->locale('ar');
var_dump($en->firstWeekDay); // int(0)
var_dump($en->lastWeekDay); // int(6)
var_dump($en->startOfWeek()->format('Y-m-d H:i')); // string(16) "2026-03-08 00:00"
var_dump($en->endOfWeek()->format('Y-m-d H:i')); // string(16) "2026-03-14 23:59"
$start = $en->startOfWeek(Carbon::TUESDAY);
$end = $en->endOfWeek(Carbon::MONDAY);

$en = CarbonImmutable::parse('2015-02-05'); // use en_US as default locale
var_dump($en->weeksInYear()); // int(52)
var_dump($en->isoWeeksInYear()); // int(53)

//* Fluent Setters:
$dt->year(1975)->month(5)->day(21)->hour(22)->minute(32)->second(5)->toDateTimeString();
$dt->setDate(1975, 5, 21)->setTime(22, 32, 5)->toDateTimeString();
$dt->setDate(1975, 5, 21)->setTimeFromTimeString('22:32:05')->toDateTimeString();
$dt->setDateTime(1975, 5, 21, 22, 32, 5)->toDateTimeString();
// All allow microsecond as optional argument, )->microsecond(123456).
$dt->timezone('Europe/London')->tz('America/Toronto')->setTimezone('America/Vancouver');

//* Manipulations:
// Add and Subtract:
$dt->toDateTimeString(); // 2012-01-31 00:00:00
$dt->addDays(29);
$dt->addDay();
$dt->subDay(); 
$dt->subDays(29);     
// Same goes for months, years, quarters, centuries, weekdays, weeks, hours, minutes, seconds, milliseconds, microseconds.
// Can pass negative values also.
$dt->add(61, 'seconds');  
$dt->sub('1 day');
$dt->subtract(new \DateInterval('PT1H'));

// Common Formats:
Carbon::createFromFormat('Y-m-d H:i:s.u', '2019-02-01 03:45:27.612584');
$dt->toAtomString(); // 2019-02-01T03:45:27+00:00
$dt->toCookieString(); // Fri, 01 Feb 2019 03:45:27 +0000
$dt->toIso8601String(); // 2019
$dt->format(\DateTime::ISO8601); // 2019-02-01T03:45:27+0000
$dt->toJSON(); // 2019-02-01T03:45:27.612584Z
// toIso8601ZuluString(), toRfc3339ZuluString() etc also available.

// Comparison:
$first = Carbon::create(2012, 9, 5, 23, 26, 11);
$second = Carbon::create(2012, 9, 5, 20, 26, 11, 'America/Vancouver');
var_dump($first->equalTo($second));  // bool(false)
var_dump($first->notEqualTo($second));
// greaterThan(), greaterThanOrEqualTo(), lessThan(), lessThanOrEqualTo() also available.
// All have shorter aliases: eq(), ne(), gt(), gte(), lt(), lte().
var_dump($first != $second);
var_dump($first->isAfter($second));  // bool(false). isBefore().
var_dump($first > $second); // bool(false)
var_dump($first >= $second); // bool(false)
Carbon::create(2012, 9, 5, 3)->between($first, $second); // bool(true).
// isBetween() also has an alias: betweenIncluded() and betweenExcluded() for including or excluding the boundaries respectively.

$dt1 = Carbon::createMidnightDate(2012, 1, 1);
$dt2 = Carbon::createMidnightDate(2014, 1, 30);
echo $dt1->min($dt2);                                                                                
echo $dt1->minimum($dt2);          
// max(), maximum() also available. 
$dt1->closest($dt2, $dt3);
$dt1->farthest($dt2, $dt3);
$dt->isSameAs('w', $dt2);

$dt->isFuture();
$dt->isNowOrFuture();
$dt->isPast();
$dt->isNowOrPast();
$dt->isSameYear($dt2);
$dt->isCurrentYear();
$dt->isNextYear();
$dt->isLastYear();
$dt->isLeapYear();
// Same goes for Quarter, Month, Weekday, Day, Hour, Minute, Second.
$dt->isWeekday();
$dt->isWeekend();
$dt->isMonday();
$dt->isTuesday();
$dt->isWednesday();
$dt->isDayOfWeek(Carbon::SATURDAY); 
$dt->isLastOfMonth();

$dt->is('Sunday');
$dt->is('June');
$dt->is('2019');
$dt->is('12:23');
$dt->is('2 June 2019');
$dt->is('06-02');

$dt->isSameDay($dt2); // Same day of same month of same year
$dt->isCurrentDay();
$dt->isYesterday();
$dt->isToday();
$dt->isTomorrow();
$dt->isNextWeek();
$dt->isLastWeek();

$dt->isStartOfDay(); // check if hour is 00:00:00
$dt->isMidnight(); // check if hour is 00:00:00 (isStartOfDay alias)
$dt->isEndOfDay(); // check if hour is 23:59:59
$dt->isMidday(); // check if hour is 12:00:00 (or other midday hour set with Carbon::setMidDayAt())

// Conversion:
// toArray(), toObject() toDateString(), toFormattedDateString(), toTimeString(), toDateTimeString(), toDayDateTimeString()
// toAtomString(), toCookieString(), toIso8601String(), toRfc850String(), toRfc1036String(), toRfc1123String(), toRfc2822String()
// toRfc3339String(), toRfc7231String(), toW3cString() etc.
// toDate(), toDateTimeImmutable()
// toDateString(), toTimeString().

// Difference for Humans: Like 1 Month ago:
Carbon::now()->subDays(5)->diffForHumans(); 
$dt->diffForHumans($dt->copy()->addMonth());
// Full documentation: https://carbon.nesbot.com/guide/date-time-manipulation/difference-for-humans.html

// Difference:
$dtOttawa = Carbon::createMidnightDate(2000, 1, 1, 'America/Toronto');
$dtVancouver = Carbon::createMidnightDate(2000, 1, 1, 'America/Vancouver');
echo $dtOttawa->diffInHours($dtVancouver);                                             
echo $dtVancouver->diffInHours($dtOttawa);
// diffIndays(), diffInWeeks(), diffInMonths(), diffInYears(), diffInSeconds(), diffInMinutes() etc also available.

//* Macro:
Carbon::macro('diffFromYear', static function ($year, $absolute = false, $short = false, $parts = 1) {
	return self::this()->diffForHumans(Carbon::create($year, 1, 1, 0, 0, 0), $absolute, $short, $parts);
});
// Macros can also be grouped in classes and be applied with mixin()

//* Modifiers:
$dt->startOfDay()->toDateTimeString(); // 2012-01-31 00:00:00
$dt->endOfDay()->toDateTimeString(); // 2012-01-31 23:59:59
$dt->startOfMonth()->toDateTimeString(); // 2012-01-01 00:00:00
$dt->endOfMonth()->toDateTimeString(); // 2012-01-31 23:59:59
$dt->startOfYear()->toDateTimeString(); // 2012-01-01 00:00:00
$dt->endOfYear()->toDateTimeString(); // 2012-12-31 23:59 etc.

//* Serailization:
echo serialize($dt);//  O:13:"Carbon\Carbon":4:{s:4:"date";s:26:"2012-12-25 20:30:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Moscow";s:18:"dumpDateProperties";a:2:{s:4:"date";s:26:"2012-12-25 20:30:00.000000";s:8:"timezone";s:13:"Europe/Moscow";}}
// same as:
echo $dt->serialize(); 

//* CarbonInterval:
echo CarbonInterval::createFromFormat('H:i:s', '10:20:00');    // 10 hours 20 minuts
CarbonInterval::year(); // 1 year
CarbonInterval::months(3);  // 3 months
CarbonInterval::days(3)->addSeconds(32); // 3 days 32 seconds
CarbonInterval::days(23); // 3 weeks 2 days etc.

//* CarbonPeriod:
$period = new CarbonPeriod('2018-04-21', '3 days', '2018-04-27'); // 2018-04-21, 2018-04-24, 2018-04-27
$period = CarbonPeriod::since('2018-04-21')->days(3)->until('2018-04-27'); // 2018-04-21, 2018-04-24, 2018-04-27

//* CarbonTimeZone:
$tz = new CarbonTimeZone('Europe/Zurich'); // instance way
echo $tz->getName(); // string(13) "Europe/Zurich"
echo $tz->getOffset(Carbon::now()); // int(3600)
echo $tz->getTransitions(); // array of transitions for daylight saving time etc.
echo $tz->getLocation(); // array with location information like country code, latitude, longitude
echo $tz->getAbbreviations(); // array of time zone abbreviations like CET, CEST etc.                 