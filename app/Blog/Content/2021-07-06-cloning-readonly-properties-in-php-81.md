---
title: 'PHP 8.1: cloning and changing readonly properties'
smallTitle: true
meta:
    description: "Readonly properties in PHP 8.1 can't be changed — or can they?"
footnotes:
    - { link: /blog/cloning-readonly-properties-in-php-83, title: 'Cloning readonly properties in PHP 8.3' }
    - { link: /blog/php-81-readonly-properties, title: 'An in-depth look at readonly properties' }
    - { link: 'https://github.com/spatie/php-cloneable', title: spatie/php-cloneable }
    - { link: /blog/new-in-php-81, title: "What's new in PHP 8.1" }
    - { link: 'https://wiki.php.net/rfc/readonly_properties_v2', title: 'The readonly properties RFC' }
    - { link: /blog/php-enums, title: 'Enums in PHP 8.1' }
    - { link: /blog/new-in-php-8, title: "What's new in PHP 8" }
---

**Note: PHP 8.3 adds a built-in way of cloning readonly properties, although it's rather limited in its possibilities. [Read more](/blog/cloning-readonly-properties-in-php-83).**

In [PHP 8.1](/blog/new-in-php-81), [readonly properties](/blog/php-81-readonly-properties) aren't allowed to be overridden as soon as they are initialized. That also means that cloning an object and changing one of its readonly properties isn't allowed. It's likely that PHP will get some kind of `<hljs keyword>clone with</hljs>` functionality in the future, but for now we'll have to work around the issue.

Let's imagine a simple DTO class with readonly properties:

```php
class Post
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>, 
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
    ) {}
}
```

PHP 8.1 would throw an error when you'd clone a post object and tried to override one of its readonly properties:

```php
$postA = new <hljs type>Post</hljs>(<hljs prop>title:</hljs> 'a', <hljs prop>author:</hljs> 'Brent');

$postB = clone $postA;
<hljs striped>$postB-><hljs prop>title</hljs> = 'b';</hljs>

<hljs error full>Error: Cannot modify readonly property <hljs type>Post</hljs>::<hljs prop>$title</hljs></hljs>
```

The reason why this happens is because the current readonly implementation will only allow a value to be set as long as it's [uninitialized](/blog/typed-properties-in-php-74#uninitialized). Since we're cloning an object that already had a value assigned to its properties, we cannot override it.

It's very likely PHP will add some kind of mechanism to clone objects and override readonly properties in the future, but with the feature freeze for PHP 8.1 coming up, we can be certain this won't be included for now.

So, at least for PHP 8.1, we'll need a way around this issue. Which is exactly what I did, and why I created a package that you can use as well: [https://github.com/spatie/php-cloneable](*https://github.com/spatie/php-cloneable).

{{ cta:dynamic }}

Here's how it works. First you download the package using composer, and next use the `<hljs type>Spatie\Cloneable\Cloneable</hljs>` trait in all classes you want to be cloneable:

```php
<hljs green>use <hljs type>Spatie\Cloneable\Cloneable</hljs>;</hljs>

class Post
{
    <hljs green>use <hljs type>Cloneable</hljs>;</hljs>
    
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>, 
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>
    ) {}
}
```

Now our `<hljs type>Post</hljs>` objects will have a `<hljs prop>with</hljs>` method that you can use to clone _and_ override properties with:

```php
$postA = new Post(<hljs prop>title:</hljs> 'a', <hljs prop>author:</hljs> 'Brent');

$postB = $postA-><hljs prop>with</hljs>(<hljs prop>title:</hljs> 'b');
$postC = $postA-><hljs prop>with</hljs>(<hljs prop>title:</hljs> 'c', <hljs prop>author:</hljs> 'Freek');
```

There are of course a few caveats:

- this package will skip calling the constructor when cloning an object, meaning any logic in the constructor won't be executed; and
- the `<hljs prop>with</hljs>` method will be a shallow clone, meaning that nested objects aren't cloned as well.

I imagine this package being useful for simple data-transfer and value objects; which are exactly the types of objects that readonly properties were designed for to start with.

For my use cases, this implementation will suffice. And since I believe in [opinion-driven design](/blog/opinion-driven-design), I'm also not interested in added more functionality to it: this package solves one specific problem, and that's good enough.

{{ cta:like }}

{{ cta:mail }}
