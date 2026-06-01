---
title: 'PHP 8: Attributes'
next: new-in-php-8
meta:
    description: 'A close look at attributes, also known as annotations; in PHP 8'
footnotes:
    - { link: 'https://wiki.php.net/rfc/shorter_attribute_syntax_change#voting', title: '#[] was voted to be the final syntax' }
    - { link: 'https://wiki.php.net/rfc/attribute_amendments', title: 'Amendments to the original RFC' }
    - { link: /blog/new-in-php-8, title: 'New in PHP 8', description: ' — A comprehensive list of all things new in PHP 8' }
    - { link: /blog/php-jit, title: 'The JIT in PHP 8', description: ' — A close look at the JIT, and what it means for PHP' }
---

As of PHP 8, we'll be able to use attributes. The goal of these attributes, also known as annotations in many other languages, is to add meta data to classes, methods, variables and what not; in a structured way.

The concept of attributes isn't new at all, we've been using docblocks to simulate their behaviour for years now. With the addition of attributes though, we now have a first-class citizen in the language to represent this kind of meta data, instead of having to manually parse docblocks.

So what do they look like? How do we make custom attributes? Are there any caveats? Those are the questions that will be answered in this post. Let's dive in!

{{ ad:carbon }}

## Rundown

First things first, here's what attribute would look like in the wild:

```php
use \Support\Attributes\ListensTo;

class ProductSubscriber
{
    #[ListensTo(ProductCreated::class)]
    public function onProductCreated(ProductCreated $event) { /* … */ }

    #[ListensTo(ProductDeleted::class)]
    public function onProductDeleted(ProductDeleted $event) { /* … */ }
}
```

I'll be showing other examples later in this post, but I think the example of event subscribers is a good one to explain the use of attributes at first.

Also yes, I know, the syntax might not be what you wished or hoped for. You might have preferred `@`, or `@:`, or docblocks or, … It's here to stay though, so we better learn to deal with it. The only thing that's worth mentioning on the syntax is that all options were discussed, and there are very good reasons why this syntax was chosen. You can read the whole discussion about the RFC on the [internals list](*https://externals.io/message/110640).

That being said, let's focus on the cool stuff: how would this `ListensTo` work under the hood?

First of all, custom attributes are simple classes, annotated themselves with the `#[Attribute]` attribute; this base `Attribute` used to be called `PhpAttribute` in the original RFC, but was changed with [another RFC](*https://wiki.php.net/rfc/attribute_amendments) afterwards.

Here's what it would look like:

```php
#[Attribute]
class ListensTo
{
    public string $event;

    public function __construct(string $event)
    {
        $this->event = $event;
    }
}
```

That's it — pretty simple right? Keep in mind the goal of attributes: they are meant to add meta data to classes and methods, nothing more. They shouldn't — and can't — be used for, for example, argument input validation. In other words: you wouldn't have access to the parameters passed to a method within its attributes. There was a previous RFC that allowed this behaviour, but this RFC specifically kept things more simple.

{{ cta:dynamic }}

Back to the event subscriber example: we still need to read the meta data and register our subscribers based somewhere. Coming from a Laravel background, I'd use a service provider as the place to do this, but feel free to come up with other solutions.

Here's the boring boilerplate setup, just to provide a little context:

```php
class EventServiceProvider extends ServiceProvider
{
    // In real life scenarios, 
    //  we'd automatically resolve and cache all subscribers
    //  instead of using a manual array.
    private array $subscribers = [
        ProductSubscriber::class,
    ];

    public function register(): void
    {
        // The event dispatcher is resolved from the container
        $eventDispatcher = $this->app->make(EventDispatcher::class);

        foreach ($this->subscribers as $subscriber) {
            // We'll resolve all listeners registered 
            //  in the subscriber class,
            //  and add them to the dispatcher.
            foreach (
                $this->resolveListeners($subscriber) 
                as [$event, $listener]
            ) {
                $eventDispatcher->listen($event, $listener);
            }       
        }       
    }
}
```

Note that if the `[$event, $listener]` syntax is unfamiliar to you, you can get up to speed with it in my post about [array destructuring](/blog/array-destructuring-with-list-in-php#in-loops).

Now let's look at `resolveListeners`, which is where the magic happens.

```php
private function resolveListeners(string $subscriberClass): array
{
    $reflectionClass = new ReflectionClass($subscriberClass);

    $listeners = [];

    foreach ($reflectionClass->getMethods() as $method) {
        $attributes = $method->getAttributes(ListensTo::class);
        
        foreach ($attributes as $attribute) {
            $listener = $attribute->newInstance();
            
            $listeners[] = [
                // The event that's configured on the attribute
                $listener->event,
    
                // The listener for this event 
                [$subscriberClass, $method->getName()],
            ];
        }
    }

    return $listeners;
}
```

You can see it's easier to read meta data this way, compared to parsing docblock strings. There are two intricacies worth looking into though.

First there's the `$attribute->newInstance()` call. This is actually the place where our custom attribute class is instantiated. It will take the parameters listed in the attribute definition in our subscriber class, and pass them to the constructor. 

This means that, technically, you don't even need to construct the custom attribute. You could call `$attribute->getArguments()` directly. Furthermore, instantiating the class means you've got the flexibility of the constructor the parse input whatever way you like. All in all I'd say it would be good to always instantiate the attribute using `newInstance()`.

The second thing worth mentioning is the use of `ReflectionMethod::getAttributes()`, the function that returns all attributes for a method. You can pass two arguments to it, to filter its output.

In order to understand this filtering though, there's one more thing you need to know about attributes first. This might have been obvious to you, but I wanted to mention it real quick anyway: it's possible to add several attributes to the same method, class, property or constant.

You could, for example, do this:

```php
#[
    Route(Http::POST, '/products/create'),
    Autowire,
]
class ProductsCreateController
{
    public function __invoke() { /* … */ }
}
```

With that in mind, it's clear why `Reflection*::getAttributes()` returns an array, so let's look at how its output can be filtered.

Say you're parsing controller routes, you're only interested in the `Route` attribute. You can easily pass that class as a filter:

```php
$attributes = $reflectionClass->getAttributes(Route::class);
```

The second parameter changes how that filtering is done. You can pass in `ReflectionAttribute::IS_INSTANCEOF`, which will return all attributes implementing a given interface.

For example, say you're parsing container definitions, which relies on several attributes, you could do something like this:

```php
$attributes = $reflectionClass->getAttributes(
    ContainerAttribute::class, 
    ReflectionAttribute::IS_INSTANCEOF
);
```

It's a nice shorthand, built into the core.

{{ cta:mail }}

## Technical theory

Now that you have an idea of how attributes work in practice, it's time for some more theory, making sure you understand them thoroughly. First of all, I mentioned this briefly before, attributes can be added in several places.

In classes, as well as anonymous classes;

```php
#[ClassAttribute]
class MyClass { /* … */ }

$object = new #[ObjectAttribute] class () { /* … */ };
```

Properties and constants;

```php
#[PropertyAttribute]
public int $foo;

#[ConstAttribute]
public const BAR = 1;
```

Methods and functions;

```php
#[MethodAttribute]
public function doSomething(): void { /* … */ }

#[FunctionAttribute]
function foo() { /* … */ }
```

As well as closures;

```php
$closure = #[ClosureAttribute] fn() => /* … */;
```

And method and function parameters;

```php
function foo(#[ArgumentAttribute] $bar) { /* … */ }
```

They can be declared before or after docblocks;

```php
/** @return void */
#[MethodAttribute]
public function doSomething(): void { /* … */ }
```

And can take no, one or several arguments, which are defined by the attribute's constructor:

```php
#[Listens(ProductCreatedEvent::class)]
#[Autowire]
#[Route(Http::POST, '/products/create')]
```

As for allowed parameters you can pass to an attribute, you've already seen that class constants, `::class` names and scalar types are allowed. There's a little more to be said about this though: attributes only accept constant expressions as input arguments.

This means that scalar expressions are allowed — even bit shifts — as well as `::class`, constants, arrays and array unpacking, boolean expressions and the null coalescing operator. A list of everything that's allowed as a constant expression can be found in the [source code](*https://github.com/php/php-src/blob/9122638ecd7dfee1cbd141a15a8d59bfc47f6ab3/Zend/zend_compile.c#L8500-L8514).

```php
#[AttributeWithScalarExpression(1 + 1)]
#[AttributeWithClassNameAndConstants(PDO::class, PHP_VERSION_ID)]
#[AttributeWithClassConstant(Http::POST)]
#[AttributeWithBitShift(4 >> 1, 4 << 1)]
```

## Attribute configuration

By default, attributes can be added in several places, as listed above. It's possible, however, to configure them so they can only be used in specific places. For example you could make it so that `ClassAttribute` can only be used on classes, and nowhere else. Opting-in this behaviour is done by passing a flag to the `Attribute` attribute on the attribute class.

It looks like this:

```php
#[Attribute(Attribute::TARGET_CLASS)]
class ClassAttribute
{
}
```

The following flags are available:

```php
Attribute::TARGET_CLASS
Attribute::TARGET_FUNCTION
Attribute::TARGET_METHOD
Attribute::TARGET_PROPERTY
Attribute::TARGET_CLASS_CONSTANT
Attribute::TARGET_PARAMETER
Attribute::TARGET_ALL
```

These are bitmask flags, so you can combine them [using a binary OR operation](/blog/bitwise-booleans-in-php).

```php
#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class ClassAttribute
{
}
```

Another configuration flag is about repeatability. By default the same attribute can't be applied twice, unless it's specifically marked as repeatable. This is done the same way as target configuration, with a bit flag. 

```php
#[Attribute(Attribute::IS_REPEATABLE)]
class ClassAttribute
{
}
```

Note that all these flags are only validated when calling `$attribute->newInstance()`, not earlier.


## Built-in attributes

Once the base RFC had been accepted, new opportunities arose to add built-in attributes to the core. One such example is the [`#[Deprecated]`](*https://wiki.php.net/rfc/deprecated_attribute) attribute, and a popular example has been a `#[Jit]` attribute — if you're not sure what that last one is about, you can read my post about [what the JIT is](/blog/php-jit).

I'm sure we'll see more and more built-in attributes in the future.

As a final note, for those worrying about generics: the syntax won't conflict with them, if they ever were to be added in PHP, so we're safe!

---

{{ cta:dynamic }}

---

I've got some use-cases already in mind for attributes, what about you? If you've got some thoughts to share about this awesome new feature in PHP 8, you can reach me on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io), or we can discuss it over on [Reddit](*https://www.reddit.com/r/PHP/comments/gixnf3/attributes_in_php_8/).
