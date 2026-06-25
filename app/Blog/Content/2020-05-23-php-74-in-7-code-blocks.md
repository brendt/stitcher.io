---
title: 'PHP 7.4 in 7 code blocks'
next: php-8-in-8-code-blocks
meta:
    description: 'The best features of PHP 7.4'
    image: resources/img/blog/php-74/meta.png
footnotes:
    - { link: /blog/new-in-php-74, title: 'New in PHP 7.4', description: ' — A comprehensive list of all things new in PHP 7.4' }
    - { link: /blog/short-closures-in-php, title: 'Short closures in PHP 7.4' }
    - { link: /blog/typed-properties-in-php-74, title: 'Typed properties in PHP 7.4' }
    - { link: /blog/liskov-and-type-safety, title: 'Liskov and type safety' }
    - { link: /blog/php-8-in-8-code-blocks, title: 'PHP 8 in 8 code blocks', description: ' — The best features of PHP 8' }
    - { link: /blog/preloading-in-php-74, title: 'Preloading in PHP 7.4' }
---

PHP 7.4, the last edition in the 7.* series, brings lots of new and handy changes. This post lists the highlights, though there's much more to this release. You can read all about the full release in this post about [what's new in PHP 7.4](/blog/new-in-php-74).

{{ ad:carbon }}

---

```php
array_map(
    fn(User $user) => $user->id,
    $users
);
```

Arrow functions, a.k.a. short closures. You can read about them in depth in [this post](/blog/short-closures-in-php).

---

```php
class A
{
    public string $name;
    
    public ?Foo $foo;
}
```

Type properties. There's quite a lot to tell [about them](/blog/typed-properties-in-php-74).

---

```php
$data['date'] ??= new DateTime();
```

The null coalescing assignment operator. If you're unfamiliar with the null coalescing operator, you can read all about [shorthand operators](/blog/shorthand-comparisons-in-php) in this blog.

---

```php
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    { /* … */ }
}

class B extends A
{
    public function covariantReturnTypes(): ChildType
    { /* … */ }
}
```

Improved type variance. If you're not sure what that's about, you should take a look at this post about [Liskov and type safety](/blog/liskov-and-type-safety).

---

```php
$result = [...$arrayA, ...$arrayB];
```

The array spread operator. There are a few [sidenotes](/blog/new-in-php-74#array-spread-operator-rfc) to be made about them.

---

```php
$formattedNumber = 107_925_284.88;
```

The numeric literal separator, which is only a visual aid.

---

```ini
[preloading]
opcache.preload=/path/to/project/preload.php
```

Preloading improves PHP performance across requests. It's a complicated topic, but I wrote about it [here](/blog/preloading-in-php-74).

{{ cta:mail }}
