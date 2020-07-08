PHP 8 introduces the new `match` expression. A powerful feature that will often be the better choice to using `switch`. So what exactly are the differences?

{{ ad:carbon }}

Let's start by comparing the two. Here's a classic `switch` example:

```php
switch ($statusCode) {
    <hljs red>case</hljs> 200:
    <hljs red>case</hljs> 300:
        <hljs yellow>$message =</hljs> null;
        <hljs red>break;</hljs>
    <hljs red>case</hljs> 400:
        <hljs yellow>$message =</hljs> 'not found';
        <hljs red>break</hljs>;
    <hljs red>case</hljs> 500:
        <hljs yellow>$message =</hljs> 'server error';
        <hljs red>break</hljs>;
    default:
        <hljs yellow>$message =</hljs> 'unknown status code';
        <hljs red>break</hljs>;
}
```

Here's its `match` equivalent:

```php
<hljs green>$message =</hljs> <hljs keyword>match</hljs> ($statusCode) {
    200<hljs green>,</hljs> 300 => null<hljs green>,</hljs>
    400 => 'not found'<hljs green>,</hljs>
    500 => 'server error'<hljs green>,</hljs>
    default => 'unknown status code'<hljs green>,</hljs>
};
```

First of all, the `match` expression is significantly shorter:

- `match` doesn't require a `break` statement
- `match` can combine different arms into one using a comma
- `match` returns a value, so you only have to assign it once

But there's lots more to it!

## No type coercion

`match` will do strict type checks instead of loose ones. It's like using `===` instead of `==`.
People will probably disagree whether that's a good thing or not, but that's a [topic on its own](/blog/tests-and-types).

```php
$statusCode = '200';

$message = <hljs keyword>match</hljs> ($statusCode) {
    200 => null,
    default => 'unknown status code',
};

// $message = 'unknown status code'
```


## Unknown options trigger errors

If you forget an option, and there's no `default` arm specified, PHP will throw an `UnhandledMatchError` exception. Again more strictness, but it will prevent subtle bugs from going unnoticed.  

```php
$statusCode = 400;

$message = <hljs keyword>match</hljs> ($statusCode) {
    200 => 'perfect',
};

// UnhandledMatchError
```

## Only single-line expressions, for now

Just like [short closures](/blog/short-closures-in-php), you can only write one expression. Expression blocks will probably get added at one point, but it's still not clear when exactly.

## Combining conditions

You already noticed the lack of `break`? This also means `match` doesn't allow for fallthrough conditions, like the two combines `case` lines in the first `switch` example. On the other hand though, you can combine conditions in the same like, separated by commas.

So you have the same functionality as switch in this regards, but with less writing, and less ways to screw up. Win-win!

```php
$message = <hljs keyword>match</hljs> ($statusCode) {
    200, 300, 301, 302 => 'combined expressions',
};
```

## Complex conditions and performance

Like with `switch`, you sometimes need complex calculations to determine whether an arm evaluates to true or not. For example whether a string matches a regular expression:

```php
$message = <hljs keyword>match</hljs> ($line) {
    $this-><hljs prop>matchesRegex</hljs>($line) => 'match A',
    $this-><hljs prop>matchesOtherRegex</hljs>($line) => 'match B',
    default => 'no match',
};
```

A `switch` statement would execute all these regex functions before evaluating every arm. The `match` expression will first execute the first arm, and stop if it evaluates to true, and so on. This might result in a significant performance increase, in some cases!

This is also the reason, by the way, why you don't want to use the "array key match workaround" — I've been guilty of doing this in the past, I admit!

```php
$message = [
    $this-><hljs prop>matchesRegex</hljs>($line) => 'match A',
    $this-><hljs prop>matchesOtherRegex</hljs>($line) => 'match B',
][$line] ?? 'no match';
```

This technique will also execute all regex functions first, again decreasing performance. So you're better off with `match`, in those cases. 

## Throwing exceptions

Because of [throw expressions in PHP 8](/blog/new-in-php-8#throw-expression-rfc), it's also possible to directly throw from an arm, if you'd like to.

```php
$message = <hljs keyword>match</hljs> ($statusCode) {
    200 => null,
    500 => throw new <hljs type>ServerError</hljs>(),
    default => 'unknown status code',
};
```

## Pattern matching

Pattern matching is a technique used in other programming languages, to allow complexer matching than simple values. Think of it as regex, but for variables. 

Pattern matching isn't supported right now, because it's quite a complex feature, but Ilija Tovilo, the [RFC author](*https://wiki.php.net/rfc/match_expression_v2) did mention it as a possible future feature. Something to look out for!

## So, switch or match?

If I'd need to summarise the `match` expression in one sentence, I'd say it's the stricter and more modern version of it's little `switch` brother.

There are some cases — _see what I did there?_ — where `switch` will offer more flexibility, especially with multiline code blocks. However, the strictness of the `match` operator is appealing, and the perspective of pattern matching would be a game-changer for PHP.

I admit I never wrote a `switch` statement in the past years because of its many quirks; quirks that `match` actually solve. So while it's not perfect yet, there are use cases that I can think of, where `match` would be a good… match.
