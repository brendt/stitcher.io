It was a [close call](/blog/why-we-need-named-params-in-php), but named arguments — also called named parameters — are supported in [PHP 8](/blog/new-in-php-8)! In this post I'll discuss their ins and outs, but let me show you first what they look like, a few examples in the wild:

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

<em class="center small"><code>setcookie</code> without named arguments</em>

```php
<hljs prop>setcookie</hljs>(
    <hljs prop>name</hljs>: 'test',
    <hljs prop>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

<em class="center small"><code>setcookie</code> with named arguments</em>

Besides skipping arguments with default values, there's also the benefit of having clarity about which variable does what.

## Named arguments in depth

- Attributes
- Array spreading
- Variable names are not supported

## When will named arguments throw errors

- Error handling
