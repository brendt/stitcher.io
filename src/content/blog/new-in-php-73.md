## `is_countable` <small>[rfc](*https://wiki.php.net/rfc/is-countable)</small>

PHP 7.2 added a warning when counting uncountable objects. 
The `is_countable` function can help preventing this warning.

```php
$count = is_countable($variable) ? count($variable) : null;
```

## `array_key_first` and `array_key_last` <small>[rfc](*https://wiki.php.net/rfc/array_key_first_last)</small>

These two functions basically do what the name says.

```php
$array = [
    'a' => '…',
    'b' => '…',
    'c' => '…',
];

array_key_first($array); // 'a'
array_key_last($array); // 'c'
```

The original RFC

## Flexibile Heredoc syntax <small>[rfc](*https://wiki.php.net/rfc/flexible_heredoc_nowdoc_syntaxes)</small>

Heredoc can be a useful tool for larger strings, though they had a indentation quirck in the past.

```php
// Instead of this:

$query = <<<SQL
SELECT * 
FROM `table`
WHERE `column` = true;
SQL;

// You can do this:

$query = <<<SQL
    SELECT * 
    FROM `table`
    WHERE `column` = true;
    SQL;
```

This is especially useful when you're using Heredoc in an already nested context.

The whitespaces in front of the closing marker will be ignored on all lines.

## Tailing commas in function calls <small>[rfc](*https://wiki.php.net/rfc/trailing-comma-function-calls)</small>

What was already possible with arrays, can now also be done with function calls. 
Note that it's not possible in function definitions!

```php
$compacted = compact(
    'posts',
    'units',
);
```

## JSON errors can be thrown <small>[rfc](*https://wiki.php.net/rfc/json_throw_on_error)</small>

Previously, JSON parse errors were a hassle to debug. 
The JSON functions now accept an extra option to make them throw an exception on parsing errors.

```php
json_encode($data, JSON_THROW_ON_ERROR);

json_decode("invalid json", null, 512, JSON_THROW_ON_ERROR);
```

## `list` reference assignment <small>[rfc](*https://wiki.php.net/rfc/list_reference_assignment)</small>

The `list()` and its shorthand `[]` syntax now support references.

```php
$array = [1, 2];

list($a, &$b) = $array;

$b = 3;

// $array = [1, 3];
```

## Undefined variables in `compact` <small>[rfc](*https://wiki.php.net/rfc/compact)</small>

Undefined variables passed to `compact` will be reported with a notice, they were previously ignored.

```php
$a = 'foo';

compact('a', 'b'); 

// Notice: compact(): Undefined variable: b
```

## Case-insensitive constants <small>[rfc](*https://wiki.php.net/rfc/case_insensitive_constant_deprecation)</small>

There were a few edge cases were case-insensitive constants were allowed. 
These have been deprecated.

## Same site cookie <small>[rfc](*https://wiki.php.net/rfc/same-site-cookie)</small>

This change not only adds a new parameter, 
it also changes to way the `setcookie`, `setrawcookie` and `session_set_cookie_params` functions work in a non-breaking manner.

Instead of one more parameter added to already huge functions, they now support an array of options, whilst still being backwards compatible.
An example:

```
bool setcookie(
    string $name 
    [, string $value = "" 
    [, int $expire = 0 
    [, string $path = "" 
    [, string $domain = "" 
    [, bool $secure = false 
    [, bool $httponly = false ]]]]]] 
)

bool setcookie ( 
    string $name 
    [, string $value = "" 
    [, int $expire = 0 
    [, array $options ]]] 
)

// Both ways work.
```

## Several deprecations <small>[rfc](*https://wiki.php.net/rfc/deprecations_php_7_3)</small>

Several small things have been deprecated, there's a possibility errors can show up in your code because of this.

- Undocumented `mbstring` function aliases
- String search functions with integer needle
- `fgetss()` function and `string.strip_tags` filter
- Defining a free-standing `assert()` function
- `FILTER_FLAG_SCHEME_REQUIRED` and `FILTER_FLAG_HOST_REQUIRED` flags
- `pdo_odbc.db2_instance_name` php.ini directive

Please refer to the [RFC](*https://wiki.php.net/rfc/deprecations_php_7_3) for a full explanation of each deprecation.
