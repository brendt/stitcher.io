You probably already know some comparison operators in PHP. 
Things like the ternary `?:`, the null coalescing `??` and the spaceship `<=>` operators. 
But do you really know how they work? 
Understanding these operators makes you use them more, resulting in a cleaner codebase.

Before looking at each operator in depth, here's a summary of what each of them does:

- The [ternary operator](#ternary-operator) is used to shorten if/else structures
- The [null coalescing operator](#null-coalescing-operator) is used to provide default values instead of null
- The [spaceship operator](#spaceship-operator) is used to compare two values

{{ ad:carbon }}

## Ternary operator

The ternary operator is a shorthand for the `if {} else {}` structure. Instead of writing this:

```php
if ($condition) {
    $result = 'foo' 
} else {
    $result = 'bar'
}
```

You can write this:

```php
$result = $condition ? 'foo' : 'bar';
```

If this `$condition` evaluates to `true`, the lefthand operand will be assigned to `$result`. 
If the condition evaluates to `false`, the righthand will be used.

Interesting fact: the name *ternary operator* actually means "an operator which acts on three operands". 
An *operand* is the term used to denote the parts needed by an expression. 
The ternary operator is the only operator in PHP which requires three operands: 
the condition, the `true` and the `false` result. Similarly, there are also binary and unary operators. 
You can read more about it [here](*http://php.net/manual/en/language.operators.php).

Back to ternary operators: do you know which expressions evaluate to `true`, and which don't? 
Take a look at the `boolean` column of [this table](*http://php.net/manual/en/types.comparisons.php).

The ternary operator will use its lefthand operand when the condition evaluates to `true`. 
This could be a string, an integer, a boolean etc. 
The righthand operand will be used for so called "falsy values". 

Examples would be `0` or `'0'`, an empty array or string, `null`, an undefined or unassigned variable, and of course `false` itself. 
All these values will make the ternary operator use its righthand operand. 

### Shorthand ternary operator

Since PHP 5.3, it's possible to leave out the lefthand operand, allowing for even shorter expressions:

```php
$result = $initial ?: 'default';
```

In this case, the value of `$result` will be the value of `$initial`, unless `$initial` evaluates to `false`, 
in which case the string `'default'` is used.

You could write this expression the same way using the normal ternary operator:

```php
$result = $condition ? $condition : 'default';
```

Ironically, by leaving out the second operand of the ternary operator, it actually becomes a **binary operator**.

### Chaining ternary operators

The following, even though it seems logical; doesn't work in PHP:

```php
$result = $firstCondition
    ? 'truth'
    : $elseCondition
        ? 'elseTrue'
        : 'elseFalse';
```

The reason because is that the ternary operator in PHP is left-associative, and thus parsed in a very strange way.
The above example would always evaluate the `$elseCondition` part first, so even when `$firstCondition` would be `true`, you'd never see its output.

I believe the right thing to do is to avoid nested ternary operators alltogether.
You can read more about this strange behaviour 
in this [Stack Overflow answer](*https://stackoverflow.com/questions/20559150/ternary-operator-left-associativity/38231137#38231137).

Furthermore, as PHP 7.4, the use of chained ternaries without brackets is [deprecated](/blog/new-in-php-74#left-associative-ternary-operator-deprecation-rfc).

## Null coalescing operator

Did you take a look at [the types comparison table](*http://php.net/manual/en/types.comparisons.php) earlier? 
The null coalescing operator is available since PHP 7.0.
It similar to the ternary operator, but will behave like `isset` on the lefthand operand instead of just using its boolean value. 
This makes this operator especially useful for arrays and assigning defaults when a variable is not set. 

```php
$undefined ?? 'fallback'; // 'fallback'

$unassigned;
$unassigned ?? 'fallback'; // 'fallback'

$assigned = 'foo';
$assigned ?? 'fallback'; // 'foo'

'' ?? 'fallback'; // ''
'foo' ?? 'fallback'; // 'foo'
'0' ?? 'fallback'; // '0'
0 ?? 'fallback'; // 0
false ?? 'fallback'; // false
```

The null coalescing operator takes two operands, making it a *binary* operator. 
"Coalescing" by the way, means "coming together to form one mass or whole". 
It will take two operands, and decide which of those to use based on the value of the lefthand operand.

### Null coalescing on arrays

This operator is especially useful in combination with arrays, because of its acts like `isset`.
This means you can quickly check for the existance of keys, even nested keys, without writing verbose expressions. 

```php
$input = [
    'key' => 'key',
    'nested' => [
        'key' => true
    ]
];

$input['key'] ?? 'fallback'; // 'key'
$input['nested']['key'] ?? 'fallback'; // true
$input['undefined'] ?? 'fallback'; // 'fallback'
$input['nested']['undefined'] ?? 'fallback'; // 'fallback'

null ?? 'fallback'; // 'fallback'
```

The first example could also be written using a ternary operator:

```php
$output = isset($input['key']) ? $input['key'] : 'fallback';
```

Note that it's impossible to use the shorthand ternary operator when checking the existance of array keys. 
It will either trigger an error or return a boolean, instead of the real lefthand operand's value.

```php
// Returns `true` instead of the value of `$input['key']`
$output = isset($input['key']) ?: 'fallback' 

// The following will trigger an 'undefined index' notice 
// when $output is no array or has no 'key'.
//
// It will trigger an 'undefined variable' notice 
// when $output doesn't exist.
$output = $input['key'] ?: 'fallback';
```

### Null coalesce chaining

The null coalescing operator can easily be chained:

```php
$input = [
    'key' => 'key',
];

$input['undefined'] ?? $input['key'] ?? 'fallback'; // 'key'
```

### Nested coalescing

It's possible to use the null coalescing operator on nested object properties, even when a property in the chain is `null`.

```php
$a = (<hljs type>object</hljs>) [
    'prop' => null,
];

<hljs prop>var_dump</hljs>($a-><hljs prop>prop</hljs>-><hljs prop>b</hljs> ?? 'empty');

// 'empty'
```

### Null coalescing assignment operator

In PHP 7,4, we can expect an even shorter syntax called the ["null coalescing assignment operator"](https://wiki.php.net/rfc/null_coalesce_equal_operator).

```php
// This operator will be available in PHP 7.4

function (array $parameters = []) {
    $parameters['property'] ??= 'default';
}
```

In this example, `$parameters['property']` will be set to `'default'`, unless it is set in the array passed to the function. 
This would be equivalent to the following, using the current null coalescing operator:

```php
function (array $parameters = []) {
    $parameters['property'] = $parameters['property'] ?? 'default';
}
```

{{ ad:google }}

## Spaceship operator

The spaceship operator, while having quite a peculiar name, can be very useful.
It's an operator used for comparison. 
It will always return one of three values: `0`, `-1` or `1`.

`0` will be returned when both operands are equals, 
`1` when the left operand is larger, and `-1` when the right operand is larger.
Let's take a look at a simple example:

```php
1 <=> 2; // Will return -1, as 2 is larger than 1.
```

This simple example isn't all that exiting, right? 
However, the spaceship operator can compare a lot more than simple values!

```php
// It can compare strings,
'a' <=> 'z'; // -1

// and arrays,
[2, 1] <=> [2, 1]; // 0

// nested arrays,
[[1, 2], [2, 2]] <=> [[1, 2], [1, 2]]; // 1

// and even casing.
'Z' <=> 'z'; // -1
```

Strangely enough, when comparing letter casing, the lowercase letter is considered the highest.
There's a simple explanation though.
String comparison is done by comparing character per character. 
As soon as a character differs, their ASCII value is compared. 
Because lowercase letters come after uppercase ones in the ASCII table, they have a higher value. 

### Comparing objects

The spaceship operator can almost compare anything, even objects. 
The way objects are compared is based on the kind of object. 
Built-in PHP classes can define their own comparison, 
while userland objects are compared based on their attributes and values.

When would you want to compare objects you ask? 
Well, there's actually a very obvious example: dates.

```php
$dateA = DateTime::createFromFormat('Y-m-d', '2000-02-01');

$dateB = DateTime::createFromFormat('Y-m-d', '2000-01-01');

$dateA <=> $dateB; // Returns 1
``` 

Of course, comparing dates is just one example, but a very useful one nevertheless.

### Sort functions

One great use for this operator, is to sort arrays.
There are quite [a few ways](*http://php.net/manual/en/array.sorting.php) to sort an array in PHP,
and some of these methods allow a user defined sort function.
This function has to compare two elements, and return `1`, `0`, or `-1` based on their position.

An excellent use case for the spaceship operator!

```php
$array = [5, 1, 6, 3];

usort($array, function ($a, $b) {
    return $a <=> $b;
});

// $array = [1, 3, 5, 6];
```

To sort descending, you can simply invert the comparison result:

```php
usort($array, function ($a, $b) {
    return -($a <=> $b);
});

// $array = [6, 5, 3, 1];
```

{{ ad:google }}

Hi there, thanks for reading! I hope this blog post helped you!
If you'd like to contact me, you can do so on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io).
I always love to chat! 
