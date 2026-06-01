---
title: 'PHP 8: Constructor property promotion'
next: new-in-php-8
meta:
    description: 'Constructor promotion in PHP significantly reduces the amount of code we need to write for simple objects'
tag: src/content/tags/php-8.yaml
footnotes:
    - { link: 'https://wiki.php.net/rfc/constructor_promotion', title: 'Constructor property promotion RFC' }
    - { link: /blog/new-in-php-8, title: "What's new in PHP 8" }
    - { link: /blog/attributes-in-php-8, title: 'Attributes in PHP 8', description: ' — A close look at attributes, also known as annotations' }
    - { link: /blog/php-jit, title: 'The JIT in PHP 8' }
    - { link: /blog/the-latest-php-version, title: 'The latest PHP version', description: ' — how modern PHP versions are managed' }
---

Personally, I use value objects and data transfer objects all the time in my projects. I even wrote a dedicated post on [how to treat data in our code](/blog/laravel-beyond-crud-02-working-with-data) a while back.

Naturally, I'm very happy with the constructor property promotion RFC, it's passed and will be added in [PHP 8](/blog/new-in-php-8). You see, this feature reduces a lot of boilerplate code when constructing simple objects such as VOs and DTOs.

In short: property promotion allows you to combine class fields, constructor definition and variable assignments all into one syntax, in the construct parameter list.

So instead of doing this:

```php
class CustomerDTO
{
    public string $name;

    public string $email;

    public DateTimeImmutable $birth_date;

    public function __construct(
        string $name, 
        string $email, 
        DateTimeImmutable $birth_date
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->birth_date = $birth_date;
    }
}
```

You would write this:

```php
class CustomerDTO
{
    public function __construct(
        public string $name, 
        public string $email, 
        public DateTimeImmutable $birth_date,
    ) {}
}
```

Let's look at how it works!

{{ ad:carbon }}

---

## How it works

The basic idea is simple: ditch all the class properties and the variable assignments, and prefix the constructor parameters with `public`, `protected` or `private`. PHP will take that new syntax, and transform it to normal syntax under the hood, before actually executing the code.

So it goes from this:

```php
class MyDTO
{
    public function __construct(
        public string $name = 'Brent',
    ) {}
}
```

To this:

```php
class MyDTO
{
    public string $name;

    public function __construct(
        string $name = 'Brent'
    ) {
        $this->name = $name;
    }
}
```

And only executes it afterwards. 

Note by the way that the default value is not set on the class property, but on the method argument in the constructor. 

## Promoted property properties

So let's look at what promoted properties can and can't do, there's quite a lot of little intricacies worth mentioning!

---

### Only in constructors

Promoted properties can only be used in constructors. That might seem obvious but I thought it was worth mentioning this, just to be clear.

{{ cta:mail }}

### No duplicates allowed

You're not able to declare a class property and a promoted property with the same name. That's also rather logical, since the promoted property is simply transpiled to a class property at runtime.

```php
class MyClass
{
    public string $a;

    public function __construct(
        public string $a,
    ) {}
}
```

---

### Untyped properties are allowed

You're allowed to promote untyped properties, though I'd argue that these days with [modern PHP](/blog/php-in-2020), you're better off typing everything.

```php
class MyDTO
{
    public function __construct(
        public $untyped,
    ) {}
}
```

---

### Simple defaults

Promoted properties can have default values, but expressions like `new …` are not allowed. 

```php
public function __construct(
    public string $name = 'Brent',
    public DateTimeImmutable $date = new DateTimeImmutable(),
) {}
```

---

### Combining promoted- and normal properties

Not all constructor properties should be promoted, you can mix and match.

```php
class MyClass
{
    public string $b;

    public function __construct(
        public string $a,
        string $b,
    ) {
        $this->b = $b;
    }
}
```

I'd say: be careful mixing the syntaxes, if it makes the code less clear, consider using a normal constructor instead.  



---

### Access promoted properties from the constructor body

You're allowed to read the promoted properties in the constructor body. This can be useful if you want to do extra validation checks. You can use both the local variable and the instance variable, both work fine.

```php
public function __construct(
    public int $a,
    public int $b,
) {
    assert($this->a >= 100);

    if ($b >= 0) {
        throw new InvalidArgumentException('…');
    }
}
```

---

### Doc comments on promoted properties

You can add doc comments on promoted properties, and they are still available via reflection.

```php
class MyClass 
{
    public function __construct(
        /** @var string */
        public $a,
    ) {}
}
```

```php
$property = new ReflectionProperty(MyClass::class, 'a');

$property->getDocComment(); // "/** @var string */"
```

---

### Attributes

Just like doc blocks, [attributes](/blog/attributes-in-php-8) are allowed on promoted properties. When transpiled, they will be present both on the constructor parameter, as well as the class property. 

```php
class MyClass
{
    public function __construct(
        #[MyAttribute]
        public $a,  
    ) {}
}
```

Will be transpiled to:

```php
class MyClass 
{
    #[MyAttribute]
    public $a;
 
    public function __construct(
        #[MyAttribute]
        $a,
    ) {
        $this->a = $a;
    }
}
```

---

### Not allowed in abstract constructors

I didn't even know abstract constructors were a thing, but here goes! Promoted properties are not allowed in them.

```php
abstract class A
{
    abstract public function __construct(
        public string $a,
    ) {}
}
```

---

### Allowed in traits

On the other hand, they are allowed in traits. This makes sense, since the transpiled syntax is also valid in traits.

```php
trait MyTrait
{
    public function __construct(
        public string $a,
    ) {}
}
```

---

### `var` is not supported

Old, I mean, experienced PHP developers might have used `var` in a distant past to declare class variables. It's not allowed with constructor promotion. Only `public`, `protected` and `private` are valid keywords.

```php
public function __construct(
    var string $a,
) {}
```

---

### Variadic parameters cannot be promoted

Since you can't convert to a type that's `array of type`, it's not possible to promote variadic parameters.

```php
public function __construct(
    public string ...$a,
) {}
```

Still waiting for generics…

---

### Reflection for `isPromoted` 

Both `ReflectionProperty` and `ReflectionParameter` have a new `isPromoted` method to check whether the class property or method parameter is promoted.

---

### Inheritance

Since PHP constructors don't need to follow the declaration of their parent constructor, there's little to be said: inheritance is allowed. If you need to pass properties from the child constructor to the parent constructor though, you'll need to manually pass them:

```php
class A
{
    public function __construct(
        public $a,
    ) {}
}

class B extends A
{
    public function __construct(
        $a,
        public $b,    
    ) {
        parent::__construct($a);
    }
}
```

---

That's about it for property promotion! I for sure will use them, what about you? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io)!

---
