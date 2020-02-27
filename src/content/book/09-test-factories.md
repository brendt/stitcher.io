Note: this chapter mostly addresses domain-related code. We'll come back to the application layer in future chapters.

---

In this chapter of [Laravel beyond CRUD](/blog/laravel-beyond-crud), we're going to look at how we can manage domain data for tests. Test factories in Laravel are a known concept, though they lack in many areas: they aren't very flexible and are also kind of a black box to the user.

{{ ad:carbon }}

Take the example of factory states, a powerful pattern, yet poorly implemented in Laravel.

```php
$factory-><hljs prop>state</hljs>(<hljs type>Invoice</hljs>::class, 'pending', [
    'status' => <hljs type>PaidInvoiceState</hljs>::class,
]);
```

First of all: your IDE has no clue what kind of object `$factory` actually is. It magically exists in factory files, though there's no autocompletion on it. A quick fix is to add this docblock, though that's cumbersome.

```php
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory-><hljs prop>state</hljs>(/* … */);
```

Second, states are defined as strings, making them a black box when actually using a factory in tests.

```php
public function test_case()
{
    $invoice = <hljs prop>factory</hljs>(<hljs type>Invoice</hljs>::class)
        -><hljs prop>states</hljs>(/* what states are actually available here? */)
        -><hljs prop>create</hljs>();
}
```

Third, there's no type hinting on the result of a factory, your IDE doesn't know that `$invoice` actually is an `Invoice` model; again: a black box.

And finally, given a large enough domain, you might need more than just a few states in your test suite, which become difficult to manage over time.

In this chapter we'll look at an alternative way of implementing this factory pattern, to allow much more flexibility and improve their user experience significantly. The actual goal of these factory classes is to help you write integration tests, without having to spend too much time on setting up the system for it. 

Note that I say "integration tests" and not "unit tests": when we're testing our domain code, we're testing the core business logic. More often than not, testing this business logic means we won't be testing an isolated piece of a class, but rather a complex and intricate business rule which requires some (or lots of) data to be present in the database.

As I've mentioned before: we're talking about large and complex systems in this book; it's important to keep that in mind. In particular, that's why I decided to call these tests _integration_ tests in this chapter; it was in order to avoid going into discussions about what unit tests are and what they aren't.

## A basic factory

A test factory is nothing more than a simple class. There's no package to require, no interfaces to implement or abstract classes to extend. The power of a factory is not the complexity of the code, but rather one or two patterns properly applied.

Here's what such a class looks like, simplified:

```php
class InvoiceFactory
{
    public static function new(): self
    {
        return new self();
    }
    
    public function create(<hljs type>array</hljs> $extra = []): Invoice
    {
        return <hljs type>Invoice</hljs>::<hljs prop>create</hljs>(<hljs prop>array_merge</hljs>(
            [
                'number' => 'I-1',
                'status' => <hljs type>PendingInvoiceState</hljs>::class,
                // …
            ],
            $extra
        ));   
    }
}
```

Let's discuss a few design decisions. 

First of all, the static constructor `new`. You might be confused as to why we need it, as we could simply make the `create` method static. I'll answer that question in depth later in this chapter, but for now you should know that we want this factory to be highly configurable before actually creating an invoice. So rest assured, it will become clearer soon.

Secondly, why the name `new` for the static constructor? The answer is a practical one: within the context of factories, `make` and `create` are often associated with a factory actually producing a result. `new` helps us avoid unnecessary confusion.

Finally, the `create` method: it takes an optional array of extra data to ensure we can always make some last-minute changes in our tests.

With our simple example, we can now create invoices like so:

```php
public function test_case()
{
    $invoice = <hljs type>InvoiceFactory</hljs>::<hljs prop>new</hljs>()-><hljs prop>create</hljs>();
}
```

Before looking at configurability, let's address a little improvement we can make right away: invoice numbers should be unique, so if we create two invoices in one test case, it will break. We don't want to worry about keeping track of invoice numbers in most cases though, so let's have the factory take care of those:

```php
class InvoiceFactory
{
    private static <hljs type>int</hljs> $number = 0;

    public function create(<hljs type>array</hljs> $extra = []): Invoice
    {
        self::$number += 1;

        return <hljs type>Invoice</hljs>::<hljs prop>create</hljs>(<hljs prop>array_merge</hljs>(
            [
                'number' => 'I-' . self::$number,
                // …
            ],
            $extra
        ));   
    }
}
```

## Factories in factories

In the original example, I showed that we might want to create a paid invoice. I was a little naive previously when I assumed this simply meant changing the status field on the invoice model. We also need an actual payment to be saved in the database! Laravel's default factories can handle this with callbacks, which trigger after a model was created; though imagine what happens if you're managing several, maybe even tens of states, each with their own side effects. A simple `$factory->afterCreating` hook just isn't robust enough to manage all this in a sane way.

So, let's turn things around. Let's properly configure our invoice factory, _before_ creating the actual invoice.

```php
class InvoiceFactory
{
    private <hljs type>string</hljs> $status = null;

    public function create(<hljs type>array</hljs> $extra = []): Invoice
    {
        $invoice = <hljs type>Invoice</hljs>::<hljs prop>create</hljs>(<hljs prop>array_merge</hljs>(
            [
                'status' => $this->status ?? <hljs type>PendingInvoiceState</hljs>::class
            ],
            $extra
        ));
        
        if ($invoice->status-><hljs prop>isPaid</hljs>()) {
            <hljs type>PaymentFactory</hljs>::<hljs prop>new</hljs>()-><hljs prop>forInvoice</hljs>($invoice)-><hljs prop>create</hljs>();
        }
        
        return $invoice;
    }

    public function paid(): self
    {
        $clone = clone $this;
        
        $clone->status = <hljs type>PaidInvoiceState</hljs>::class;
        
        return $clone;
    }
}
```

If you're wondering about that `clone` by the way, we'll look at it later.

The thing we've made configurable is the invoice status, just like factory states in Laravel would do, but in our case there's the advantage that our IDE actually knows what we're dealing with:

```php
public function test_case()
{
    $invoice = <hljs type>InvoiceFactory</hljs>::<hljs prop>new</hljs>()
        -><hljs prop>paid</hljs>()
        -><hljs prop>create</hljs>();
}
```

Still, there's room for improvement. Have you seen that check we do after the invoice is created? 

```php
if ($invoice->status-><hljs prop>isPaid</hljs>()) {
    <hljs type>PaymentFactory</hljs>::<hljs prop>new</hljs>()-><hljs prop>forInvoice</hljs>($invoice)-><hljs prop>create</hljs>();
}
```

This can be made more flexible still. We're using a `PaymentFactory` underneath, but what if we want more fine-grained control about how that payment was made? You can imagine there are some business rules about paid invoices that behave differently depending on the type of payment, for example. 

Also, we want to avoid passing too much configuration directly into the `InvoiceFactory`, because it will become a mess very quickly. So how to solve this? 

Here's the answer: we allow the developer to optionally pass a `PaymentFactory` to `InvoiceFactory`, this factory can be configured however the developer wants. Here's how that looks:

```php
public function paid(<hljs type>PaymentFactory</hljs> $paymentFactory = null): self
{
    $clone = clone $this;
    
    $clone->status = <hljs type>PaidInvoiceState</hljs>::class;
    $clone->paymentFactory = $paymentFactory ?? <hljs type>PaymentFactory</hljs>::<hljs prop>new</hljs>();
    
    return $clone;
}
```

And here's how it's used in the `create` method:

```php
if ($this->paymentFactory) {
    $this->paymentFactory-><hljs prop>forInvoice</hljs>($invoice)-><hljs prop>create</hljs>();
}
```

By doing so, a lot of possibilities arise. In this example we're making an invoice that's paid, specifically with a Bancontact payment.

```php
public function test_case()
{
    $invoice = <hljs type>InvoiceFactory</hljs>::<hljs prop>new</hljs>()
        -><hljs prop>paid</hljs>(
            <hljs type>PaymentFactory</hljs>::<hljs prop>new</hljs>()-><hljs prop>type</hljs>(<hljs type>BancontactPaymentType</hljs>::class)
        )
        -><hljs prop>create</hljs>();
}
```

Another example: we want to test how an invoice is handled when it has been paid, but only after the invoice expiration date:

```php
public function test_case()
{
    $invoice = <hljs type>InvoiceFactory</hljs>::<hljs prop>new</hljs>()
        -><hljs prop>expiresAt</hljs>('2020-01-01')
        -><hljs prop>paid</hljs>(
            <hljs type>PaymentFactory</hljs>::<hljs prop>new</hljs>()-><hljs prop>at</hljs>('2020-01-20')
        )
        -><hljs prop>create</hljs>();
}
```

With just a few lines of code, we get a lot more flexibility.

## Immutable factories

Now what about that cloning earlier? Why is it important to make factories immutable? See, sometimes you need to make several models with the same factory, but with small differences. Instead of creating a new factory object for each model, you could reuse the original factory object, and only change the things you need.

If you're not using immutable factories though, there's a chance that you'll end up with data you didn't actually want. Take the example of the invoice payments, say we need two invoices on the same date, one paid and one pending.

```php
$invoiceFactory = <hljs type>InvoiceFactory</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>expiresAt</hljs>(<hljs type>Carbon</hljs>::<hljs prop>make</hljs>('2020-01-01'));

$invoiceA = $invoiceFactory-><hljs prop>paid</hljs>()-><hljs prop>create</hljs>();
$invoiceB = $invoiceFactory-><hljs prop>create</hljs>();
```

If our `paid` method wasn't immutable, it would mean that `$invoiceB` would also be a paid invoice! Sure, we could micro-manage every model creation, but that takes away from the flexibility of this pattern. That's why immutable functions are great: you can set up a base factory, and reuse it throughout your tests, without worrying about unintended side effects!

---

Built upon these two principles: configuring factories within factories and making them immutable; a lot of possibilities arise. Sure, it takes some time to actually write these factories, but they also _save_ lots of time over the course of development. In my experience, they are well worth the overhead, as there's much more to gain from them compared to their cost.

Ever since using this pattern, I never looked back at Laravel's built-in factories. There's just too much to gain from this approach.

One downside I can come up with, is that you'll need a little more extra code to create several models at once. Though if you want to, you can easily add a small piece of code in a base factory class, something like this:

```php
abstract class Factory
{
    // Concrete factory classes should add a return type 
    abstract public function create(<hljs type>array</hljs> $extra = []);

    public function times(<hljs type>int</hljs> $times, <hljs type>array</hljs> $extra = []): Collection
    {
        return <hljs prop>collect</hljs>()
            -><hljs prop>times</hljs>($times)
            -><hljs prop>map</hljs>(<hljs keyword>fn</hljs>() => $this-><hljs prop>create</hljs>($extra));
    }
}
```

Also keep in mind that you can use these factories for other stuff too, not just models. I've been also using them extensively to set up [DTOs](/blog/laravel-beyond-crud-02-working-with-data), and sometimes even request classes.

I'd suggest to play around with them the next time you're in need of test factories. I can assure you they will not disappoint!
