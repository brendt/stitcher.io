---
title: Basic Syntax
meta:
  title: PHP's syntax explained
description: Learn the essentials of PHP syntax, including variables, type basics, comments, strings, arrays, functions, classes, and more.
image: meta/php/02-basic-syntax.png
---

In this chapter you'll get a basic overview of PHP's syntax. At later points in this book we'll dive deeper into specific topics, but we need to start somewhere, and thus here's the high-level overview.

## &lt;?php

You'll notice how each PHP file starts with a `<?php` opening tag. This is required for PHP to interpret the code in that file as actual PHP code. Most IDEs will automatically insert it for you when creating a new PHP file. From now on, we'll usually omit the `<?php` opening tag in examples, unless it's relevant; know that it should always be there.

```php
// index.php

<?php

// Usually the first line of any PHP file contains the <?php opening tag 
```

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

Variables can store primitive types like integers, floats, booleans, and strings; or contain more complex types like objects, enums, and arrays. we'll cover PHP's type system in depth later in this chapter: even though PHP used to have a fairly loose type system, it has evolved quite a lot and now has an extensive type system both enforced during runtime, but also with the help of static analyzers.

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
 * Finally, there are docblocks, 
 * a special kind of multi-line comment that start with `/**` instead of `/*`
 * Docblocks can be used to attach structured documentation to your code, 
 * which can be parsed by external tools and PHP itself during runtime.
 */
```

## Built-in keywords

PHP has a bunch of built-in keywords. You've already seen the `echo` keyword in a previous example, but there's also `clone`, `require`, `yield`, `include`, and many more. You can find a [full list of keywords on the PHP website](https://www.php.net/manual/en/reserved.keywords.php). Keywords are words that have a special meaning in PHP and are so-called "reserved", meaning you cannot use them in, for example, class or function names. They can be used as variable names such as `$echo`, since variables are always prefixed with the `$` sign. 

Each keyword has a special _thing_ it can do. For example, `echo` and `print` write text to the output buffer; `clone` makes a copy of an object, etc. Don't worry about learning all keywords right now, we'll cover each of them when they are relevant to understand a bigger concept.

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
    3 => 4,
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

## Control structures

Control structures use special keywords that allow you to alter the code execution flow. There are the common examples like `if` and `for`, but PHP has a wide range of control structures. We'll list the most important ones here, and you can find the [full list of control sturctures on the PHP website](https://www.php.net/manual/en/language.control-structures.php). 

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

Another category of control structures is loops. PHP has a handful: `for`, `foreach`, `while`, and `do while`. Loops allow you to execute the same code multiple times. The classic `for` loop starts from an initial state, iterates for as long as a condition is met, and will alter the state with every step.

```php
// index.php

for ($i = 0; $i < 10; $i = $i + 1) {
    echo $i; // Will print the numbers 0 to 9
}
```

In contrast, the `foreach` loop will iterate over an array (or any kind of iterable, which we'll cover later in this book).

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

Within all of these loops, you can use two additional keywords `break` and `continue` to futher alter the execution flow:

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

Next, there are also the number comparison operators to determine whether two numbers are greater or less than:

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

Finally, there's the so-called "spaceship operator" `<=>` which will compare two items and return either `-1`, `0` or `1` depending on which is the biggest. This operator is useful when sorting items.

```php
// index.php

$a = 'a';
$z = 'z';

echo $a <=> $z; // -1
echo $z <=> $a; // 1
echo $a <=> $a; // 0
```

## Functions

A function in PHP allows you to write reusable code. A function is defined using the `function` keyword, it takes an arbitrary amount of input parameters, and will return some output or nothing.

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

Another important note about functions is that any function allows you to use named parameters instead of positional ones:

```php
// index.php

function hello($greeting, $name) 
{ /* … */ }

echo hello(name: 'Hi', greeting: 'Brent');
```

Named parameters can be useful when functions have optional parameters you want to skip or to add a bit more structure for function calls with long parameter lists.


```php
// index.php

function complex($a, $b = null, $c = null, $d = null, $e = null) 
{ /* … */ }

echo hello(a: 'A', e: 'E');
```

PHP also comes with a rich library of built-in functions. They allow you to manipulate files, perform HTTP requests, connect to databases, transform arrays, and many, many more things. We'll discuss them in depth in the next chapter about [PHP's standard library](/php/the-basics/the-standard-library).

There's much more to say about functions; we'll circle back to them later in this chapter.

## Closures

Closures are a special kind of functions that have no name. Instead of calling them by name, they can be stored in a variable:

```php
// index.php

$hello = function($name)
{
    return 'Hello ' . $name . "\n";
};

$hello('Brent');
```

Another way of accessing them is to pass them directly into another function:

```php
// index.php

function newline($closure)
{
    return $closure('Brent') . "\n";
}

echo newline(function ($name) {
     return "Hello, {$name}";
});
```

One important note about closures is that they don't have access to variables declared outside of them by default:

```raw
// index.php

$greeting = {:hl-value:'Hello':};

$hello = {:hl-keyword:function:}($name)
{
    {:hl-comment:// `$greeting` is undefined:}
    {:hl-keyword:return:} {:hl-error:$greeting:} . {:hl-value:' ':} . $name . {:hl-value:"\n":};
};
```

In order to access variables from the outer scope — "the outside" — you can use the `use` keyword:

```php
// index.php

$greeting = 'Hello';

$hello = function($name) use ($greeting)
{
    return $greeting . ' ' . $name . "\n";
};
```

Finally, PHP also has a way of writing short closures: these are closures that directly return a result. They are written with the `fn()` keyword:

```php
// index.php

$greeting = 'Hello';

$hello = fn($name) => $greeting . ' ' . $name . "\n";
```

Note how you don't need to write `return` in short closures: they only take one line of code, and the result of that is always returned. Also note how short closures don't need to use `use`. Indeed, they have access to the outer scope automatically.

## Types

PHP's type system is pretty unique compared to other programming languages. Types can be defined in most places and will be checked at runtime by the PHP interpreter. However, the use of a static analyzer to type-check your codebase before running it is highly recommended. PHP doesn't have a built-in static type checker, but there are several great third-party options out there. The most notable ones are [PHPStan](https://phpstan.org/), [Mago](https://mago.carthage.software/1.30.0/en/), and [Psalm](https://psalm.dev/). We'll discuss them in-depth in a later chapter. However, it's already important to discuss the basics of PHP's type system now, because the rest of this book will use them all throughout.

Let's revisit our `hello()` function from the previous examples, this time with added types:

```php
// index.php

function hello(string $greeting, string $name): string 
{
    return $greeting . ' ' . $name . "\n";
}
```

These type annotations tell PHP that both `$greeting` and `$name` should be strings, and that the function will also return a string. `{:hl-type:string:}` is one of the so-called "primitive types". Here's a list of all built-in types (primitive and complex):

- `{:hl-type:bool:}`
- `{:hl-type:int:}`
- `{:hl-type:float:}`
- `{:hl-type:array:}`
- `{:hl-type:object:}`
- `{:hl-type:resource:}`
- `{raw}{:hl-type:callable:}`
- `{:hl-type:iterable:}`
- `{:hl-type:mixed:}`
- `{:hl-type:void:}`
- `{:hl-type:never:}`

Apart from built-in types, there are also classes that can be used as types. PHP infamously doesn't have built-in support for generic types, although these are supported when using the previously mentioned static analysis tools. We'll cover them in a later chapter.

What's important to note about PHP's type system is that types are checked at the boundaries of function calls. That means that the type of `$greeting` can still change within the `hello()` function's body:

```php
// index.php

function hello(string $greeting, string $name): string 
{
    // We're sure $greeting is of type `string` at this point; however,
    // we're allowed to change its type afterward:
    $greeting = 1;
}
```

While PHP doesn't guard against these type changes, the static analyzers do. Again, we'll revisit them in a later chapter.

Another important thing to mention is that, by default, PHP allows types to be juggled automatically. Let's say you pass in an integer into a parameter that should be a string; then PHP will automatically convert it to a string for you:

```php
// index.php

function hello(string $greeting, string $name): string 
{ /* … */ }

hello(1, 2); // Will print "1, 2"
```

This behavior can be prevented by enabling PHP's "strict type" mode, which you can define on a per-file basis:

```php
// index.php

<?php declare(strict_types=1);

function hello(string $greeting, string $name): string 
{ /* … */ }

hello(1, 2); // Will error
```

Note that `declare(strict_types=1)` is rather limited as it only checks for strict types on a per-file basis. Later in this book, we'll recommend the use of a static analyzer instead. Also note that since PHP checks types at the boundaries of function calls, it's not possible to type a standalone variable; once again something that's solved with static analyzers.

From here on out, we'll use types whenever possible. They aren't a requirement, but we're learning about modern PHP for serious projects, and types are an invaluable tool to use.

## Classes

Classes are the standard way of structuring your code in modern PHP. As with many C-style programming languages, classes in PHP are used as a blueprint to create objects from. A class has properties and methods, some of these methods have special meaning. 

```php
// index.php

class Book
{
    public function __construct(
        public readonly string $title
    ) {}   
}

$book = new Book('Timeline Taxi');
```

Classes can live in any PHP file, but the best practice is to make a new PHP file for each class. On top of that, classes should also live in a namespace, which makes including them from other places more convenient. We'll cover the concept of autoloading more in depth in the [Composer chapter](/php/the-basics/composer). For now, it's good to know that this is the recommended approach to structure your classes:

```php
// app/Models/Book.php

<?php

namespace App\Models;

class Book
{
    private string $title;
    
    public function __construct(string $title)
    {
        $this->title = $title;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
}
```

If you're using a proper IDE for PHP development, namespaces will automatically be added for you when creating classes. If you want to reference a class from another file, you can do so by using its namespace and class name:

```php
// index.php

use App\Models\Book;

$book = new Book('Timeline Taxi');
```

Similar to many other object-oriented languages, classes come with a bunch of keywords. There are the visibility modifiers `public`, `protected`, and `private`; as well as tools for subclassing and interfacing like `abstract`, `extends`, and `interface`. We'll have a dedicated [in-depth chapter about classes](/php/in-depth/classes) later where we'll cover all these topics and more like promoted properties, property hooks, magic methods, reflection, and more.

## Attributes

[Attributes](/blog/attributes-in-php-8) — AKA "annotations" in other languages — are special classes that can be used to add metadata to classes, methods, functions, parameters, properties, and constants. Most attributes are defined in userland code and are often provided by frameworks. There are a handful of niche attributes shipped with PHP as well that might even impact PHP's runtime. In general though, attributes are only used for meta-programming and static analysis.

Here's one example of how an attribute can be used to make a controller action accessible via an HTTP GET request. Don't worry about the details; there's a dedicated chapter about [frameworks](/php/the-basics/frameworks).

```php
// app/Controllers/HomeController.php

final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('./home.view.php');
    }
}
```

## Exceptions

Exceptions are used to halt the normal program flow and indicate that something went wrong. PHP has a number of built-in exceptions, and you can create your own as well. Here's what throwing a basic exception looks like:

```php
// index.php

function do_something(bool $problem): void
{
    if ($problem) {
        throw new Exception('Something went wrong');
    }
    
    // …
}
```

When an exception is thrown, you can handle it with `try` and `catch`:

```php
// index.php

try {
    do_something(true);
} catch (Exception $exception) {
    echo $exception->getMessage();
}
```

You can create your own exceptions by creating a dedicated class, and extending from the base `Exception` class:

```php
// app/Exceptions/ValidationException.php

namespace App\Exceptions\ValidationException;

class ValidationException extends Exception {}
```

You can also chain multiple `catch` blocks, and even add a `finally` block; the `finally` block will always be executed, even when no exception was thrown:

```php
// index.php

try {
    do_something(true);
} catch (ValidationException $exception) {
    echo "Validation failed!";
} catch (Exception $exception) {
    echo $exception->getMessage();
} finally {
    echo "Done";
}
```

## In practice

Within a file `index.php`, create two new classes: `Book` and `Author`. A `Book` has three properties: `{:hl-type:string:} {:hl-property:$title:}`, `{:hl-type:Author:} {:hl-property:$author:}`, and `{:hl-type:int:} {:hl-property:$categoryId:}`. An `Author` has a property `{:hl-type:string:} {:hl-property:$name:}`. Use types wherever possible.

Make it so that when the categoryId of a book is below `1` or above `5`, an exception is thrown. In a loop, create 20 books. Use a random integer between `0` and `10` for the category ID. If the creation of a book fails, increment a failed counter. In the end, print out how many books were created successfully.

{{{
```php
// index.php

class Book
{
    private string $title;
    private Author $author;
    private int $categoryId;

    public function __construct(
        string $title,
        Author $author,
        int $categoryId,
    ) {
        if ($categoryId < 1 || $categoryId > 5) {
            throw new Exception("Category ID must be between 1 and 5.");
        }

        $this->title = $title;
        $this->author = $author;
        $this->categoryId = $categoryId;
    }
}

class Author
{
    private string $name;

    public function __construct(
        string $name,
    ) {
        $this->name = $name;
    }
}

$total = 20;
$failed = 0;

foreach (range(1, $total) as $i) {
    $author = new Author("Author {$i}");

    $categoryId = random_int(0, 10);

    try {
        $book = new Book("Book {$i}", $author, $categoryId);
    } catch (Exception $exception) {
        $failed += 1;
    }
}

echo "{$failed} out of {$total} books failed";
```
}}}
