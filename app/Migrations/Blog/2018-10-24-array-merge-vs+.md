PHP has several ways of combining two arrays into one. You can use `array_merge` or the `+` operator.
There's a subtle difference between these two methods though, a difference worth knowing.

{{ ad:carbon }}

Let's take a look at how these two methods compare:

```php
array_merge($first, $second);

// vs.

$first + $second;
```

Let's say these are the two arrays we're working with:

```php
$first = [
    'a',
    'b',
];

$second = [
    'c',
];
```

This would be the result of a simple `array_merge` call:

```php
array_merge($first, $second);

[
    'a',
    'b',
    'c',
]
```

While the `+` operator gives us this result:

```php
$first + $second;

[
    'a',
    'b',
]
```

Switching the operands while using the `+` operator, gives a different result:

```php
$second + $first;

[
    'c',
    'b',
]
```

Confused? So was I.

Let's write out the `$first` and `$second` arrays in full, with their indices. 
This will make things more clear:

```php
$first = [
    0 => 'a',
    1 => 'b',
];

$second = [
    0 => 'c',
];
```

By now you can probably guess what's going on: 
the `+` operator will only *add* the elements of the rightside operand, if their key
doesn't exist in the leftside operand, while `array_merge` will *override* existing keys.

By that definition, we can also determine that `+` can never be used to recursively merge arrays, 
as it will leave existing elements untouched:

```php
$first = [
    'A' => [
        'B' => true,
        'C' => true,
    ],
];

$second = [
    'A' => [
        'B' => false,
        'C' => false,
    ],
];

$first + $second;
```

Here's the result:

```php
[
    'A' => [
        'B' => true,
        'C' => true,
    ],
]
```

While using `array_merge`, would give this result:

```php
[
    'A' => [
        'B' => false,
        'C' => false,
    ],
]
```

"Hang on", I hear you say, "isn't that what `array_merge_recursive` is supposed to do?".

Here we have a case of unfortunate naming. 
Please don't be surprised&thinsp;—&thinsp;it's PHP after all.

See, `array_merge` will merge matching elements by overriding them.
`array_merge_recursive` on the other hand will keep both elements, and merge them in a new array, keeping both values.

This is what our previous example would look like, using `array_merge_recursive`:

```php
[
    'A' => [
        'B' => [
            true,
            false,
        ],
        'C' => [
            true,
            false,
        ],
    ],
]
```

What about merging multiple arrays? 
You can probably guess the outcome by now:

```php
$first = ['a'];
$second = ['b'];
$third = ['c'];
```

Here's what `array_merge` results in:

```php
array_merge($first, $second, $third)
```

```php
[
    'a',
    'b',
    'c',
]
```

Chaining the `+` operator also works, with the following result:

```php
$first + $second + $third
```

```php
[
    'a',
]
```

---

With this little refresher, 
I hope that you won't find yourself confused anymore when you're deep into your code and need to merge arrays.

I found it to be a cognitive burden when I had to stop and think about "hang on, what is the correct way to do this?".
Luckily now, we know!
