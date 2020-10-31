In PHP it's possible to do array + array. The "plus" sign is a shorthand way of merging arrays, but there's a difference in how they are merged compared to using `array_merge`.

Let's imagine these two arrays:

```php
$first = [
    'a',
    'b',
];

$second = [
    'c',
];
```

Merging them using `+` would result in the following:

```php
$first + $second;

// ['a', 'b']
```

While using `array_merge`, would result in this:

```php
<hljs prop>array_merge</hljs>($first, $second);

// ['a', 'b', 'c']
```

{{ ad:carbon }}

What's happening here is that `array_merge` will override existing keys, while `+` will not. In other words: when a key exists in the first array, `+` will not merge an item with the same key from another array into the first one. 

In our example, both arrays actually had numerical keys, like so:

```php
$first = [
    0 => 'a',
    1 => 'b',
];

$second = [
    0 => 'c',
];
```

Which explains why `$first + $second` doesn't add 'c' as an element: there already is an item with index `0` in the original. 

The same applies for textual keys:

```php
$first = [
    'a' => 'a',
    'b' => 'b',
];

$second = [
    'a' => 'a - override',
];

$first + $second;

// ['a' => 'a', 'b' => 'b']
```

And finally, `+` also works with nested arrays:

```php
$first = [
    'level 1' => [
        'level 2' => 'original'
    ],
];

$second = [
    'level 1' => [
        'level 2' => 'override'
    ],
];
```

Using `+` will keep the `original` value, while `array_merge` would `override` it.

One more thing to mention is that `+` will apply the same behaviour when [merging multidimensional arrays](/blog/merging-multidimensional-arrays-in-php).

{{ cta:mail }}
