---
title: The call-site problem
---

Pop-quiz; you have this function:

```php
function output(string $line): void
{
    echo $line . PHP_EOL;
}
```

And you pass an integer to it:

```php
output(10);
```

What will happen?

PHP will type-juggle that integer to a string just fine:

```php
output(10);

// echo "10" . PHP_EOL;
```

Since the addition of type hints in PHP 7, though, there’s been an option to disable this type juggling:

```php
declare(strict_types=1);

function output(string $line): void
{
    echo $line . PHP_EOL;
}

output(10);

// Fatal error: Uncaught TypeError: output():
// Argument #1 ($line) must be of type string, int given
```

Now whether you prefer to use strict_types or not is up to you. You could make an argument for both sides, and that's ok. 

Pop-quiz number 2: the `{php}output()` function is now defined in a separate file (which is the more common scenario):

```php
// vendor/tempest/output/functions.php

declare(strict_types=1);

function output(string $line): void
{
    echo $line . PHP_EOL;
}
```

And you call it from another place:

```php
// index.php

require_once __DIR__ . '/vendor/autoload.php;'

output(10);
```

What do you think will happen? Since I'm spending all this time on setting the scene, you probably know it will be something weird. Indeed; this function call will work just fine:


```php
// index.php

require_once __DIR__ . '/vendor/autoload.php;'

output(10);

// echo "10" . PHP_EOL;
```

That's because `{php}declare(strict_types=1)` is enforced at the _call-site_ instead of _declaration-site_. In other words: strict types are only checked when you're calling a function, not when a function is called. For the past ten years, this behavior has come as a surprise to many. 

The reason strict types were designed this way can be summarized in one word: compromise. I don't think that's necessarily a bad thing, because we likely wouldn't have any types at all if it wasn't for this compromise. The [scalar types RFC](https://wiki.php.net/rfc/scalar_type_hints_v5) needed as many as five iterations before being accepted in PHP. There's a rich history of why strict types were designed the way they were, and this blog post isn't meant to be an argument for or against them.

Then why am I telling you all of this? Well, let's imagine a new feature in PHP: an opt-in way to disable PHP's runtime type checker. Think of it as "strict types times ten": a `{php}declare` flag that tells PHP "this part of my code has been type checked by a static analyzer, no need to do runtime type checks anymore". There are [many reasons](/blog/we-dont-need-runtime-type-checks) why you'd want that, but the most obvious one for me is that this mechanism would open the door for proper generics in PHP; time and time again, internals have tried adding runtime generics in PHP, and time and time again it turned out to be not feasible. Luckily, generics are by nature most useful for static analysis and metaprogramming, both of which don't need runtime generic type checks. 

In short: being able to disable PHP's runtime type checker for specific parts means we could add generics.

Except for: the call-site problem. Let's imagine the following code:

```php
final class SelectQueryBuilder<TModel>
{
    public function all(): ImmutableArray<TModel>
    {
        $table = reflect($this)->getGenericType();
        
        return new QueryBuilder()->select('*')->from($table);
    }
}
```


> https://wiki.php.net/rfc/scalar_type_hints_v5#discussion_points
> https://externals.io/message/80442#80475
> https://externals.io/message/83580
> https://externals.io/message/80442
> https://externals.io/message/83011
> https://externals.io/message/83117