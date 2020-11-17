If you've used the [null coalescing operator](/blog/shorthand-comparisons-in-php#null-coalescing-operator) in the past, you probably also noticed its shortcomings: null coalescing doesn't work on method calls. Instead you need intermediate checks, or rely on `<hljs prop>optional</hljs>` helpers provided by some frameworks:

```php
$startDate = $booking-><hljs prop>getStartDate</hljs>();

$dateAsString = $startDate ? $startDate-><hljs prop>asDateTimeString</hljs>() : null;
```

The nullsafe operator provides functionality similar to null coalescing, but also supports method calls. Instead of writing this:

```php
$country =  null;
 
if ($session !== null) {
    $user = $session-><hljs prop>user</hljs>;
 
    if ($user !== null) {
        $address = $user-><hljs prop>getAddress</hljs>();
 
        if ($address !== null) {
            $country = $address-><hljs prop>country</hljs>;
        }
    }
}
``` 

PHP 8 allows you to write this:

```php
$country = $session?-><hljs prop>user</hljs>?-><hljs prop>getAddress</hljs>()?-><hljs prop>country</hljs>;
```

Let's take a look at what this new operator can and cannot do!

## Nullsafe operator in depth

Let's start by addressing the most important question: what exactly is the difference between the null coalescing operator and the nullsafe operator?

Let's take a look at this example:

```php
class Order
{
    public ?<hljs type>Invoice</hljs> <hljs prop>$invoice</hljs> = null;
}

$order = new <hljs type>Order</hljs>();
```

Here we have an `<hljs type>Order</hljs>` object which has an optional relation to an `<hljs type>Invoice</hljs>` object. Now image we'd want to get the invoice's number (if the invoice isn't null). You could do this both with the null coalescing operator and the nullsafe operator:

```php
<hljs prop>var_dump</hljs>($order-><hljs prop>invoice</hljs>?-><hljs prop>number</hljs>);
<hljs prop>var_dump</hljs>($order-><hljs prop>invoice</hljs>-><hljs prop>number</hljs> ?? null);
```

So what's the difference? While you could use both operators to achieve the same result in this example, they also have specific edge cases only one of them can handle. For example, you can use the null coalescing operator in combination with array keys, while the nullsafe operator can't handle them:

```php
$array = [];

<hljs prop>var_dump</hljs>($array['key']-><hljs prop>foo</hljs> ?? null);
```

```
<hljs prop>var_dump</hljs>($array<hljs striped>['key']?-></hljs><hljs prop>foo</hljs>);

<hljs red full>Warning: Undefined array key "key"</hljs>
```

The nullsafe operator, on the other hand, can work with method calls, while the null coalescing operator can't. Imagine an `<hljs type>Invoice</hljs>` object like so:

```php
class Invoice
{
    public function getDate(): ?DateTime { /* … */ }
    
    // …
}

$invoice = new <hljs type>Invoice</hljs>();
```

You could use the nullsafe operator to call `<hljs type>format</hljs>` on the invoice's date, even when it's `<hljs keyword>null</hljs>`:

```php
<hljs prop>var_dump</hljs>($invoice-><hljs prop>getDate</hljs>()?-><hljs prop>format</hljs>('Y-m-d'));

// null
```

While the null coalescing operator would crash:

```php
<hljs prop>var_dump</hljs>($invoice-><hljs prop>getDate</hljs>()-><hljs prop>format</hljs>('Y-m-d') ?? null);

<hljs text red full>Fatal error: Uncaught Error: Call to a member function format() on null</hljs>
```

{{ cta:flp_mail }}

### Short circuiting

Sometimes you could use either the null coalescing or nullsafe operator, and other times you'd need to use a specific one. The difference is that the nullsafe operator uses a form of "short circuiting": writing `?->` will cause PHP to look at whats on the lefthand side of this operator, if it's `<hljs keyword>null</hljs>` then the righthand side will simply be discarded. The null coalescing operator is actually an `<hljs keyword>isset</hljs>` call in disguise on its lefthand operand, which doesn't support short circuiting.

Short circuiting also means that when writing something like this:

```php
$foo?-><hljs prop>bar</hljs>(<hljs prop>expensive_function</hljs>());
```

`<hljs prop>expensive_function</hljs>` would only be executed if `$foo` is actually not `<hljs keyword>null</hljs>`.

### Nested nullsafe operators

It's possible to nest several nullsafe operator calls like so:

```php
$foo?-><hljs prop>bar</hljs>?-><hljs prop>baz</hljs>()?-><hljs prop>boo</hljs>?-><hljs prop>baa</hljs>();
```

### Only for reading data

You cannot use the nullsafe operator to write data to objects:

```php
<hljs striped>$offer?-><hljs prop>invoice</hljs>?-><hljs prop>date</hljs> = new <hljs type>DateTime</hljs>();</hljs> 
```

{{ cta:mail }}

The nullsafe operator is definitely a missing piece of the puzzle finally added in PHP. Given its dynamic nature, it feels good to have a smooth way of dealing with `<hljs keyword>null</hljs>`. The difference and overlap between the nullsafe operator and null coalescing operator feels a bit confusing at first, but I'm sure we'll get used to it.

