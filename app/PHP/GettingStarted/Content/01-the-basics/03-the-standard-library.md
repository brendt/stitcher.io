---
title: The Standard Library
---

Being over 30 years old, PHP comes with a massive standard library and a collection of built-in functions and classes that you can make use of. On top of that, there's an ecosystem of PHP extensions to install as well.

The most common part of PHP's standard functionality comes both in a functional and object-oriented flavor. It's impossible to discuss everything in this book, but I will show you an overview of what's possible with PHP.

## Extensions

A big part of PHP's core functionality is added via extensions. Some extensions are bundled and thus built-in, others can be optionally installed. Depending on how you installed PHP, there may be more or less optional extensions preinstalled. You can always check which extensions are installed by running the following command:

```shell
~ php -m

# [PHP Modules]
# bcmath
# bz2
# calendar
# Core
# …
# xsl
# Zend OPcache
# zip
# zlib
```

Installing additional extensions is done with a tool called [PIE](https://www.php.net/manual/en/install.pie.intro.php). Once PIE is available on your system, you can run `pie install` to add extensions to PHP:

```shell
~ pie install imagick/imagick
```

All available extensions are listed on [Packagist](https://packagist.org/extensions), which is the standard repository for PHP packages. Packagist handles both PHP extensions and userland packages, which are packages written in PHP itself. We'll discuss this topic in-depth in [the next chapter](/php/the-basics/packagist-composer).

## Arrays

PHP comes with a collection of functions to iterate and manipulate arrays. [The list is pretty long](https://www.php.net/manual/en/ref.array.php), but here are some highlights.

```php
// index.php

// Count the number of items in an array
$c = count($array);

// Remove values from an array based on a condition
$filtered = array_filter($array, function (mixed $value) {
    return $value < 5;
});

// Get all the keys from an array
$keys = array_keys($array);

// Get the first and last items from an array
$first = array_first($array);
$last = array_last($array);

// Remove duplicate entries from an array
$uniques = array_unique($array);

// Sort an array
sort($array);

// Sort an array based on a user-defined function
usort($array, function ($a, $b) {
    return $a <=> $b;
});

// Combine all elements of an array into a string
implode(', ', $array);
```

## Strings

Similar to arrays, there's [a long list of string functions](https://www.php.net/manual/en/ref.strings.php) available as well. Again, a couple of highlights:

```php
// index.php

// Split a string into an array based on a delimiter
explode(',', $string);

// Trim whitespace or other characters from the start and end of a string
trim($string);

// Check whether a string starts or ends with another string
str_starts_with($string, 'Hello');
str_ends_with($string, 'world');

// Check whether a string contains another string
str_contains($string, 'Brent');

// Convert to all lower- or uppercase
strtolower($string);
strtoupper($string);

// Convert new lines to HTML <br> tags
nl2br($string);

// Convert applicable characters to HTML entities and back
htmlentities($string);
html_entity_decode($html);
```

## DateTime

PHP comes with an extensive collection of classes to deal with dates, times, timezones, and intervals. 

```php
// index.php

$dateTime = new DateTime();

$dateTime->add(new DateInterval('P1D'));

echo $dateTime->format('Y-m-d H:i:s');
```

There's also an immutable version of the `DateTime` class called `DateTimeImmutable`. Immutable classes mean that the original object will never be modified when a function is called. Instead, a new object is created for every function call:

```php
// index.php

$original = new DateTimeImmutable();

// The original object isn't modified
$new = $dateTime->add(new DateInterval('P1D')); 

echo $original->format('Y-m-d H:i:s');
echo $new->format('Y-m-d H:i:s');
```

It's usually a good idea to use the `DateTimeImmutable` variant so that you can avoid unforeseen sideeffects when working with dates.

## Files

There's also a wide range of [functions to interact with the filesystem](https://www.php.net/manual/en/ref.filesystem.php).


```php
// index.php

// Read contents from a file
$contents = file_get_contents($path);

// Create a directory
mkdir($path);

// Get the directory name from a path
$dir = dirname($path);

// Get an array of information about a path
$info = pathinfo($path);

// Delete a file
unlink($path);
```

## Database

There are several ways to connect to databases in PHP, but the most common one we'll focus on is [PDO](https://www.php.net/manual/en/book.pdo.php). PDO gives you a object-oriented API to interact with a number of databases: from MySQL to SQLite, MS SQL to Postgres, and [many more](https://www.php.net/manual/en/pdo.drivers.php). Note that, depending on how you installed PHP, you may need to install individual drivers manually.

```php
// index.php

$pdo = new PDO(
    'mysql:host=localhost:3306;dbname=app',
    'root',
    'password',
);

$query = $pdo->prepare('SELECT * FROM books WHERE `title` = :title');
$query->execute(['title' => 'Timeline Taxi']);

$books = $query->fetchAll();
```

We'll have a separate chapter about databases later in this book.

## FFI

FFI stands for [Foreign Function Interface](https://www.php.net/manual/en/book.ffi.php) and allows you to interact with other languages like Rust or C from within PHP.

```php
$ffi = FFI::cdef(
    "int printf(const char *format, ...);",
    "libc.so.6",
);
    
$ffi->printf("Hello %s!\n", "world");
```

Here's a repository with [examples of what you can do with FFI](https://github.com/gabrielrcouto/awesome-php-ffi](https://github.com/gabrielrcouto/awesome-php-ffi).

## Cryptography

PHP has built-in support for many cryptographical extensions for encryption and hashing, like llibsodium and libargon2. For basics like password hashing, you don't need to worry about the underlying details, as the [`password_hash()`](https://www.php.net/manual/en/function.password-hash.php) function will automatically use the strongest hashing algorithm avaiable.

## Image processing

PHP comes with a library called [GD](https://www.php.net/manual/en/book.image.php) built-in to do image processing. In a web context, imagine processing can be incredibly useful to generate meta-images or downscaled versions of responsive images.

```php
// index.php

$image = imagecreatefrompng(__DIR__ . '/tempest-meta.png');

// …

imagecopyresized($image, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
```

When preferred, you can also install [ImageMagick as an extension](https://www.php.net/manual/en/book.imagick.php) for even more extended image support.

## Maths

Apart from [classic math functions](https://www.php.net/manual/en/ref.math.php), PHP also has an optional extension called [BC Maths](https://www.php.net/manual/en/book.bc.php) for arbitrary precision mathematics.

```php
// index.php

$num1 = 0; // (string) 0 => '0'
$num2 = -0.000005; // (string) -0.000005 => '-5.05E-6'

echo bcadd($num1, $num2, 6); // => '0.000000'
```

## cURL

## DOM

## Redis

## Random

## JSON

## Mail