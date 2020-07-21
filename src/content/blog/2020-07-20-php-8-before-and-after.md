It's only a few months before [PHP 8](/blog/new-in-php-8) will be released, and honestly there are so many good features. In this post I want to share the real-life impact that PHP 8 will have on my own code.  

{{ ad:carbon }}

## Events subscribers with attributes

I'm going to try not to abuse [attributes](/blog/attributes-in-php-8), but I think configuring event listeners is an example of an annotation I'll be using extensively. 

You might know that I've been working on [event sourced systems](/blog/combining-event-sourcing-and-stateful-systems) lately, and I can tell you: there's lots of event configuration to do. Take this simple projector, for example:

```php
// Before

class CartsProjector implements Projector
{
    use ProjectsEvents;

    protected array $handlesEvents = [
        <hljs type>CartStartedEvent</hljs>::class => 'onCartStarted',
        <hljs type>CartItemAddedEvent</hljs>::class => 'onCartItemAdded',
        <hljs type>CartItemRemovedEvent</hljs>::class => 'onCartItemRemoved',
        <hljs type>CartExpiredEvent</hljs>::class => 'onCartExpired',
        <hljs type>CartCheckedOutEvent</hljs>::class => 'onCartCheckedOut',
        <hljs type>CouponAddedToCartItemEvent</hljs>::class => 'onCouponAddedToCartItem',
    ];

    public function onCartStarted(<hljs type>CartStartedEvent</hljs> $event): void
    { /* … */ }

    public function onCartItemAdded(<hljs type>CartItemAddedEvent</hljs> $event): void
    { /* … */ }

    public function onCartItemRemoved(<hljs type>CartItemRemovedEvent</hljs> $event): void
    { /* … */ }

    public function onCartCheckedOut(<hljs type>CartCheckedOutEvent</hljs> $event): void
    { /* … */ }

    public function onCartExpired(<hljs type>CartExpiredEvent</hljs> $event): void
    { /* … */ }

    public function onCouponAddedToCartItem(<hljs type>CouponAddedToCartItemEvent</hljs> $event): void
    { /* … */ }
}
``` 

<em class="center small">PHP 7.4</em>

There are two benefits attributes will give me:

- Event listener configuration and handlers are put together, I don't have to scroll to the top of the file to know whether a listener is configured correctly.
- I don't have to bother anymore writing and managing method names as strings: your IDE can't autocomplete them, there's no static analysis on typos and method renaming doesn't work.

Luckily, PHP 8 solves these problems:

```php
class CartsProjector implements Projector
{
    use ProjectsEvents;

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CartStartedEvent</hljs>::class)
    public function onCartStarted(<hljs type>CartStartedEvent</hljs> $event): void
    { /* … */ }

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CartItemAddedEvent</hljs>::class)
    public function onCartItemAdded(<hljs type>CartItemAddedEvent</hljs> $event): void
    { /* … */ }

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CartItemRemovedEvent</hljs>::class)
    public function onCartItemRemoved(<hljs type>CartItemRemovedEvent</hljs> $event): void
    { /* … */ }

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CartCheckedOutEvent</hljs>::class)
    public function onCartCheckedOut(<hljs type>CartCheckedOutEvent</hljs> $event): void
    { /* … */ }

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CartExpiredEvent</hljs>::class)
    public function onCartExpired(<hljs type>CartExpiredEvent</hljs> $event): void
    { /* … */ }

    @@<hljs type>SubscribesTo</hljs>(<hljs type>CouponAddedToCartItemEvent</hljs>::class)
    public function onCouponAddedToCartItem(<hljs type>CouponAddedToCartItemEvent</hljs> $event): void
    { /* … */ }
}
```

<em class="center small">PHP 8</em>

## Static instead of doc blocks

A smaller one, but this one will have a day-by-day impact. I often find myself still needing doc blocks because of two things: static return types en generics. The latter one can't be solved yet, but luckily the first one will in PHP 8!

When I'd write this in PHP 7.4:

```php
/**
 * @return <hljs type>static</hljs>
 */
public static function new()
{
    return new static();
}
```

<em class="center small">PHP 7.4</em>

I'll now be able to write:

```php
public static function new(): <hljs type>static</hljs>
{
    return new static();
}
```

<em class="center small">PHP 8</em>

## DTO's, property promotion and named arguments

If you read my blog, you know I wrote quite a bit about the use of PHP's type system combined with [data transfer objects](/blog/laravel-beyond-crud-02-working-with-data). Naturally, I use lots of DTOs in my own code, so you can image how happy I am, being able to rewrite this:

```php
class CustomerData extends DataTransferObject
{
    public <hljs type>string</hljs> <hljs prop>$name</hljs>;

    public <hljs type>string</hljs> <hljs prop>$email</hljs>;

    public <hljs type>int</hljs> <hljs prop>$age</hljs>;
    
    public static function fromRequest(
        <hljs type>CustomerRequest</hljs> $request
    ): self {
        return new self([
            'name' => $request-><hljs prop>get</hljs>('name'),
            'email' => $request-><hljs prop>get</hljs>('email'),
            'age' => $request-><hljs prop>get</hljs>('age'),
        ]);
    }
}

$data = <hljs type>CustomerData</hljs>::<hljs prop>fromRequest</hljs>($customerRequest);
```

<em class="center small">PHP 7.4</em>

As this:

```php
class CustomerData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$name</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$email</hljs>,
        <hljs keyword>public</hljs> <hljs type>int</hljs> <hljs prop>$age</hljs>,
    ) {}
}

$data = new <hljs type>CustomerData</hljs>(...$customerRequest-><hljs prop>validated</hljs>());
```

<em class="center small">PHP 8</em>

Note the use of both [constructor property promotion](/blog/constructor-promotion-in-php-8), as well as named arguments. Yes, they can be passed using named arrays and the spread operator!

## Enums and the match expression

Do you sometimes find yourself using an enum with some methods on it, that will give a different result based on the enum value?

```php
/**
 * @method static self PENDING()
 * @method static self PAID()
 */
class InvoiceState extends Enum
{
    private const PENDING = 'pending';
    private const PAID = 'paid';

    public function getColour(): string
    {
        return [
            self::PENDING => 'orange',
            self::PAID => 'green',
        ][$this-><hljs prop>value</hljs>] ?? 'gray';   
    }
}
```

<em class="center small">PHP 7.4</em>

I would argue that for complexer conditions, you're better off using [the state pattern](/blog/laravel-beyond-crud-05-states), yet there are cases where an enum does suffice. This weird array syntax already is a shorthand for a more verbose conditional:

```php
/**
 * @method static self PENDING()
 * @method static self PAID()
 */
class InvoiceState extends Enum
{
    private const PENDING = 'pending';
    private const PAID = 'paid';

    public function getColour(): string
    {
        if ($this-><hljs prop>value</hljs> === self::PENDING) {
            return 'orange';
        }
    
        if ($this-><hljs prop>value</hljs> === self::PAID) {
            return 'green'
        }

        return 'gray';
    }
}
```

<em class="center small">PHP 7.4 — alternative</em>

But with PHP 8, we can use the [`<hljs keyword>match</hljs>` expression](/blog/php-8-match-or-switch) instead!

```php
/**
 * @method static self PENDING()
 * @method static self PAID()
 */
class InvoiceState extends Enum
{
    private const PENDING = 'pending';
    private const PAID = 'paid';

    public function getColour(): string
    {
        return <hljs keyword>match</hljs> ($this->value) {
            self::PENDING => 'orange',
            self::PAID => 'green',
            <hljs keyword>default</hljs> => 'gray',
        };
}
```

<em class="center small">PHP 8</em>

## Union types instead of doc blocks

When I mentioned the `<hljs type>static</hljs>` return type before, I forgot another use case where docblock type hints were required: union types. At least, they were required before, because PHP 8 supports them natively!

```php
/**
 * @param <hljs type>string|int</hljs> $input
 *
 * @return <hljs type>string</hljs> 
 */
public function sanitize($input): string;
```

<em class="center small">PHP 7.4</em>

```php
public function sanitize(<hljs type>string|int</hljs> $input): string;
```

<em class="center small">PHP 8</em>

## Throw expressions

Before PHP 8, you couldn't use `<hljs keyword>throw</hljs>` in an expression, meaning you'd have to do explicit checks like so:

```php
public function (<hljs type>array</hljs> $input): void
{
    if (! isset($input['bar'])) {
        throw <hljs type>BarIsMissing</hljs>::<hljs prop>new</hljs>();
    }
    
    $bar = $input['bar'];

    // …
}
```

<em class="center small">PHP 7.4</em>

In PHP 8, `<hljs keyword>throw</hljs>` has become an expression, meaning you can use it like so:

```php
public function (<hljs type>array</hljs> $input): void
{
    $bar = $input['bar'] ?? throw <hljs type>BarIsMissing</hljs>::<hljs prop>new</hljs>();

    // …
}
```

<em class="center small">PHP 8</em>

## The nullsafe operator

If you're familiar with the [null coalescing operator](/blog/shorthand-comparisons-in-php#null-coalescing-operator) you're already familiar with its shortcomings: it doesn't work on method calls. Instead you need intermediate checks, or rely on `<hljs prop>optional</hljs>` helpers provided by some frameworks:

```php
$startDate = $booking-><hljs prop>getStartDate</hljs>();

$dateAsString = $startDate ? $startDate-><hljs prop>asDateTimeString</hljs>() : null;
```

<em class="center small">PHP 7.4</em>

With the addition of the nullsafe operator, we can now have null coalescing-like behaviour on methods!

```php
$dateAsString = $booking-><hljs prop>getStartDate</hljs>()?-><hljs prop>asDateTimeString</hljs>();
``` 

<em class="center small">PHP 8</em>

---

What's your favourite [PHP 8 feature](/blog/new-in-php-8)? Let me know via [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io)!
