Typed class properties have been added in ((PHP 7.4)) and provide a major improvement to ((PHP))'s type system.
These changes are fully opt-in and non breaking to previous versions.

In this post we'll look at the feature in-depth, but first let's start by summarising the most important points:

- They are available as of ((PHP 7.4)), which is scheduled to be released in November of 2019
- They are only available in classes and require an access modifier: `public`, `protected` or `private`; or `var`
- All types are allowed, except `void` and `callable`

{{ ad:carbon }}

This is what they look like in action:

```php
class Foo
{
    public <hljs type>int</hljs> $a;

    public <hljs type>?string</hljs> $b = 'foo';

    private <hljs type>Foo</hljs> $prop;

    protected static <hljs type>string</hljs> $static = 'default';
}
```

If you're unsure about the added benefit of types, I'd recommend you reading [this post](/blog/tests-and-types) first.

## Uninitialized

Before looking at the fun stuff, there's an important aspect about typed properties that's essential to talk about first.

Despite what you might think on first sight, the following code is valid:

```php
class Foo
{
    public <hljs type>int</hljs> $bar;
}

$foo = new <hljs type>Foo</hljs>;
```

Even though the value of `$bar` isn't an integer after making an object of `Foo`, ((PHP)) will only throw an error when `$bar` is accessed:

```php
<hljs prop>var_dump</hljs>($foo->bar);

<hljs full error>Fatal error: Uncaught Error: Typed property Foo::$bar 
must not be accessed before initialization</hljs>
```

As you can read from the error message, 
there's a new kind of "variable state": uninitialized.

If `$bar` didn't have a type, its value would simply be `null`. 
Types can be nullable though, so it's not possible to determine whether a typed nullable property was set, or simply forgotten. 
That's why "uninitialized" was added.

There are four important things to remember about uninitialized:

- You cannot read from uninitialized properties, doing so will result in a fatal error.
- Because uninitialized state is checked when accessing a property, you're able to create an object with an uninitialized property, even though its type is non-nullable.
- You can write to an uninitialized property before reading from it.
- Using `unset` on a typed property will make it uninitialized, while unsetting an untyped property will make it `null`.

Especially note that the following code, where an uninitialised, non-nullable property is set after constructing the object, is valid

```php
class Foo
{
    public <hljs type>int</hljs> $a;
}

$foo = new <hljs type>Foo</hljs>;

$foo->a = 1;
```

While uninitialized state is only checked when reading the value of a property, type validation is done when writing to it.
This means that you can be sure that no invalid type will ever end up as a property's value.

{{ cta:flp8 }}

## Defaults and constructors

Let's take a closer look at how typed values can be initialized.
In case of scalar types, it's possible to provide a default value:

```php
class Foo
{
    public <hljs type>int</hljs> $bar = 4;
    
    public <hljs type>?string</hljs> $baz = null;
    
    public <hljs type>array</hljs> $list = [1, 2, 3];
}
```

Note that you can only use `null` as a default if the type is actually nullable.
This might seem obvious, but there's some legacy behaviour with parameter defaults where the following is allowed:

```php
function passNull(<hljs type>int</hljs> $i = null)
{ /* … */ }

<hljs prop>passNull</hljs>(null);
```

Luckily this confusing behaviour is not allowed with typed properties.

Also note that it's impossible to have default values with `object` or class types. 
You should use the constructor to set their defaults.

The obvious place to initialize typed values would of course be the constructor:

```php
class Foo
{
    private <hljs type>int</hljs> $a;

    public function __construct(<hljs type>int</hljs> $a)
    {
        $this->a = $a;
    }
}
```

But also remember what I mentioned before: it's valid to write to an uninitialized property, outside of the constructor. As long as there are nothing is reading from a property, the uninitialized check is not performed. 

## Types of types

So what exactly can be typed and how? I already mentioned that typed properties will only work in classes (for now),
and that they need an access modifier or the `var` key word in front of them.

As of available types, almost all types can be used, except `void` and `callable`.

Because `void` means the absence of a value, it makes sense that it cannot be used to type a value.
`callable` however is a little more nuanced.

See, a "callable" in ((PHP)) can be written like so:

```php
$callable = [$this, 'method'];
```

Say you'd have the following (broken) code:

```php
class Foo
{
    public <hljs striped>callable</hljs> $callable;
    
    public function __construct(<hljs striped>callable</hljs> $callable)
    { /* … */ }
}

class Bar
{
    public <hljs type>Foo</hljs> $foo;
    
    public function __construct()
    {
        $this->foo = new <hljs type>Foo</hljs>([$this, 'method'])
    }
    
    private function method()
    { /* … */ }
}

$bar = new <hljs type>Bar</hljs>;

($bar->foo-><hljs striped>callable</hljs>)();
```

In this example, `$callable` refers to the private `Bar::method`, but is called within the context of `Foo`.
Because of this problem, it was decided not to add `callable` support. 

It's no big deal though, because `Closure` is a valid type, which will remember the `$this` context where it was constructed.

With that out of the way, here's a list of all available types:

- bool
- int
- float
- string
- array
- iterable
- object
- ? (nullable)
- self & parent
- Classes & interfaces

## Coercion and strict types

((PHP)), being the dynamic language we love and hate, will try to coerce or convert types whenever possible. 
Say you pass a string where you expect an integer, ((PHP)) will try and convert that string automatically:

```php
function coerce(<hljs type>int</hljs> $i)
{ /* … */ }

<hljs prop>coerce</hljs>('1'); // 1
```

The same principles apply to typed properties. The following code is valid and will convert `'1'` to `1`.

```php
class Bar
{
    public <hljs type>int</hljs> $i;
}

$bar = new <hljs type>Bar</hljs>;

$bar->i = '1'; // 1
```

If you don't like this behaviour you can disabled it by declaring strict types:

```php
declare(strict_types=1);

$bar = new <hljs type>Bar</hljs>;

$bar->i = '1'; // 1

<hljs error full>Fatal error: Uncaught TypeError: 
Typed property Bar::$i must be int, string used</hljs>
``` 

## Type variance and inheritance

Even though ((PHP 7.4)) introduced [improved type variance](/blog/new-in-php-74#improved-type-variance-rfc), typed properties are still invariant. This means that the following is not valid:

```php
class A {}
class B extends A {}

class Foo
{
    public A $prop;
}

class Bar extends Foo
{
    public <hljs striped>B</hljs> $prop;
}

<hljs full error text>Fatal error: Type of Bar::$prop must be A (as in class Foo)</hljs>
```

If the above example doesn't seem significant, you should take a look at the following:

```php
class Foo
{
    public <hljs type>self</hljs> $prop;
}

class Bar extends Foo
{
    public <hljs type>self</hljs> $prop;
}
```

((PHP)) will replace `self` behind the scenes with the concrete class it refers to, before running the code.
This means that the same error will be thrown in this example. 
The only way to handle it, is by doing the following:

```php
class Foo
{
    public <hljs type>Foo</hljs> $prop;
}

class Bar extends Foo
{
    public <hljs type>Foo</hljs> $prop;
}
```

Speaking of inheritance, you might find it hard to come up with any good use cases to overwrite the types of inherited properties. 

While I agree with that sentiment, it's worth noting that it is possible to change the type of an inherited property, but only if the access modifier also changes from `private` to `protected` or `public`.

The following code is valid:

```php
class Foo
{
    private <hljs type>int</hljs> $prop;
}

class Bar extends Foo
{
    public <hljs type>string</hljs> $prop;
}
```

However, changing a type from nullable to non-nullable or reverse, is not allowed.

```php
class Foo
{
    public <hljs type>int</hljs> $a;
    public <hljs type>?int</hljs> $b;
}

class Bar extends Foo
{
    public <hljs striped>?int</hljs> $a;
    public <hljs striped>int</hljs> $b;
}

<hljs full error text>Fatal error: Type of Bar::$a must be int (as in class Foo)</hljs>
```

## There's more!

Like a said at the start of this post, typed properties are a _major_ addition to ((PHP)). 
There's lots more to say about them. I'd suggest you reading through the [((RFC))](*https://wiki.php.net/rfc/typed_properties_v2) to know all the neat little details.

If you're new to ((PHP 7.4)), you probably want to read the [full list](/blog/new-in-php-74) of changes made and features added. To be honest, it's one of the best releases in a long time, and worth your time!

Finally, if you have any thoughts you want to share on the topic, I'd love to hear from you!
You can reach me via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

Until next time!
