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

Str::startsWith('This is my name', 'This');
Str::endsWith('This is my name', 'name'); // true, can pass array
Str::doesntStartWith('This is my name', ['What', 'That', 'There']);
Str::doesntEndWith('This is my name', 'dog'); // true

Str::charAt('This', 0); // Returns the character at the specified index. - T
Str::ascii('û'); // Ascii Value: 'u'

Str::length('Laravel'); // 7
Str::limit('The quick brown fox jumps over the lazy dog', 20); // Truncate the text: // The quick brown fox...
Str::limit('The quick brown fox jumps over the lazy dog', 20, ' (...)'); // The quick brown fox (...)
Str::limit('The quick brown fox', 12, preserveWords: true); // The quick...

Str::lower('LARAVEL'); // laravel
Str::apa('Creating A Project'); // Title case as APA guidline: 'Creating a Project'
Str::camel('foo_bar'); // 'fooBar'
Str::headline('steve_jobs'); // Steve Jobs
Str::headline('EmailNotificationSent'); // Email Notification Sent
Str::kebab('fooBar'); // foo-bar
Str::lcfirst('Foo Bar'); // foo Bar: with the first character lowercased

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

Str::deduplicate('The   Laravel   Framework'); // The Laravel Framework
Str::deduplicate('The---Laravel---Framework', '-'); // The-Laravel-Framework 

// Truncate String
Str::excerpt('This is my name', 'my', ['radius' => 3]); // Output: '...is my na...' we got three dots before and after and truncate.
// By default radius is 100
Str::excerpt('This is my name', 'name', ['radius' => 3, 'omission' => '(...) ']); // '(...) my name'

Str::fromBase64('TGFyYXZlbA=='); // Laravel. Decode a base64 to a string.

// Str::inlineMarkdown: GitHub flavored Markdown into inline HTML. Not secured for CORS Attack, add other arguments to make seured. 