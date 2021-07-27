The release of [PHP 8.1](/blog/new-in-php-81) will be here within a few months, and once again there are many features that get me excited! In this post I want to share the real-life impact that PHP 8.1 will have on my own code.  

## Enums

A long-awaited feature, [enums](/blog/php-enums) are coming! There's little to be said about this one, besides that I'm looking forward to not having to use [spatie/enum](*https://github.com/spatie/enum) or [myclabs/php-enum](*https://github.com/myclabs/php-enum) anymore. Thanks for all the years of enum support to those packages, but they are the first I'll ditch when PHP 8.1 arrives and when I change this:

```php
/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived()
 */
class StatusEnum extends Enum
{
}
``` 

<em class="center small">PHP 8.0</em>

To this:

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs>
{
    case <hljs prop>draft</hljs>;
    case <hljs prop>published</hljs>;
    case <hljs prop>archived</hljs>;
}
``` 

<em class="center small">PHP 8.1</em>

## Array unpacking with string keys

This one might seem like a small one, but it has bothered me more than once: only list arrays could be unpacked before PHP 8.1:

```php
$a = [1, 2, 3];
$b = [4, 5, 6];

// This is allowed
$new = [...$a, ...$b];
```

<em class="center small">PHP 8.0</em>

While arrays with string keys cannot:

```php
$a = ['a' => 1, 'b' => 2, 'c' => 3];
$b = ['d' => 4, 'e' => 5, 'f' => 6];

$new = <hljs striped>[...$a, ...$b]</hljs>; 

// You'd need to use array_merge in this case
$new = <hljs prop>array_merge</hljs>($a, $b); 
``` 

<em class="center small">PHP 8.0</em>

And so, one of the great features of PHP 8.1 that will make my life easier, is that arrays with string keys can now be unpacked as well!

```php
$a = ['a' => 1, 'b' => 2, 'c' => 3];
$b = ['d' => 4, 'e' => 5, 'f' => 6];

// :)
$new = [...$a, ...$b]; 
```

<em class="center small">PHP 8.1</em>

{{ cta:mail }}

## Class properties: initializers and readonly

Another stunning new feature is once again such a quality-of-life improvement that I've struggled with for years: default arguments in function parameters. Imagine you'd want to set a default state class for a `<hljs type>BlogData</hljs>` object. Before PHP 8.1 you'd have to make it nullable and set it in the constructor:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>?BlogState</hljs> <hljs prop>$state</hljs> = null,
    ) {
        $this-><hljs prop>state</hljs> ??= new <hljs type>Draft</hljs>();
    }
}
```

<em class="center small">PHP 8.0</em>

PHP 8.1 allows that `<hljs keyword>new</hljs>` call directly in the function definition. This will be **huge**:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>BlogState</hljs> <hljs prop>$state</hljs> = <hljs keyword>new</hljs> <hljs type>Draft</hljs>(),
    ) {
    }
}
```

<em class="center small">PHP 8.1</em>

Speaking of huge, have I mentioned yet that **readonly** properties are a _thing_ now?!?

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>BlogState</hljs> <hljs prop>$state</hljs> = <hljs keyword>new</hljs> <hljs type>Draft</hljs>(),
    ) {
    }
}
```

<em class="center small">PHP 8.1</em>

Oh and don't worry about cloning, by the way: [I've got you covered](/blog/cloning-readonly-properties-in-php-81).

## First-class callable syntax

As if that wasn't enough, there's now also the first-class callable syntax, which gives you a clean way of creating closures from callables. 

Previously you'd had to write something like this:

```php
$strlen = <hljs type>Closure</hljs>::<hljs prop>fromCallable</hljs>('strlen');
$callback = <hljs type>Closure</hljs>::<hljs prop>fromCallable</hljs>([$object, 'method']);
```

<em class="center small">PHP 8.0</em>

In PHP 8.1, you can do… this:

```php
$strlen = <hljs prop>strlen</hljs>(...);
$callback = $object-><hljs prop>method</hljs>(...);
```

<em class="center small">PHP 8.1</em>

---

There are even [more features](/blog/new-in-php-81) in PHP 8.1, but these are the ones I'm most excited about. What's your favourite one? Let me know on [Twitter](*https://twitter.com/brendt_gd)!
