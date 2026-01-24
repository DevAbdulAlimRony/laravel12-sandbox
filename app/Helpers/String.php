<?php
// Laravel includes a variety of functions for manipulating string values.

echo e('<html>foo</html>'); //  htmlspecialchars function with the double_encode option set to true
__('Welcom'); // Return translated string or given value if translation not exist
class_basename('Foo\Bar\Baz'); // Return the Class name: // Baz
preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], 'between :start and :end'); // Output: between 8:30 and 9:00

use Illuminate\Support\Str;

Str::is('foo*', 'foobar'); // True: If a given string matches a given pattern
Str::is('*.jpg', 'photo.JPG', ignoreCase: true);

Str::isAscii('Taylor'); // True
Str::isJson('[1,2,3]'); // true
Str::isJson('{"first": "John", "last": "Doe"}'); // true
Str::isJson('{first: "John", last: "Doe"}'); // false
Str::isUrl('http://example.com'); // true: Check if a valid URL
Str::isUrl('http://example.com', ['http', 'https']); // Can define protocol.
Str::isUlid('01gd6r360bp37zj17nxb55yv40'); // true: if the string is a valid ULID
Str::isUuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de'); // true: if the string is a valid UUID

Str::contains('This is my name', 'my'); // If the given string contains the given value. Output: true. By default case sensitive.
Str::contains('This is my name', ['my', 'foo']); // If contains anything of the array.
Str::contains('This is my name', 'MY', ignoreCase: true); // Case sensitivity disabled.
Str::containsAll('This is my name', ['my', 'name']); // If contains all in the array. Can pass ignoreCase.
Str::doesntContain('This is name', 'my'); // true. Also can pass array or ignoreCase.

Str::start('this/string', '/'); // Add / at first if already not at first
Str::startsWith('This is my name', 'This'); // Can take array also as 2nd argument.
Str::endsWith('This is my name', 'name'); // true, can pass array
Str::doesntStartWith('This is my name', ['What', 'That', 'There']);
Str::doesntEndWith('This is my name', 'dog'); // true

Str::repeat('a', 5); // aaaaa
Str::charAt('This', 0); // Returns the character at the specified index. - T
Str::position('Hello, World!', 'Hello'); // 0
Str::position('Hello, World!', 'W'); // 7
Str::ascii('û'); // Ascii Value: 'u'
Str::singular('cars'); // car
Str::plural('car'); // Cars. Make singluar to plural.
Str::plural('child'); // children.
Str::plural('child', 1); // It will return singular form, if 2 given then plural
Str::plural('car', 1000, prependCount: true); // 1,000 cars
Str::pluralStudly('VerifiedHuman'); // VerifiedHumans. formatted in studly caps case to its plural form. Can provide 1, 2.
Str::random(40); // Generate 40 length random string.
Str::createRandomStringsNormally();
Str::createRandomStringsUsing(function () {
    return 'fake-random-string';
});

Str::length('Laravel'); // 7
Str::take('Build something amazing!', 5); // Return first 5 character
Str::substr('The Laravel Framework', 4, 7); // Extract from postion 4 to length 7.
Str::substrCount('If you like ice cream, you will like snow cones.', 'like'); // 2
Str::substrReplace('1300', ':', 2); // Replace by : from position 2- 13::
Str::limit('The quick brown fox jumps over the lazy dog', 20); // Truncate the text: // The quick brown fox...
Str::limit('The quick brown fox jumps over the lazy dog', 20, ' (...)'); // The quick brown fox (...)
Str::limit('The quick brown fox', 12, preserveWords: true); // The quick...
Str::mask('taylor@example.com', '*', 3); // tay***************
Str::mask('taylor@example.com', '*', -15, 3); // tay***@example.com
(string) Str::ordderedUuid(); // Generates a timestamp first UUID that efficiend for indexed column.
Str::padBoth('James', 10, '_'); // Add _ both side as log as char will 10, '__James___' : If last argument not given then will take just space.
// padLeft, padRight
Str::slug('Laravel 5 Framework', '-'); // laravel-5-framework

(string) Str::ulid(); // Generates a ULID, which is a compact, time-ordered unique identifier
// Using Carbon Instance: Carbon::createFromId((string) Str::ulid());
use Symfony\Component\Uid\Ulid;
Str::createUlidsUsing(function () {
    return new Ulid('01HRDBNHHCKNW2AK4Z29SN82T9');
});
Str::createUlidsNormally();

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