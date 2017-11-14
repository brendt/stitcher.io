You've probably used the ternary `?:` and the null coalescing `??` operators in PHP. 
But do you really know how they work? 
Understanding these operators makes you use them more, resulting in a cleaner codebase.

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
If the condition evaluates to `false`, the righand will be used.

Interesting fact: the name **ternary operator** actually means "an operator which acts on three operands". 
An **operand** is the term used to denote the parts needed by an expression. 
The ternary operator is the only operator in PHP which requires three operands: 
the condition, the `true` and the `false` result. Similarly, there are also **binary** and **unary** operators. 
You can read more about it [here](*http://php.net/manual/en/language.operators.php).

Back to ternary operators: do you know which expressions evaluate to `true`, and which don't? 
Take a look at the `boolean` column of this table: [http://php.net/manual/en/types.comparisons.php](*http://php.net/manual/en/types.comparisons.php).

The ternary operator will use its lefthand operand when the condition evaluates to `true`. 
This could be a string, an integer, a boolean etc. 
**The righthand operand will be used for so called "falsy values"**. 
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

## Null coalescing operator

Did you take a look at [the types comparison table](*http://php.net/manual/en/types.comparisons.php) earlier? 
The null coalescing operator is similar to the ternary operator, 
but will use `isset` **on the lefthand operand** instead of just its boolean value. 
This makes this operator especially useful for arrays. 
It will also use the lefthand operand for falsy values:

```php
var_dump($undifined ?? 'fallback'); // 'fallback'

$unassigned;
var_dump($unassigned ?? 'fallback'); // 'fallback'

$assigned = 'foo';
var_dump($assigned ?? 'fallback'); // 'foo'

var_dump('' ?? 'fallback'); // ''
var_dump('foo' ?? 'fallback'); // 'foo'
var_dump('0' ?? 'fallback'); // '0'
var_dump(0 ?? 'fallback'); // 0
var_dump(false ?? 'fallback'); // false

$input = [
    'key' => 'key',
];
var_dump($input['key'] ?? 'fallback'); // 'key'
var_dump($input['undefined'] ?? 'fallback'); // 'fallback'

var_dump(null ?? 'fallback'); // 'fallback'
```

The null coalescing operator takes two operands, making it a **binary operator**. 
"Coalescing" by the way, means "coming together to form one mass or whole". 
It will take two operands, and decide which of those to use based on the value of the lefthand operand.

The array condition in the above example could also be written using a ternary operator:

```php
$output = isset($input['key']) ? $input['key'] : 'fallback';
```

Note that it's impossible to use the shorthand ternary operator in this case. 
It will either trigger an error or return a boolean, instead of the real lefthand operand's value.

```php
// Returns `true` instead of the value of `$input['key']`
$output = isset($input['key']) ?: 'fallback' 

// Will trigger an 'undefined index' notice when $output is no array or has no 'key'.
// It will trigger an 'undefined variable' notice when $output doesn't exist.
$output = $input['key'] ?: 'fallback';
```

### Null coalescing assignment operator

In the future, we can expect an even shorter syntax called the "null coalescing assignment operator": [https://wiki.php.net/rfc/null_coalesce_equal_operator](https://wiki.php.net/rfc/null_coalesce_equal_operator).

```php
// This operator is not in PHP yet!

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

