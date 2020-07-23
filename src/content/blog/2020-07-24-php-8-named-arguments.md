It was a [close call](/blog/why-we-need-named-params-in-php), but named arguments — also called named parameters — are supported in [PHP 8](/blog/new-in-php-8)! In this post I'll discuss their ins and outs, but let me show you first what they look like with a few examples in the wild:

```php
<hljs prop>setcookie</hljs>(
    <hljs prop>name</hljs>: 'test',
    <hljs prop>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

<em class="center small">Named arguments used on a built-in PHP function</em>

```php
class CustomerData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$name</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$email</hljs>,
        <hljs keyword>public</hljs> <hljs type>int</hljs> <hljs prop>$age</hljs>,
    ) {}
}

$data = new <hljs type>CustomerData</hljs>(
    <hljs prop>name</hljs>: $input['name'],
    <hljs prop>email</hljs>: $input['email'],
    <hljs prop>age</hljs>: $input['age'],
);
```

<em class="center small">A DTO making use of <a href="/blog/constructor-promotion-in-php-8">promoted properties</a>, as well as named arguments</em>

```php
$data = new <hljs type>CustomerData</hljs>(...$customerRequest-><hljs prop>validated</hljs>());
```

<em class="center small">Named arguments also support array spreading</em>

You might have guessed it from the examples: named arguments allow you to pass input data into a function, based on their argument name instead of the argument order.

I would named arguments are a great feature that will have a significant impact on my day-to-day programming life.
You're probably wondering about the details though: what if you pass a wrong name, what's up with that array spreading syntax? Well, let's look at all those questions in-depth.

{{ ad:carbon }}

## Why named arguments?

Let's say this feature was a highly debated one, and there were some [counter arguments](/blog/why-we-need-named-params-in-php) to not adding them. However, I'd say their benefit far outweigh the fear of backwards compatibility problems or bloated APIs. The way I see it they will allow us to write cleaner and more flexible code.

For one, named arguments allow you to skip default values. Take a look again at the cookie example:

```php
<hljs prop>setcookie</hljs>(
    <hljs prop>name</hljs>: 'test',
    <hljs prop>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

Its method signature is actually the following:

```php
<hljs prop>setcookie</hljs> ( 
    <hljs type>string</hljs> $name, 
    <hljs type>string</hljs> $value = "", 
    <hljs type>int</hljs> $expires = 0, 
    <hljs type>string</hljs> $path = "", 
    <hljs type>string</hljs> $domain = "", 
    <hljs type>bool</hljs> $secure = false, 
    <hljs type>bool</hljs> $httponly = false,
) : <hljs type>bool</hljs>
```

In the example I showed, we didn't need to set the a cookie `$value`, but we did need to set an expiration time. Named arguments made this method call a little more concise:

```php
<hljs prop>setcookie</hljs>(
    'test',
    '',
    <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

<em class="center small">`setcookie` without named arguments</em>

```php
<hljs prop>setcookie</hljs>(
    <hljs prop>name</hljs>: 'test',
    <hljs prop>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

<em class="center small">`setcookie` with named arguments</em>

Besides skipping arguments with default values, there's also the benefit of having clarity about which variable does what. Something that's especially useful in functions with large method signatures. Now we could say that lots of arguments are usually a code smell; we still have to deal with them no matter what, so it's better to have a sane way of doing so, than nothing at all. 

## Named arguments in depth

With the basics out of the way, let's look at what named arguments can and can't do.

First of all, named arguments can be combined with unnamed — also called ordered — arguments. In that case the ordered arguments must always come first.  

Take our DTO example from before:

```php
class CustomerData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$name</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$email</hljs>,
        <hljs keyword>public</hljs> <hljs type>int</hljs> <hljs prop>$age</hljs>,
    ) {}
}
```

You could construct it like so:

```php
$data = new <hljs type>CustomerData</hljs>(
    $input['name'],
    <hljs prop>age</hljs>: $input['age'],
    <hljs prop>email</hljs>: $input['email'],
);
```

However, having an ordered argument after a named one would throw an error:

```php
$data = new <hljs type>CustomerData</hljs>(
    <hljs prop>age</hljs>: $input['age'],
    <hljs striped>$input['name'],</hljs>
    <hljs prop>email</hljs>: $input['email'],
);
```

---

Next, it's possible to use array spreading in combination with named arguments:

```php
$input = [
    'age' => 25,
    'name' => 'Brent',
    'email' => 'brent@stitcher.io',
];

$data = new <hljs type>CustomerData</hljs>(...$input);
```

_If_, however, there are missing required entries in the array, or if there's a key that's not listed as a named argument, an error will be thrown:

```php
$input = [
    'age' => 25,
    'name' => 'Brent',
    'email' => 'brent@stitcher.io',
    <hljs striped>'unknownProperty' => 'This is not allowed'</hljs>,
];

$data = new <hljs type>CustomerData</hljs>(<hljs striped>...$input</hljs>);
```

It _is_ possible to combine named and ordered arguments in an input array, but only if the ordered arguments follow the same rule as before: they must come first!

```php
$input = [
    'Brent',
    'age' => 25,
    'email' => 'brent@stitcher.io',
];

$data = new <hljs type>CustomerData</hljs>(...$input);
```

---

If you're using variadic functions, named arguments will be passed with their key name into the variadic arguments array. Take the following example:

```php
class CustomerData
{
    public static function new(...$args): self
    {
        return new self(...$args);
    }

    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$name</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$email</hljs>,
        <hljs keyword>public</hljs> <hljs type>int</hljs> <hljs prop>$age</hljs>,
    ) {}
}

$data = <hljs type>CustomerData</hljs>::<hljs prop>new</hljs>(
    <hljs prop>email</hljs>: 'brent@stitcher.io',
    <hljs prop>age</hljs>: 25,
    <hljs prop>name</hljs>: 'Brent',
);
```

In this case, `$args` in `<hljs type>CustomerData</hljs>::<hljs prop>new</hljs>` will contain the following data:

```php
[
    'age' => 25,
    'email' => 'brent@stitcher.io',
    'name' => 'Brent',
]
```

---

[Attributes](/blog/attributes-in-php-8) — also known as annotations — also support named arguments:

```php
class ProductSubscriber
{
    @@<hljs type>ListensTo</hljs>(<hljs prop>event</hljs>: <hljs type>ProductCreated</hljs>::class)
    public function onProductCreated(<hljs type>ProductCreated</hljs> $event) { /* … */ }
}
```

---

It's not possible to have a variable as a the argument name:

```php
$field = 'age';

$data = <hljs type>CustomerData</hljs>::<hljs prop>new</hljs>(
    <hljs striped>$field</hljs>: 25,
);
```

---

And finally, named arguments will deal in a pragmatic way with name changes during inheritance. Take this example:

```php
interface EventListener {
    public function on($event, $handler);
}

class MyListener implements EventListener
{
    public function on($myEvent, $myHandler)
    {
        // …
    }
}
```

PHP will silently allow changing the name of `$event` to `$myEvent`, and `$handler` to `$myHandler`; _but_ if you decide to use named arguments using the parent's name, it will result in a runtime error:

```php
public function register(EventLister $lister)
{
    $listener-><hljs prop>on</hljs>(
        <hljs striped prop>event</hljs>: $this-><hljs prop>event</hljs>,
        <hljs striped prop>handler</hljs>: $this-><hljs prop>handler</hljs>, 
    );
}
```

<em class="small center">Runtime error in case `$listener` is an instance of `<hljs type>MyListener</hljs>`</em>

This pragmatic approach was chosen to prevent a major breaking change when all inherited argument would have to keep the same name. Seems like a good solution to me.

---

That's most there is to tell about named arguments, if you want to know a little more backstory behind some design decisions, I'd encourage you to read [the RFC](https://wiki.php.net/rfc/named_params). 

Are you looking forward to using named arguments? Let me know via [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io)!
