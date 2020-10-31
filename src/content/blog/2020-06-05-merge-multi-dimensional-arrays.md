If you want to join two multidimensional arrays in PHP, you should still use `array_merge`, and not `array_merge_recursive`. Confused? So was I. Let's explain what's happening.

{{ ad:carbon }}

Let's first explain what `array_merge_recursive` does, take for example these two arrays:

```php
$first = [
    'key' => 'original'
];

$second = [
    'key' => 'override'
];
```

Using `array_merge_recursive` will result in the following:

```php
<hljs prop>array_merge_recursive</hljs>($first, $second);

// [
//     'key' => [
//         'original',
//         'override',
//     ],
// ]
```

Instead over overriding the original `key` value, `array_merge_recursive` created an array, with the original and new value both in it.

While that looks strange in this simply example, it's actually more useful in cases where one of the values already is an array, and you want to merge another item in that array, instead of overriding it.

```php
$first = [
    'key' => ['original']
];

$second = [
    'key' => 'override'
];
```

In this case, `array_merge_recursive` will yield the same result as the first example: it takes the value from the `$second` array, and appends it to the value in the `$first` array, which already was an array itself.

```php
<hljs prop>array_merge_recursive</hljs>($first, $second);

// [
//     'key' => [
//         'original',
//         'override',
//     ],
// ]
```

So if you want to merge multidimensional arrays, you can simply use `array_merge`, it can handle multiple levels of arrays just fine:

```php
$first = [
    'level 1' => [
        'level 2' => 'original'
    ]
];

$second = [
    'level 1' => [
        'level 2' => 'override'
    ]
];

<hljs prop>array_merge</hljs>($first, $second);

// [  
//     'level 1' => [
//         'level 2' => 'override'
//     ]
// ]
```

All of that being said, you could also use the `+` operator to merge multidimensional arrays, but [it will work slightly different](/blog/what-is-array-plus-in-php) compared to `array_merge`.

{{ cta:mail }}
