---
title: 'PHP 8: the null safe operator'
meta:
    description: 'The null safe operator allows you to safely call methods and properties on nullables on PHP 8.'
    template: blog/meta/nullsafe-operator.twig
footnotes:
    - { title: 'Dealing with null', link: 'https://front-line-php.com/dealing-with-null' }
    - { title: 'The nullsafe operator RFC', link: 'https://wiki.php.net/rfc/nullsafe_operator' }
    - { title: "What's new in PHP 8", link: /blog/new-in-php-8 }
---

If you've used the [null coalescing operator](/blog/shorthand-comparisons-in-php#null-coalescing-operator) in the past, you probably also noticed its shortcomings: null coalescing doesn't work on method calls. Instead you need intermediate checks, or rely on `optional` helpers provided by some frameworks:

```php
$startDate = $booking->getStartDate();

$dateAsString = $startDate ? $startDate->asDateTimeString() : null;
```

The nullsafe operator provides functionality similar to null coalescing, but also supports method calls. Instead of writing this:

```php
$country =  null;
 
if ($session !== null) {
    $user = $session->user;
 
    if ($user !== null) {
        $address = $user->getAddress();
 
        if ($address !== null) {
            $country = $address->country;
        }
    }
}
``` 

PHP 8 allows you to write this:

```php
$country = $session?->user?->getAddress()?->country;
```

Let's take a look at what this new operator can and cannot do!

## Nullsafe operator in depth

Let's start by addressing the most important question: what exactly is the difference between the null coalescing operator and the nullsafe operator?

Let's take a look at this example:

```php
class Order
{
    public ?Invoice $invoice = null;
}

$order = new Order();
```

Here we have an `Order` object which has an optional relation to an `Invoice` object. Now imagine we'd want to get the invoice's number (if the invoice isn't null). You could do this both with the null coalescing operator and the nullsafe operator:

```php
var_dump($order->invoice?->number);
var_dump($order->invoice->number ?? null);
```

So what's the difference? While you could use both operators to achieve the same result in this example, they also have specific edge cases only one of them can handle. For example, you can use the null coalescing operator in combination with array keys, while the nullsafe operator can't handle them:

```php
$array = [];

var_dump($array['key']->foo ?? null);
```

```
var_dump($array['key']?->foo);

Warning: Undefined array key "key"
```

The nullsafe operator, on the other hand, can work with method calls, while the null coalescing operator can't. Imagine an `Invoice` object like so:

```php
class Invoice
{
    public function getDate(): ?DateTime { /* … */ }
    
    // …
}

$invoice = new Invoice();
```

You could use the nullsafe operator to call `format` on the invoice's date, even when it's `null`:

```php
var_dump($invoice->getDate()?->format('Y-m-d'));

// null
```

While the null coalescing operator would crash:

```php
var_dump($invoice->getDate()->format('Y-m-d') ?? null);

Fatal error: Uncaught Error: Call to a member function format() on null
```

{{ cta:dynamic }}

### Short circuiting

Sometimes you could use either the null coalescing or nullsafe operator, and other times you'd need to use a specific one. The difference is that the nullsafe operator uses a form of "short circuiting": writing `?->` will cause PHP to look at whats on the lefthand side of this operator, if it's `null` then the righthand side will simply be discarded. The null coalescing operator is actually an `isset` call in disguise on its lefthand operand, which doesn't support short circuiting.

Short circuiting also means that when writing something like this:

```php
$foo?->bar(expensive_function());
```

`expensive_function` would only be executed if `$foo` is actually not `null`.

### Nested nullsafe operators

It's possible to nest several nullsafe operator calls like so:

```php
$foo?->bar?->baz()?->boo?->baa();
```

### Only for reading data

You cannot use the nullsafe operator to write data to objects:

```php
$offer?->invoice?->date = new DateTime(); 
```

{{ cta:mail }}

The nullsafe operator is definitely a missing piece of the puzzle finally added in PHP. Given its dynamic nature, it feels good to have a smooth way of dealing with `null`. The difference and overlap between the nullsafe operator and null coalescing operator feels a bit confusing at first, but I'm sure we'll get used to it.

