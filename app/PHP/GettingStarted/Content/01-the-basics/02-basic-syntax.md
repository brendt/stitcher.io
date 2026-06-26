---
title: Basic Syntax
---

## Variables

Variables in PHP are defined with the `$` sign and a name. 

```php
// index.php

$age = 32;
```

Variables in PHP are loosely typed and their types can change over time.

```php
// index.php

$age = 32;

$age = 'a';
```

Variables can store primitive types like integers, floats, booleans, and strings; or contain more complex types like objects, enums, and arrays. I'll cover PHP's type system in depth in [a later chapter](/php/the-basics/phps-type-system): even though PHP used to have a fairly loose type system, it has evolved quite a lot and now has an extensive type system that's both enforced during runtime, but also with the help of static analyzers.

Continuing with the basics of variables first, PHP is very flexible when it comes to referencing them. For example, you can reference a variable's name, with another variable's value:

```php
// index.php

$name = 'variable';

$variable = 5;

echo $$variable; 

// This will output `5`
// given that `$variable` is a string `'variable'`
// and `$variable` has the value of `5`
```

## Comments

As you've noticed in the previous examples, comments in PHP can be written with `//`. However, there are other ways to define comments as well:

```php
// index.php

// A single-line comment with double-dashes

# A single-line comment with the hash-sign

/*
 * A multiline comment
 * The `*` within the comment's body isn't required, 
 * though best practices say you should write them.
 * Most IDEs and editors will insert them automatically for you. 
 */

/* Multiline comments can also go on a single line */

/* They can even go */ $between = 'code';  /* if you want to */

/**
 * Finally, there are doc comments, 
 * a special kind of multi-line comment that start with `/**` instead of `/*`
 * Doc comments can be used to attach structured documentation to your code, 
 * which can be parsed by external tools and PHP itself during runtime.
 */
```

## Built-in keywords

PHP has a bunch of built-in keywords. You've already seen the `echo` keyword in a previous example, but there are a lot more. There's `clone`, `require`, `yield`, `include`, and many more. You can find a [full list of keywords on the PHP website](https://www.php.net/manual/en/reserved.keywords.php).

Keywords are words that have a special meaning in PHP and are so-called "reserved", meaning you cannot use them in, for example, function names. Each keyword has a special _thing_ it can do. For example, `echo` and `print` write text to the output buffer; `clone` makes a copy of an object, etc. Don't worry about learning all keywords right now, I'll cover each of them when they are relevant to understand a bigger concept. 

## Control Structures

Speaking of keywords, control structures allow you to alter the code execution flow. There are the common examples like `if` and `for`, but PHP has a wide range of control structures. I'll list the most important ones here, and you can find the [full list of control sturctures on the PHP website](https://www.php.net/manual/en/language.control-structures.php). 

### Conditionals

`if` and its related keywords allow you to write conditional logic.

```php
// index.php

$published = true; // This is a boolean value, by the way, it can be `true` or `false`

if ($published) {
    echo 'Yes';
} else {
    echo 'No';
}
```

Apart from `if` and `else`, there's also `elseif` to create more complex conditionals:

```php
// index.php

$drafted = true;
$published = false;

if ($published) {
    echo 'Yes';
} elseif ($drafted) {
    echo 'Almost';
} else {
    echo 'No';
}
```

You can also write complex boolean expressions with [logical operators](https://www.php.net/manual/en/language.operators.logical.php) like and `&&`, or `||`, and not `!`

```php
// index.php

$drafted = true;
$published = false;

if ($drafted || $published) {
    echo 'Yes';
} elseif (! $published) {
    echo 'Not published';
}
```

### Loops

Another category of control structures are loops. PHP has a handful: `for`, `foreach`, `while`, and `do while`. Loops allow you to execute the same code multiple times. The classic `for` loop starts from an initial state, iterates for as long as a condition isn't met, and will alter the state with every step.

```php
// index.php

for ($i = 0; $i < 10; $i = $i + 1) {
    echo $i; // Will print the numbers 0 to 9
}
```

In contrast, the `foreach` loop will iterate over an array (or any kind of iterable). We haven't covered these concepts yet, but we will further down this page.

```php
// index.php

$arrayOfNumbers = [1, 2, 3, 4, 5];

foreach ($arrayOfNumbers as $number) {
    echo $number; // Will print 1, 2, 3, 4, and 5
}
```

A `while` loop is more flexible than a `for` loop, because it only requires you to specify an initial condition. It's up to you to make state changes to satisfy or break this condition:

```php
// index.php

$i = 0;

while ($i < 10) {
    echo $i; // Will print the numbers 0 to 9
    
    $i = $i + 1;
}
```

Finally, `do while` is a variation of the classic `while` loop where you always execute the while loop at least once:

```php
// index.php

$i = 0;

do {
    echo $i; // This will print 0, even though the condition of `$i < 0` isn't met.
    
    $i = $i + 1;
} while ($i < 0);
```

Within all of these loops, you can use two additional keywords `break` and `continue` to futher alter the flow

```php
// index.php

for ($i = 0; $i < 10; $i = $i + 1) {
    if ($i === 3) {
        continue; // Immediately continue with the next iteration
    }
    
    if ($i > 6) {
        break; // Break from the loop and ignore the rest of the loop 
    }
    
    echo $i; // This will print out the numbers 0, 1, 2, 4, and 5
}
```

## Comparisons

There are a couple of important things to note about comparing values and variables in PHP. As I mentioned before: PHP is a loosely typed language, which means it's pretty flexible when it comes to comparing values; you can compare values in a loose or strict way.

Loose comparison will compare values regardless of their type, this is done with the double equal operator `==`. For example:

```php
// index.php

$integer = 1;
$float = 1.0;

if ($integer == $float) {
    // true
}
```

Loose comparisons are even more flexible, as they allow you to compare empty values:

```php
// index.php

$emptyString = '';
$zero = 0;

if ($emptyString == $zero) {
    // true
}
```

Because loose comparisons can easily lead to unforeseen side effects, it's highly recommended to always use strict comparisons, unless you have a very good reason not to do it. Strict comparisons are done with the triple equal operator `===`:

```php
// index.php

$emptyString = '';
$zero = 0;

if ($emptyString === $zero) {
    // false
}
```

Similar, strict negations are done with the `!==` operator:

```php
// index.php

$integer = 1;
$float = 1.0;

if ($integer !== $float) {
    // true
}
```

Finally, there are also the number comparison operators to determine whether two numbers are greater or less than:

```php
// index.php

$a = 1;
$b = 5;

if ($a > $b) {
    // false
} elseif ($b <= $a) {
    // false
} elseif ($a >= $b) {
    // false
} elseif ($a < $b) {
    // true
}
``` 

## Arrays

PHP arrays are a powerful data structure that can be used both as a list or sequence of items but also as a map of values with named keys.

```php
// index.php

$list = [1, 2, 3, 4];

$map = [
    'title' => 'Basic Syntax', 
    'description' => "PHP's basic syntax explained",
];
```

Note that "a list" means that the indices of the array are numeric and in order, starting from `0`. The above example of `$list` could also be written like so, and still be a list:

```php
// index.php

$list = [
    0 => 1, 
    1 => 2, 
    2 => 3, 
    4 => 4,
];
```

Another thing to notice is that arrays can contain mixed types as well:

```php
// index.php

$list = [1, '2', 1.2, {:hl-keyword:false:}];
```

PHP has a wide range of [array-specific functions](https://www.php.net/manual/en/function.array.php) to read, iterate, and manipulate arrays. We'll take a closer look at arrays and other kinds of iterables in a later chapter.

## Strings

You might have noticed a couple of different ways to define strings in PHP in the previous examples. The two most common ways are using single `'` or double `"` quotes.

```php
// index.php

$name = 'Brent';

$hello = "Hello";
```

Double `"` quotes give you more flexibility when it comes to writing special characters and string interpolation. For example, you can write `"\n"`, which will be interpeted as a new line, or `"{$variable}"`, which will insert the variable's value:

```php
// index.php

$name = 'Brent';

echo "Hello {$name}\n";

// Will print `Hello Brent
// `
```

You can also use the string concatenation operator `.` to combine two strings together:

```php
// index.php

$name = 'Brent';

echo 'Hello ' . $name . "\n";

// Will print `Hello Brent
// `
```

## Functions

A function in PHP allows you to write reusable code. A function is defined using to `function` keyword, it takes some an arbitrary amount of input parameters, and will return some output or nothing.

```php
// index.php

function hello($name) 
{
    echo 'Hello ' . $name . "\n";
}

hello('Brent');
```

The above function writes something using `echo`, but it's not returning any value from the function itself. To do that, you can use the `return` keyword:

```php
// index.php

function hello($greeting, $name) 
{
    return $greeting . ' ' . $name . "\n";
}

echo hello('Hi', 'Brent');
```

PHP also comes with a very rich library of built-in functions. They allow you to manipulate files, perform HTTP requests, connect to databases, transform arrays, and many, many more things. We'll discuss them in depth in the next chapter about [PHP's standard library](/php/the-basics/the-standard-library).

Also, there's much more to say about functions; we'll circle back to them later in this chapter.

## Classes

## Types
