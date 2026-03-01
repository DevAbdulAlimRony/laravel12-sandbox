<?php
// Laravel includes a variety of functions for manipulating string values.

echo e('<html>foo</html>'); //  htmlspecialchars function with the double_encode option set to true
__('Welcom'); // Return translated string or given value if translation not exist
class_basename('Foo\Bar\Baz'); // Return the Class name: // Baz
preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], 'between :start and :end'); // Output: between 8:30 and 9:00

Str::createUlidsUsing(function () {
    return new Ulid('01HRDBNHHCKNW2AK4Z29SN82T9');
});
Str::createUlidsNormally();

(string) Str::uuid(); // uuid version 4
Str::createUuidsUsing(function () {
    return Uuid::fromString('eadbfeac-5258-45c2-bab7-ccb9b5ef74f9');
});
Str::createUuidsNormally();
(string) Str::uuid7(); // uuid version 7
(string) Str::uuid7(time: now());

Str::match('/bar/', 'foo bar'); // 'bar': Return matched thing by regular expression.
Str::matchAll('/bar/', 'bar foo bar'); // collect(['bar', 'bar'])
Str::isMatch('/foo (.*)/', 'foo bar'); // true

Str::trim(' foo bar '); // strips whitespace (or other characters) from the beginning and end of the given string. 
Str::ltrim('  foo bar  ');
Str::rtrim('  foo bar  ');
Str::squish('    laravel    framework    '); // Remove all extra space: laravel framework 
Str::lower('LARAVEL'); // laravel
Str::upper('laravel');
Str::apa('Creating A Project'); // Title case as APA guidline: 'Creating a Project'
Str::camel('foo_bar'); // 'fooBar'
Str::kebab('fooBar'); // foo-bar
Str::lcfirst('Foo Bar'); // foo Bar: with the first character lowercased
Str::snake('fooBar'); // foo_bar
Str::snake('fooBar', '-'); // foo-bar
Str::studly('foo_bar'); // Convert to studly Case: // FooBar
Str::headline('steve_jobs'); // Steve Jobs
Str::headline('EmailNotificationSent'); // Email Notification Sent
Str::title('a nice title uses the correct case'); // A Nice Title Uses The Correct Case
Str::ucwords('laravel framework'); // Laravel Framewrok
Str::ucfirst('foo bar'); // Foo bar: Make first character uppercase
Str::ucsplit('FooBar'); // [0 => 'Foo', 1 => 'Bar']

Str::before('This is my name', 'my name'); // Returns everything before the given value in a string: This is
Str::after('This is my name', 'This is'); // ' my name'
Str::afterLast('App\Http\Controllers\Controller', '\\'); // the last occurrence of the given value in a string: 'Controller'
Str::beforeLast('This is my name is', 'is'); // name

Str::between('This is my name', 'This', 'name'); // ' is my '
Str::betweenFirst('[a] bc [d]', '[', ']'); // Returns the smallest possible portion of a string between two values: a

Str::chopStart('https://laravel.com', 'https://'); // 'laravel.com': Removes the first occurrence of the given value only if the value appears at the start of the string
Str::chopEnd('app/Models/Photograph.php', '.php'); // 'app/Models/Photograph': removes the last occurrence of the given value only if the value appears at the end of the string. Can pass array as second argument.

Str::finish('this/string', '/'); // Adds a single instance of the given value to a string if it does not already end with that value
// this/string/. If already contains / at last, it wont add.
Str::remove('e', 'Peter Piper'); // Ptr Pipr. Pass false to ignore case.
Str::unwrap('-Laravel-', '-'); // Laravel
Str::unwrap('{framework: "Laravel"}', '{', '}'); // framework: "Laravel"

Str::deduplicate('The   Laravel   Framework'); // The Laravel Framework
Str::deduplicate('The---Laravel---Framework', '-'); // The-Laravel-Framework 
Str::replace('11.x', '12.x', 'Laravel 11.x'); // Replaces a given string within the string, 4th optional argument: caseSensitive: false.
Str::replaceArray('?', ['8:30', '9:00'], 'The event will take place between ? and ?');
Str::replaceFirst('the', 'a', 'the quick brown fox jumps over the lazy dog');
Str::replaceLast('the', 'a', 'the quick brown fox jumps over the lazy dog');
Str::replaceMatches(pattern: '/[^A-Za-z0-9]++/', replace: '', subject: '(+1) 501-555-1000'); // Can take closure as second argument.
// replaceStart(): first occurrence of the given value only if the value appears at the start of the string
// replaceEnd()
Str::reverse('Hello World');
Str::swap(['Tacos' => 'Burritos', 'great' => 'fantastic'], 'Tacos are great!'); // Burritos are fantastic!

// Truncate String
Str::excerpt('This is my name', 'my', ['radius' => 3]); // Output: '...is my na...' we got three dots before and after and truncate.
// By default radius is 100
Str::excerpt('This is my name', 'name', ['radius' => 3, 'omission' => '(...) ']); // '(...) my name'

Str::fromBase64('TGFyYXZlbA=='); // Laravel. Decode a base64 to a string.
Str::toBase64('Laravel'); // TGFyYXZlbA==
Str::transliterate('ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ'); // Convert into its closest ASCII representation: 'test@laravel.com'

// Str::inlineMarkdown: GitHub flavored Markdown into inline HTML. Not secured for CORS Attack, add other arguments to make seured. 

 Str::password(); // Generate a secure random password: 'EbJo2vE-AS:U,$%_gkrV4n,q~1xy/-_4'
 Str::password(12); // Length 12.

 //* Fluent String:
 // Using Str::of(), can chain up others now:
 Str::of('This is my name')->after('This is');
 // Chainable Methods: All methods above and some others.