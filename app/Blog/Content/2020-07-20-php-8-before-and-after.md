---
title: 'PHP 8: before and after'
next: new-in-php-8
tag: src/content/tags/php-8.yaml
meta:
    description: 'The impact of PHP 8 on my code'
footnotes:
    - { link: /blog/new-in-php-8, title: "What's new in PHP 8" }
    - { link: /blog/php-8-named-arguments, title: 'Named arguments in PHP 8' }
    - { link: /blog/attributes-in-php-8, title: 'Attributes in PHP 8' }
    - { link: /blog/constructor-promotion-in-php-8, title: 'Constructor property promotion in PHP 8' }
---

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
        CartStartedEvent::class => 'onCartStarted',
        CartItemAddedEvent::class => 'onCartItemAdded',
        CartItemRemovedEvent::class => 'onCartItemRemoved',
        CartExpiredEvent::class => 'onCartExpired',
        CartCheckedOutEvent::class => 'onCartCheckedOut',
        CouponAddedToCartItemEvent::class => 'onCouponAddedToCartItem',
    ];

    public function onCartStarted(CartStartedEvent $event): void
    { /* … */ }

    public function onCartItemAdded(CartItemAddedEvent $event): void
    { /* … */ }

    public function onCartItemRemoved(CartItemRemovedEvent $event): void
    { /* … */ }

    public function onCartCheckedOut(CartCheckedOutEvent $event): void
    { /* … */ }

    public function onCartExpired(CartExpiredEvent $event): void
    { /* … */ }

    public function onCouponAddedToCartItem(CouponAddedToCartItemEvent $event): void
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

    #[SubscribesTo(CartStartedEvent::class)]
    public function onCartStarted(CartStartedEvent $event): void
    { /* … */ }

    #[SubscribesTo(CartItemAddedEvent::class)]
    public function onCartItemAdded(CartItemAddedEvent $event): void
    { /* … */ }

    #[SubscribesTo(CartItemRemovedEvent::class)]
    public function onCartItemRemoved(CartItemRemovedEvent $event): void
    { /* … */ }

    #[SubscribesTo(CartCheckedOutEvent::class)]
    public function onCartCheckedOut(CartCheckedOutEvent $event): void
    { /* … */ }

    #[SubscribesTo(CartExpiredEvent::class)]
    public function onCartExpired(CartExpiredEvent $event): void
    { /* … */ }

    #[SubscribesTo(CouponAddedToCartItemEvent::class)]
    public function onCouponAddedToCartItem(CouponAddedToCartItemEvent $event): void
    { /* … */ }
}
```

<em class="center small">PHP 8</em>

## Static instead of doc blocks

A smaller one, but this one will have a day-by-day impact. I often find myself still needing doc blocks because of two things: static return types and generics. The latter one can't be solved yet, but luckily the first one will in PHP 8!

When I'd write this in PHP 7.4:

```php
/**
 * @return static
 */
public static function new()
{
    return new static();
}
```

<em class="center small">PHP 7.4</em>

I'll now be able to write:

```php
public static function new(): static
{
    return new static();
}
```

<em class="center small">PHP 8</em>

{{ cta:dynamic }}

## DTO's, property promotion and named arguments

If you read my blog, you know I wrote quite a bit about the use of PHP's type system combined with [data transfer objects](/blog/laravel-beyond-crud-02-working-with-data). Naturally, I use lots of DTOs in my own code, so you can imagine how happy I am, being able to rewrite this:

```php
class CustomerData extends DataTransferObject
{
    public string $name;

    public string $email;

    public int $age;
    
    public static function fromRequest(
        CustomerRequest $request
    ): self {
        return new self([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'age' => $request->get('age'),
        ]);
    }
}

$data = CustomerData::fromRequest($customerRequest);
```

<em class="center small">PHP 7.4</em>

As this:

```php
class CustomerData
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {}
}

$data = new CustomerData(...$customerRequest->validated());
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
        ][$this->value] ?? 'gray';   
    }
}
```

<em class="center small">PHP 7.4</em>

I would argue that for more complex conditions, you're better off using [the state pattern](/blog/laravel-beyond-crud-05-states), yet there are cases where an enum does suffice. This weird array syntax already is a shorthand for a more verbose conditional:

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
        if ($this->value === self::PENDING) {
            return 'orange';
        }
    
        if ($this->value === self::PAID) {
            return 'green'
        }

        return 'gray';
    }
}
```

<em class="center small">PHP 7.4 — alternative</em>

But with PHP 8, we can use the [`match` expression](/blog/php-8-match-or-switch) instead!

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
        return match ($this->value) {
            self::PENDING => 'orange',
            self::PAID => 'green',
            default => 'gray',
        };
    }
}
```

<em class="center small">PHP 8</em>

{{ cta:mail }}

## Union types instead of doc blocks

When I mentioned the `static` return type before, I forgot another use case where docblock type hints were required: union types. At least, they were required before, because PHP 8 supports them natively!

```php
/**
 * @param string|int $input
 *
 * @return string 
 */
public function sanitize($input): string;
```

<em class="center small">PHP 7.4</em>

```php
public function sanitize(string|int $input): string;
```

<em class="center small">PHP 8</em>

## Throw expressions

Before PHP 8, you couldn't use `throw` in an expression, meaning you'd have to do explicit checks like so:

```php
public function (array $input): void
{
    if (! isset($input['bar'])) {
        throw BarIsMissing::new();
    }
    
    $bar = $input['bar'];

    // …
}
```

<em class="center small">PHP 7.4</em>

In PHP 8, `throw` has become an expression, meaning you can use it like so:

```php
public function (array $input): void
{
    $bar = $input['bar'] ?? throw BarIsMissing::new();

    // …
}
```

<em class="center small">PHP 8</em>

## The nullsafe operator

If you're familiar with the [null coalescing operator](/blog/shorthand-comparisons-in-php#null-coalescing-operator) you're already familiar with its shortcomings: it doesn't work on method calls. Instead you need intermediate checks, or rely on `optional` helpers provided by some frameworks:

```php
$startDate = $booking->getStartDate();

$dateAsString = $startDate ? $startDate->asDateTimeString() : null;
```

<em class="center small">PHP 7.4</em>

With the addition of the nullsafe operator, we can now have null coalescing-like behaviour on methods!

```php
$dateAsString = $booking->getStartDate()?->asDateTimeString();
``` 

<em class="center small">PHP 8</em>

{{ cta:dynamic }}

What's your favourite [PHP 8 feature](/blog/new-in-php-8)? Let me know via [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io)!
