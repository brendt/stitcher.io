---
title: 'Readonly classes in PHP 8.2'
next: new-in-php-82
footnotes:
    - { link: /blog/cloning-readonly-properties-in-php-83, title: 'Cloning readonly properties in PHP 8.3' }
    - { link: /blog/php-82-upgrade-mac, title: 'How to upgrade to PHP 8.2 on Mac' }
    - { link: /blog/new-in-php-82, title: 'New in PHP 8.2', description: ' — A comprehensive list of all things new in PHP 8.2' }
    - { link: /blog/deprecated-dynamic-properties-in-php-82, title: 'Deprecated dynamic properties in PHP 8.2' }
    - { link: /blog/php-82-in-8-code-blocks, title: 'PHP 8.2 in 8 code blocks' }
    - { link: 'https://road-to-php.com/', title: 'The Road to PHP 8.2' }
---

PHP 8.2 adds a new way of declaring classes: you can make them readonly. In practice, it means that all properties of that class will be readonly. This is especially useful when you're using [data transfer objects](/blog/structuring-unstructured-data) or value objects, where a class only has public [readonly properties](/blog/php-81-readonly-properties).

<iframe width="560" height="422" src="https://www.youtube.com/embed/2cyJq08q6xE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

In other words, instead of writing this:

```php
class BlogData
{
    public function __construct(
        public readonly string $title,
        public readonly Status $status,
        public readonly ?DateTimeImmutable $publishedAt = null,
    ) {}
}
```

You can now write this:

```php
readonly class BlogData
{
    public function __construct(
        public string $title,
        public Status $status,
        public ?DateTimeImmutable $publishedAt = null,
    ) {}
}
```

I've written about readonly properties before, so let's quickly summarise first:

- readonly properties can only be written once — usually in the constructor;
- only typed properties can be made readonly;
- they cannot have a default value (unless you're using promoted properties);
- the `readonly` flag cannot be changed during inheritance; and finally
- you cannot unset readonly properties.

Since readonly classes are merely syntactic sugar for making all properties of that class readonly, it means that the same rules apply to readonly classes as well.

### Write once

All properties of a readonly class can only be written once, and can not be unset:

```php
readonly class BlogData { /* … */ }

$blogData = new BlogData(/* … */);

$blogData->title = 'other';

unset($blogData->title);
```

### Only typed properties

A readonly class can only have typed properties:

```php
readonly class BlogData
{
    public string $title;
    
    public $mixed;
}
```

### No static properties

Since readonly properties cannot be static, readonly classes cannot have any static properties:

```php
readonly class BlogData
{
    public static string $title;
}
```

### No default values

Properties of a readonly class can not have a default value unless you're using promoted properties:

```php
readonly class BlogData
{
    public string $title = 'default';
}
```

```php
readonly class BlogData
{
    public function __construct(
        public string $title = 'default', // This works
   ) {}
}
```

### No changes during inheritance

You cannot change the `readonly` class flag during inheritance:

```php
readonly class BlogData { /* … */ }

class NewsItemData extends BlogData { /* … */ }
```

### No dynamic properties

Readonly classes also don't allow dynamic properties. This won't have a big impact since [dynamic properties are deprecated](/blog/deprecated-dynamic-properties-in-php-82) in PHP 8.2 anyway, but means that you cannot add the `#[AllowDynamicProperties]` attribute to readonly classes:

```php
#[AllowDynamicProperties]
readonly class BlogData { /* … */ }
```

### Reflection

Finally, there's a new reflection method to determine whether a class is readonly: `ReflectionClass::isReadOnly()`. You can also use `ReflectionClass::getModifiers()`, which will include the `ReflectionClass::IS_READONLY` flag.

---

{{ cta:dynamic }}
