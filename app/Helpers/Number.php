<?php
use Illuminate\Support\Number;

// Format the Number:
Number::format(100000); // 100,000
// Other Parameters: precision, maxPrecision, locale.

// Removes any trailing zero digits after the decimal point
Number::trim(12.0); // 12
Number::trim(12.30); // 12.3

// String into Int
Number::parseInt('10.123') // (int) 10
Number::parseInt('10,123', locale: 'fr'); // (int) 10

// String into float:
Number::parseFloat('10', locale: 'fr');

// Spell the number:
Number::spell(102); // one hundred and two
Number::spell(88, locale: 'fr'); // quatre-vingt-huit
Number::spell(10, after: 10); // 10
Number::spell(11, after: 10); // eleven
Number::spell(5, until: 10); // five
Number::spell(10, until: 10); // 10

// Return clammed number:
Number::clamp(105, min: 10, max: 100); // 100
Number::clamp(5, min: 10, max: 100); // 10

// Return currency representation:
Number::currency(1000); // $1,000.00
Number::currency(1000, in: 'EUR', locale: 'de', precision: 0); // 1.000 €
Number::defaultCurrency(); // USD
Number::defaultLocale(); // en

// Return Human Readable Format:
Number::abbreviate(489939); // 490k
Number::abbreviate(1230000, precision: 2) // 1.23M
umber::forHumans(1000); // 1 thousand
Number::forHumans(489939); // 490 thousand
Number::forHumans(1230000, precision: 2); // 1.23 million

// 1st 2nd 3rd type return:
Number::ordinal(2); // 2nd
Number::spellOrdinal(1); // second

// Return percentage string:
 Number::percentage(10); // 10%
Number::percentage(10, precision: 2, locale: 'de'); // 10,00%

// Large number into smaller pairs of range array, useful for pagination and batching tasks.
Number::pairs(25, 10); // [[0, 9], [10, 19], [20, 25]]
Number::pairs(25, 10, offset: 0); // [[0, 10], [10, 20], [20, 25]]

// File size of the number as a string:
Number::fileSize(1024); // 1 KB
Number::fileSize(1024, precision: 2);

// Executes the given closure using the specified locale and then restores the original locale after the callback has executed
$number = Number::withLocale('de', function () {
    return Number::format(1500);
}); // Same goes for withCurrency()

// UseCurrency in AppServiceProvider's boot method
// Sets the default number currency globally, which affects how the currency is formatted by subsequent invocations to the Number class's methods