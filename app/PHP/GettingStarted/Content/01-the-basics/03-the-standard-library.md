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

## Files

## Database

## FFI

## Cryptography

## Image processing

## Maths

## Process Control

## cURL

## DOM

## Redis

## Random

## JSON

## Mail