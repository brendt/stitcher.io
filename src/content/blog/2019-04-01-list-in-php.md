## List or []

In PHP, `list` or `[]` is a so called "language construct", just like `array()`. 
This language construct is used to "pull" variables out of an array. 

Here's what that looks like:

```php
$array = [1, 2, 3]; 

// Using the list syntax:
list($a, $b, $c) = $array;

// Or the shorthand syntax:
[$a, $b, $c] = $array;

// $a = 1
// $b = 2
// $c = 3
```

Whether you prefer `list` or its shorthand `[]` is up to you. 
People might argue that `[]` is ambiguous with the shorthand array syntax, 
and therefor prefer `list`. 
I'll be using the shorthand version in code samples though.

 So what more can `list` do?

## Skip elements

Say you only need the third element of an array, 
the first two can be skipped by simply not providing a variable.

```php
[, , $c] = $array;

// $c = 3
```

Also note that `list` will always start at index 0.
Take for example the following array:

```php
$array = [
    1 => 'a',
    2 => 'b',
    3 => 'c',
];
```

The first variable pulled out with `list` would be `null`, 
because there's no element with index `0`. 
This might seem like a shortcoming, but luckily there are more possibilities. 

## Non-numerical keys

PHP 7.1 allows `list` to be used with arrays that have non-numerical keys. 
This opens a world of possibilities.

```php
$array = [
    'a' => 1,
    'b' => 2,
    'c' => 3,
];
```

```php
['c' => $c, 'a' => $a] = $array;
```

As you can see, you can change the order however you want, and also skip elements entirely.

## In practice

One of the uses of `list` are functions like `parse_url` and `pathinfo`.
Because these functions return an array with named parameters, 
we can use `list` to pull out the information we'd like:

```php
[
    'basename' => $file,
    'dirname' => $directory,
] = pathinfo('/users/test/file.png');
``` 

As you can see, the variables don't need the same name as the key.
Also note that destructuring an array with an unknown key will trigger a notice:

```php
[
    'path' => $path, 
    'query' => $query,
] = parse_url('https://stitcher.io/blog');

// PHP Notice:  Undefined index: query
```

In this case, `$query` would be `null`.

One last detail: trailing commas are allowed with named destructs, 
just like you're used to with arrays.

## In loops

You can also use the list construct in loops:

```php
$array = [
    [
        'name' => 'a',
        'id' => 1
    ],
    [
        'name' => 'b',
        'id' => 2
    ],
];

foreach ($array as ['id' => $id, 'name' => $name]) {
    // …
}
```

This could be useful when parsing for example a JSON or CSV file. 
Be careful though that undefined keys will still trigger a notice.

In summary, there are some pretty good cases in which `list` can be of help! 

{{ ad }}

By the way, I know it's called "array destructuring". 
But let's be honest: "destruction" sounds way cooler!
