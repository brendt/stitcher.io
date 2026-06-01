---
title: 'PHP 8 in 8 code blocks'
next: new-in-php-8
meta:
    description: 'The best features of PHP 8'
    template: blog/meta/php-8-in-8-codeblocks.twig
footnotes:
    - { link: /blog/new-in-php-8, title: 'New in PHP 8', description: ' — A comprehensive list of all things new in PHP 8' }
    - { link: /blog/attributes-in-php-8, title: 'Attributes in PHP 8', description: ' — A close look at attributes, also known as annotations' }
    - { link: /blog/php-jit, title: 'The JIT in PHP 8', description: ' — A close look at the JIT, and what it means for PHP' }
---

PHP 8 brings lots of new features, in this list we'll look at the most outstanding ones. If you want a full list and background information, you can read about all things [new in PHP 8](/blog/new-in-php-8).

{{ ad:carbon }}

---

```php
use Support\Attributes\ListensTo;

class ProductSubscriber
{
    #[ListensTo(ProductCreated::class)]
    public function onProductCreated(ProductCreated $event) { /* … */ }

    #[ListensTo(ProductDeleted::class)]
    public function onProductDeleted(ProductDeleted $event) { /* … */ }
}
```

Attributes — aka annotations — you can read about them in depth in [this post](/blog/attributes-in-php-8).

---

```php
public function foo(Foo|Bar $input): int|float;

public function bar(mixed $input): mixed;
```

Union types allows for type hinting several types. There's also a new `mixed` type which represents [several types](/blog/new-in-php-8#new-mixed-type-rfc) at once.

---

```php
interface Foo
{
    public function bar(): static;
}
```

The `static` return type is built-in.

---

```ini
[JIT]
opcache.jit=1225
```

[The just-in-time compiler](/blog/php-jit) for PHP.

---

```php
$triggerError = fn() => throw new MyError();

$foo = $bar['offset'] ?? throw new OffsetDoesNotExist('offset');
```

`throw` can be used in expressions.

---

```php
try {
    // Something goes wrong
} catch (MySpecialException) {
    Log::error("Something went wrong");
}
```

Non-capturing catches: no need to specify an exception variable if you don't need it.

---

```php
setcookie(
    name: 'test',
    expires: time() + 60 * 60 * 2,
);
```

[Named arguments](/blog/php-8-named-arguments).

---

```php
$result = match($input) {
    0 => "hello",
    '1', '2', '3' => "world",
};
``` 

The `match` expression as an improvement to the `switch` expression.

{{ cta:dynamic }}

There's even more. If you want a full list, you can find it [on this blog](/blog/new-in-php-8).

What feature are you looking forward to the most? Let me know on [Twitter](*https://twitter.com/brendt_gd) or via [email](mailto:brendt@stitcher.io).
