As of PHP 8, we'll be able to use attributes. The goal of these attributes, also known as annotations in many other languages, is to add meta data to classes, methods, variables and what not; in a structured way.

The concept of attributes isn't new at all, we've been using docblocks to simulate their behaviour for years now. With the addition of attributes though, we now have a first-class citizen in the language to represent this kind of meta data, instead of having to manually parse docblocks.

So what do they look like? How do we make custom attributes? Are there any caveats? Those are the questions that will be answered in this post. Let's dive in!

{{ ad:carbon }}

## Rundown

<div class="author footnotes">
<p>

Note that the attribute syntax can still change, it's still [undecided](*https://wiki.php.net/rfc/shorter_attribute_syntax_change#voting).

</p>
</div>

First things first, here's what attribute would look like in the wild:

```php
use <hljs type>\Support\Attributes\ListensTo</hljs>;

class ProductSubscriber
{
    @@<hljs type>ListensTo</hljs>(<hljs type>ProductCreated</hljs>::class)
    public function onProductCreated(<hljs type>ProductCreated</hljs> $event) { /* … */ }

    @@<hljs type>ListensTo</hljs>(<hljs type>ProductDeleted</hljs>::class)
    public function onProductDeleted(<hljs type>ProductDeleted</hljs> $event) { /* … */ }
}
```

I'll be showing other examples later in this post, but I think the example of event subscribers is a good one to explain the use of attributes at first.

Also yes, I know, the syntax might not be what you wished or hoped for. You might have preferred `@`, or `@:`, or docblocks or, … It's here to stay though, so we better learn to deal with it. The only thing that's worth mentioning on the syntax is that all options were discussed, and there are very good reasons why this syntax was chosen. You can read the whole discussion about the RFC on the [internals list](*https://externals.io/message/110640).

That being said, let's focus on the cool stuff: how would this `ListensTo` work under the hood?

First of all, custom attributes are simple classes, annotated themselves with the `@@Attribute` attribute; this base `Attribute` used to be called `PhpAttribute` in the original RFC, but was changed with [another RFC](*https://wiki.php.net/rfc/attribute_amendments) afterwards.

Here's what it would look like:

```php
@@<hljs type>Attribute</hljs>
class ListensTo
{
    public <hljs type>string</hljs> $event;

    public function __construct(<hljs type>string</hljs> $event)
    {
        $this->event = $event;
    }
}
```

That's it — pretty simple right? Keep in mind the goal of attributes: they are meant to add meta data to classes and methods, nothing more. They shouldn't — and can't — be used for, for example, argument input validation. In other words: you wouldn't have access to the parameters passed to a method within its attributes. There was a previous RFC that allowed this behaviour, but this RFC specifically kept things more simple.



Back to the event subscriber example: we still need to read the meta data and register our subscribers based somewhere. Coming from a Laravel background, I'd use a service provider as the place to do this, but feel free to come up with other solutions.

Here's the boring boilerplate setup, just to provide a little context:

```php
class EventServiceProvider extends ServiceProvider
{
    // In real life scenarios, 
    //  we'd automatically resolve and cache all subscribers
    //  instead of using a manual array.
    private <hljs type>array</hljs> $subscribers = [
        <hljs type>ProductSubscriber</hljs>::class,
    ];

    public function register(): void
    {
        // The event dispatcher is resolved from the container
        $eventDispatcher = $this-><hljs prop>app</hljs>-><hljs prop>make</hljs>(<hljs type>EventDispatcher</hljs>::class);

        foreach ($this-><hljs prop>subscribers</hljs> as $subscriber) {
            // We'll resolve all listeners registered 
            //  in the subscriber class,
            //  and add them to the dispatcher.
            foreach (
                $this-><hljs prop>resolveListeners</hljs>($subscriber) 
                as [$event, $listener]
            ) {
                $eventDispatcher-><hljs prop>listen</hljs>($event, $listener);
            }       
        }       
    }
}
```

Note that if the `[$event, $listener]` syntax is unfamiliar to you, you can get up to speed with it in my post about [array destructuring](/blog/array-destructuring-with-list-in-php#in-loops).

Now let's look at `resolveListeners`, which is where the magic happens.

```php
private function resolveListeners(<hljs type>string</hljs> $subscriberClass): array
{
    $reflectionClass = new <hljs type>ReflectionClass</hljs>($subscriberClass);

    $listeners = [];

    foreach ($reflectionClass-><hljs prop>getMethods</hljs>() as $method) {
        $attributes = $method-><hljs prop>getAttributes</hljs>(<hljs type>ListensTo</hljs>::class);
        
        foreach ($attributes as $attribute) {
            $listener = $attribute-><hljs prop>newInstance</hljs>();
            
            $listeners[] = [
                // The event that's configured on the attribute
                $listener-><hljs prop>event</hljs>,
    
                // The listener for this event 
                [$subscriberClass, $method-><hljs prop>getName</hljs>()],
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
@@<hljs type>Route</hljs>(<hljs type>Http</hljs>::<hljs prop>POST</hljs>, '/products/create')
@@<hljs type>Autowire</hljs>
class ProductsCreateController
{
    public function __invoke() { /* … */ }
}
```

With that in mind, it's clear why `Reflection*::getAttributes()` returns an array, so let's look at how its output can be filtered.

Say you're parsing controller routes, you're only interested in the `Route` attribute. You can easily pass that class as a filter:

```php
$attributes = $reflectionClass-><hljs prop>getAttributes</hljs>(<hljs type>Route</hljs>::class);
```

The second parameter changes how that filtering is done. You can pass in `ReflectionAttribute::IS_INSTANCEOF`, which will return all attributes implementing a given interface.

For example, say you're parsing container definitions, which relies on several attributes, you could do something like this:

```php
$attributes = $reflectionClass-><hljs prop>getAttributes</hljs>(
    <hljs type>ContainerAttribute</hljs>::class, 
    <hljs type>ReflectionAttribute</hljs>::<hljs prop>IS_INSTANCEOF</hljs>
);
```

It's a nice shorthand, built into the core.

## Technical theory

Now that you have an idea of how attributes work in practice, it's time for some more theory, making sure you understand them thoroughly. First of all, I mentioned this briefly before, attributes can be added in several places.

In classes, as well as anonymous classes;

```php
@@<hljs type>ClassAttribute</hljs>
class MyClass { /* … */ }

$object = new @@<hljs type>ObjectAttribute</hljs> class () { /* … */ };
```

Properties and constants;

```php
@@<hljs type>PropertyAttribute</hljs>
public <hljs type>int</hljs> $foo;

@@<hljs type>ConstAttribute</hljs>
public const BAR = 1;
```

Methods and functions;

```php
@@<hljs type>MethodAttribute</hljs>
public function doSomething(): void { /* … */ }

@@<hljs type>FunctionAttribute</hljs>
function foo() { /* … */ }
```

As well as closures;

```php
$closure = @@<hljs type>ClosureAttribute</hljs> <hljs keyword>fn</hljs>() => /* … */;
```

And method and function parameters;

```php
function foo(@@<hljs type>ArgumentAttribute</hljs> $bar) { /* … */ }
```

They can be declared before or after docblocks;

```php
/** @return void */
@@<hljs type>MethodAttribute</hljs>
public function doSomething(): void { /* … */ }
```

And can take no, one or several arguments, which are defined by the attribute's constructor:

```php
@@<hljs type>Listens</hljs>(<hljs type>ProductCreatedEvent</hljs>::class)
@@<hljs type>Autowire</hljs>
@@<hljs type>Route</hljs>(<hljs type>Http</hljs>::<hljs prop>POST</hljs>, '/products/create')
```

As for allowed parameters you can pass to an attribute, you've already seen that class constants, `::class` names and scalar types are allowed. There's a little more to be said about this though: attributes only accept constant expressions as input arguments.

This means that scalar expressions are allowed — even bit shifts — as well as `::class`, constants, arrays and array unpacking, boolean expressions and the null coalescing operator. A list of everything that's allowed as a constant expression can be found in the [source code](*https://github.com/php/php-src/blob/9122638ecd7dfee1cbd141a15a8d59bfc47f6ab3/Zend/zend_compile.c#L8500-L8514).

```php
@@<hljs type>AttributeWithScalarExpression</hljs>(1 + 1)
@@<hljs type>AttributeWithClassNameAndConstants</hljs>(<hljs type>PDO</hljs>::class, <hljs prop>PHP_VERSION_ID</hljs>)
@@<hljs type>AttributeWithClassConstant</hljs>(<hljs type>Http</hljs>::<hljs prop>POST</hljs>)
@@<hljs type>AttributeWithBitShift</hljs>(4 >> 1, 4 << 1)
```

## Attribute configuration

By default, attributes can be added in several places, as listed above. It's possible, however, to configure them so they can only be used in specific places. For example you could make it so that `ClassAttribute` can only be used on classes, and nowhere else. Opting-in this behaviour is done by passing a flag to the `Attribute` attribute on the attribute class.

It looks like this:

```php
@@<hljs type>Attribute</hljs>(<hljs type>Attribute</hljs>::<hljs prop>TARGET_CLASS</hljs>)
class ClassAttribute
{
}
```

The following flags are available:

```php
<hljs type>Attribute</hljs>::<hljs prop>TARGET_CLASS</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_FUNCTION</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_METHOD</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_PROPERTY</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_CLASS_CONSTANT</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_PARAMETER</hljs>
<hljs type>Attribute</hljs>::<hljs prop>TARGET_ALL</hljs>
```

These are bitmask flags, so you can combine them [using a binary OR operation](/blog/bitwise-booleans-in-php).

```php
@@<hljs type>Attribute</hljs>(<hljs type>Attribute</hljs>::<hljs prop>TARGET_METHOD</hljs>|<hljs type>Attribute</hljs>::<hljs prop>TARGET_FUNCTION</hljs>)
class ClassAttribute
{
}
```

Another configuration flag is about repeatability. By default the same attribute can't be applied twice, unless it's specifically marked as repeatable. This is done the same way as target configuration, with a bit flag. 

```php
@@<hljs type>Attribute</hljs>(<hljs type>Attribute</hljs>::<hljs prop>IS_REPEATABLE</hljs>)
class ClassAttribute
{
}
```

Note that all these flags are only validated when calling `$attribute->newInstance()`, not earlier.


## Built-in attributes

Once the base RFC had been accepted, new opportunities arose to add built-in attributes to the core. One such example is the [`@@Deprecated`](*https://wiki.php.net/rfc/deprecated_attribute) attribute, and a popular example has been a `@@Jit` attribute — if you're not sure what that last one is about, you can read my post about [what the JIT is](/blog/php-jit).

I'm sure we'll see more and more built-in attributes in the future.

As a final note, for those worrying about generics: the syntax won't conflict with them, if they ever were to be added in PHP, so we're safe!

---

I've got some use-cases already in mind for attributes, what about you? If you've got some thoughts to share about this awesome new feature in PHP 8, you can reach me on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io), or we can discuss it over on [Reddit](*https://www.reddit.com/r/PHP/comments/gixnf3/attributes_in_php_8/).
