Writing data transfer objects and value objects in PHP has become significantly easier over the years. Take for example a look at a DTO in PHP 5.6:


```php
class BlogData
{
    /** @var <hljs type>string</hljs> */
    private <hljs prop>$title</hljs>;
    
    /** @var <hljs type>Status</hljs> */
    private <hljs prop>$status</hljs>;
    
    /** @var <hljs type>\DateTimeImmutable</hljs>|<hljs type>null</hljs> */
    private <hljs prop>$publishedAt</hljs>;
   
   /**
    * @param <hljs type>string</hljs> $title 
    * @param <hljs type>Status</hljs> $status 
    * @param <hljs type>\DateTimeImmutable</hljs>|<hljs type>null</hljs> $publishedAt 
    */
    public function __construct(
        $title,
        $status,
        $publishedAt = <hljs keyword>null</hljs>
    ) {
        $this-><hljs prop>title</hljs> = $title;
        $this-><hljs prop>status</hljs> = $status;
        $this-><hljs prop>publishedAt</hljs> = $publishedAt;
    }
    
    /**
     * @return <hljs type>string</hljs> 
     */
    public function getTitle()
    {
        return $this-><hljs prop>title</hljs>;    
    }
    
    /**
     * @return <hljs type>Status</hljs> 
     */
    public function getStatus() 
    {
        return $this-><hljs prop>status</hljs>;    
    }
    
    /**
     * @return <hljs type>\DateTimeImmutable</hljs>|<hljs type>null</hljs> 
     */
    public function getPublishedAt() 
    {
        return $this-><hljs prop>publishedAt</hljs>;    
    }
}
```

And compare it to its [PHP 8.0](/blog/new-in-php-8)'s equivalent:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>private</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>private</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
        <hljs keyword>private</hljs> <hljs type>?DateTimeImmutable</hljs> <hljs prop>$publishedAt</hljs> = <hljs keyword>null</hljs>,
    ) {}
    
    public function getTitle(): string
    {
        return $this-><hljs prop>title</hljs>;    
    }
    
    public function getStatus(): Status 
    {
        return $this-><hljs prop>status</hljs>;    
    }
    
    public function getPublishedAt(): <hljs type>?DateTimeImmutable</hljs>
    {
        return $this-><hljs prop>publishedAt</hljs>;    
    }
}
```

That's already quite the difference, though I think there's still one big issue: all those getters. Personally, I don't use them anymore since PHP 8.0 with its [promoted properties](/blog/constructor-promotion-in-php-8). I simply prefer to use public properties instead of adding getters:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
        <hljs keyword>public</hljs> <hljs type>?DateTimeImmutable</hljs> <hljs prop>$publishedAt</hljs> = <hljs keyword>null</hljs>,
    ) {}
}
```

Object oriented purists don't like this approach though: an object's internal status shouldn't be exposed directly, and definitely not be changeable from the outside.

In our projects at Spatie, we have an internal style guide rule that DTOs and VOs with public properties shouldn't be changed from the outside; a practice that seems to work fairly well, we've been doing it for quite some time now without running into any problems.

However, yes; I agree that it would be better if the language ensured that public properties couldn't be overwritten at all. Well, [PHP 8.1](/blog/new-in-php-81) solves all these issues by introducing the `<hljs keyword>readonly</hljs>` keyword:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>?DateTimeImmutable</hljs> <hljs prop>$publishedAt</hljs> = <hljs keyword>null</hljs>,
    ) {}
}
```

This keyword basically does what its name suggests: once a property is set, it cannot be overwritten anymore:

```php
$blog = new <hljs type>BlogData</hljs>(
    <hljs prop>title</hljs>: 'PHP 8.1: readonly properties', 
    <hljs prop>status</hljs>: <hljs type>Status</hljs>::<hljs prop>PUBLISHED</hljs>, 
    <hljs prop>publishedAt</hljs>: <hljs prop>now</hljs>()
);

<hljs striped>$blog-><hljs prop>title</hljs> = 'Another title';</hljs>

<hljs error full>Error: Cannot modify readonly property BlogData::$title</hljs>
```

Knowing that, when an object is constructed, it won't change anymore, gives a level of certainty and peace when writing code: a whole range of unforeseen data changes simply can't happen anymore.

Of course, you still want to be able to copy data over to a new object, and maybe change some properties along the way. We'll discuss how to do that with readonly properties later in this post. First, let's look at them in depth.

{{ cta:dynamic }}

### Only typed properties

Readonly properties can only be used in combination with typed properties:

```php
class BlogData
{
    public <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public <hljs keyword striped>readonly</hljs> <hljs prop>$mixed</hljs>;
}
```

You can however use `<hljs type>mixed</hljs>` as a type hint:

```php
class BlogData
{
    public <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public <hljs keyword>readonly</hljs> <hljs type>mixed</hljs> <hljs prop>$mixed</hljs>;
}
```

The reason for this restriction is that by omitting a property type, PHP will automatically set a property's value to `<hljs keyword>null</hljs>` if no explicit value was supplied in the constructor. This behaviour, combined with readonly, would cause unnecessary confusion.

### Both normal and promoted properties

You've already seen examples of both: `<hljs keyword>readonly</hljs>` can be added both on normal, as well as promoted properties:

```php
class BlogData
{
    public <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>, 
    ) {}
}
```

### No default value

Readonly properties can not have a default value:

```php
class BlogData
{
    public <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs><hljs striped> = 'Readonly properties'</hljs>;
}
```

That is, unless they are promoted properties:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs> = 'Readonly properties', 
    ) {}
}
```

The reason that it _is_ allowed for promoted properties, is because the default value of a promoted property isn't used as the default value for the class property, but only for the constructor argument. Under the hood, the above code would transpile to this:

```php
class BlogData
{
    public <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public function __construct(
        <hljs type>string</hljs> <hljs prop>$title</hljs> = 'Readonly properties', 
    ) {
        $this-><hljs prop>title</hljs> = $title;
    }
}
```

You can see how the actual property doesn't get assigned a default value.
The reason for not allowing default values on readonly properties, by the way, is that they wouldn't be any different from constants in that form.

### Inheritance

You're not allowed to change the readonly flag during inheritance:

```php
class Foo
{
    public <hljs keyword>readonly</hljs> <hljs type>int</hljs> <hljs prop>$prop</hljs>;
}

class Bar extends Foo
{
    <hljs striped>public <hljs type>int</hljs> <hljs prop>$prop</hljs>;</hljs>
}
```

This rule goes in both directions: you're not allowed to add or remove the `<hljs keyword>readonly</hljs>` flag during inheritance.

### Unset is not allowed

Once a readonly property is set, you cannot change it, not even unset it:

```php
$foo = new <hljs type>Foo</hljs>('value');

<hljs striped><hljs keyword>unset</hljs>($foo-><hljs prop>prop</hljs>);</hljs>
```

### Reflection

There's a new `<hljs type>ReflectionProperty</hljs>::<hljs prop>isReadOnly</hljs>()` method, as well as a `<hljs type>ReflectionProperty</hljs>::<hljs prop>IS_READONLY</hljs>` flag.

### Cloning

So, if you can't change readonly properties, and if you can't unset them, how can you create a copy of your DTOs or VOs and change some of its data? You can't `<hljs keyword>clone</hljs>` them, because you wouldn't be able to overwrite its values. There's actually an idea to add a `<hljs keyword>clone with</hljs>` construct in the future that allows this behaviour, but that doesn't solve our problem now.

Well, you _can_ copy over objects with changed readonly properties, if you rely on a little bit of reflection magic. By creating an object _without_ calling its constructor (which is possible using reflection), and then by manually copying each property over — sometimes overwriting its value — you can in fact "clone" an object and change its readonly properties. 

I made [a small package](*https://github.com/spatie/php-cloneable) to do exactly that, here's what it looks like:

```php
class BlogData
{
    use <hljs type>Cloneable</hljs>;

    public function __construct(
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
    ) {}
}

$dataA = new <hljs type>BlogData</hljs>('Title');

$dataB = $dataA-><hljs prop>with</hljs>(<hljs prop>title</hljs>: 'Another title');
```

I actually wrote a dedicated blogpost explaining the mechanics behind all of this, you can read it [here](/blog/cloning-readonly-properties-in-php-81).

{{ cta:mail }}

So, that's all there is to say about readonly properties. I think they are a great feature if you're working on projects that deal with lots of DTOs and VOs, and require you to carefully manage the data flow throughout your code. Immutable objects with readonly properties are a significant help in doing so.

I'm looking forward to using them, what about you? Let me know on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io)! 

