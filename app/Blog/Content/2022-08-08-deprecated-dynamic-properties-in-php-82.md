---
title: 'Deprecated dynamic properties in PHP 8.2'
footnotes:
    - { link: /blog/new-in-php-82, title: "What's new in PHP 8.2" }
    - { link: /blog/php-82-upgrade-mac, title: 'How to upgrade to PHP 8.2 on Mac' }
    - { link: /blog/deprecated-dynamic-properties-in-php-82, title: 'Deprecated dynamic properties in PHP 8.2' }
    - { link: /blog/readonly-classes-in-php-82, title: 'Readonly classes in PHP 8.2' }
    - { link: /blog/php-82-in-8-code-blocks, title: 'PHP 8.2 in 8 code blocks' }
    - { link: /blog/uncertainty-doubt-and-static-analysis, title: 'Uncertainty, doubt, and static analysis' }
    - { link: 'https://road-to-php.com/', title: 'The Road to PHP 8.2' }
---

As is common with minor releases, [PHP 8.2](/blog/new-in-php-82) adds some deprecations. Deprecations often are a source of frustration, though it's important to realise they are actually very helpful. I already wrote about [dealing with deprecations](/blog/dealing-with-deprecations) in general, so if you're already feeling frustrated, maybe it's good to take a look at that post first. Today, I want to focus on one deprecation in particular in PHP 8.2: deprecated dynamic properties.

So first things first, what are dynamic properties exactly? Well, they are properties that aren't present on a class' definition, but are set on objects of those classes dynamically, at runtime.

For example this `Post` class doesn't have a `name` property, but nevertheless we set it at runtime:

```php
class Post
{
}

// …

$post = new Post();

$post->name = 'Name';

var_dump($post->name); // 'Name'
```

As of PHP 8.2, these dynamic properties will be deprecated:

```php
// …

$post->name = 'Name';
```

You'll see this message: `Deprecated: Creation of dynamic property Post::$name is deprecated`.

{{ ad:carbon }}

## Implementing `__get` and `__set` still works!

You might be panicking at this point, because dynamic properties are a big part of meta programming in PHP — many frameworks rely on it!

Not to worry: this new deprecation won't affect any class that implements `__get` and `__set`. Classes that implement these magic methods will keep working as intended:

```php
class Post
{
    private array $properties = [];

    public function __set(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    // …
}

// …

$post->name = 'Name';
```

The same goes for objects of `stdClass`, they will support dynamic properties just as before:

```php
$object = new stdClass();

$object->name = 'Name'; // Works fine in PHP 8.2
```

Now some clever readers might wonder: if `stdClass` still allows dynamic properties, what would happen if you'd extend from it?

Indeed, it _is_ possible to extend from `stdClass` to prevent the deprecation notice from being shown. However, I'd say this solution is far from ideal:

```php
// Don't do this

class Post extends stdClass
{
}

$post = new Post();

$post->name = 'Name'; // Works in PHP 8.2
```

{{ cta:dynamic }}

## A better alternative

If you _really_ want to use dynamic properties without implementing `__get` and `__set`, there is a much better alternative than to extend from `stdClass`.

The PHP core team has provided a built-in [attribute](/blog/attributes-in-php-8) called `AllowDynamicProperties`. As its name suggests, it allows dynamic properties on classes, without having to rely on sketchy extends:

```php
#[\AllowDynamicProperties]
class Post
{
}

$post = new Post();

$post->name = 'Name'; // All fine
```

## Closing thoughts

PHP used to be a very dynamic language, but has been moving away from that mindset for a while now. Personally I think it's a good thing to embrace stricter rules and rely on static analysis wherever possible, as I find it leads to writing better code.

I can imagine developers who relied on dynamic properties, who are less happy with this change. If you're in that group, you might find it useful to take a closer look into static analysis. You can check out my [Road to PHP: Static Analysis](https://road-to-php.com/static) series if you want to learn more!

_If_ you're willing to invest some time in figuring out static analysis, I'm fairly certain that most of you won't ever want to return back to the mess that is a dynamic programming language. With PHP we're lucky that both options are available and that you can migrate gradually towards a stricter type system.

So, yes: this deprecation might be a little painful, but I believe it's for the best of the language to do so. And remember that it won't be a fatal error until PHP 9.0, so there's plenty of time to deal with it.

{{ cta:like }}

{{ cta:mail }}
