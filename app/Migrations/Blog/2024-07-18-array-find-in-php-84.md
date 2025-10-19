PHP 8.4 adds a handful of functions that have been missing for a while: `{php}array_find()` and its variants. The purpose of `{php}array_find()` is simple: pass it an array and a callback, and return the first element for which the callback returns true. 

```php
$numbers = [1, 2, 3, 4, 5, 6];

$firstMatch = array_find(
    array: $numbers, 
    callback: fn (int $number) => $number % 2 === 0
);
```

To start off with, I want to make a note on naming conventions: in Laravel, the function that returns the first element from an array that matches a callback is called `{php}Collection::first()` instead of _find_. This might cause some confusion for people expecting `{php}array_find()` to return _all_ elements that match the callback's condition. 

The decision for `{php}array_find()` over `{php}array_first()` isn't all that weird though: lots of languages implement a method to find the _first matching_ element from an array, and those functions are always called _find_. Just to name two examples: [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find) and [Rust](https://doc.rust-lang.org/rust-by-example/fn/closures/closure_examples/iter_find.html).

If you need a way to get _multiple elements_ from the array based on a callback, then the `{php}array_filter()` function is what you're looking for.

```php
$numbers = [1, 2, 3, 4, 5, 6];

$allMatches = array_filter(
    array: $numbers, 
    callback: fn (int $number) => $number % 2 === 0
);
```

Another important thing to note is that `{php}array_find()`, as well as the three other functions (we'll look at those later in this post), they accept both the value _and_ key as arguments in the callback function:

```php
$firstMatch = array_find(
    array: $array, 
    callback: fn (mixed $value, int|string $key) => /* … */
);
```

By the way, you can read with me through the RFC to learn about all the details:

<iframe width="560" height="345" src="https://www.youtube.com/embed/yuCTnlEUJ4c" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## array_find_key

Besides `{php}array_find()`, there's now also a function called `{php}array_find_key()`. It does exactly the same, but returns the key instead of the value of the matched element:

```php
$numbers = [1, 2, 3, 4, 5, 6];

$firstMatchedKey = array_find_key(
    array: $numbers, 
    callback: fn (int $number) => $number % 2 === 0
);
```

## array_any and array_all

Finally, there are two related functions added, these two will return a boolean instead of a value. `{php}array_any()` will return `{php}true` if at least one element within the array matches a callback's condition, while `{php}array_all()` will return `{php}true`, _only_ if _all_ elements match the callback's condition:

```php
$numbers = [1, 2, 3, 4, 5, 6];

// True: at least one element is dividable by 2
array_any(
    array: $numbers,
    callback: fn (int $number) => $number % 2 === 0
);

// False: not all elements are dividable by 2
array_all(
    array: $numbers,
    callback: fn (int $number) => $number % 2 === 0
);

// True: all elements are smaller than 10
array_all(
    array: $numbers,
    callback: fn (int $number) => $number < 10
);
```