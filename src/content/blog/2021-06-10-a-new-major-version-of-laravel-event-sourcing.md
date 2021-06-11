Today, we released a new version of [spatie/laravel-event-sourcing](*https://github.com/spatie/laravel-event-sourcing), version 5 is probably one of the largest releases since the beginning of the package, we've worked several months on it and have been testing it extensively already in our own projects.

Credit where credit is due, many new features were inspired by [Axon](*https://docs.axoniq.io/reference-guide/),  
a popular event sourcing framework in the Java world, and several people pitched in during the development process.

In this post, I'll walk you through all significant changes, but first I want to mention the course that we've built at Spatie over the last months about event sourcing. If you're working on an event sourced project or thinking about starting one, this course will be of great help. Check it out on [https://event-sourcing-laravel.com/](*https://event-sourcing-laravel.com/)!

{{ cta:like }}

### Consistent event handling

If you've used previous versions of our package, you might have struggled with how event handlers were registered across classes. Aggregate roots required you to write `applyEventName` functions, while projectors and reactors had an explicit event mapping. 

Whatever class your writing will now register event handlers the same way: by looking at the type of the event. You don't need any more configuration or naming conventions anymore. 

```php
class CartAggregateRoot extends AggregateRoot
{
    // …
    
    public function onCartAdded(<hljs type>CartAdded</hljs> $event): void
    {
        // Any `CartAdded` event will automatically be matched to this handler
    }
}
```
```php
class CartProjector extends Projector
{
    public function onCartAdded(<hljs type>CartAdded</hljs> $event): void
    {
        // The same goes for projectors and reactors.
    }
}
```

### Event Queries

Event queries are a new feature that allow you to easily query an event stream without building database projections. You can think of them as in-memory projections that are rebuilt every time you call them. 

Here's an example of it in action:

```php
class EarningsForProductAndPeriod extends EventQuery
{
    <hljs keyword>private</hljs> <hljs type>int</hljs> <hljs prop>$totalPrice</hljs> = 0;
    
    <hljs keyword>public</hljs> function __construct(
        <hljs keyword>private</hljs> <hljs type>Period</hljs> <hljs prop>$period</hljs>,
        <hljs keyword>private</hljs> <hljs type>Collection</hljs> <hljs prop>$products</hljs>
    ) {
        <hljs type>EloquentStoredEvent</hljs>::<hljs prop>query</hljs>()
            -><hljs prop>whereEvent</hljs>(<hljs type>OrderCreated</hljs>::class)
            -><hljs prop>whereDate</hljs>('created_at', '>=', $this-><hljs prop>period</hljs>-><hljs prop>getStart</hljs>())
            -><hljs prop>whereDate</hljs>('created_at', '<=', $this-><hljs prop>period</hljs>-><hljs prop>getEnd</hljs>())
            -><hljs prop>cursor</hljs>()
            -><hljs prop>each</hljs>(
                <hljs keyword>fn</hljs> (<hljs type>EloquentStoredEvent</hljs> $event) => $this-><hljs prop>apply</hljs>($event)
            );
    }

    protected function applyOrderCreated(<hljs type>OrderCreated</hljs> $orderCreated): void 
    {
        $orderLines = <hljs prop>collect</hljs>($orderCreated-><hljs prop>orderData</hljs>-><hljs prop>orderLineData</hljs>);

        $totalPriceForOrder = $orderLines
            -><hljs prop>filter</hljs>(function (<hljs type>OrderLineData</hljs> $orderLineData) {
                <hljs keyword>return</hljs> $this-><hljs prop>products</hljs>-><hljs prop>first</hljs>(
                    <hljs keyword>fn</hljs>(<hljs type>Product</hljs> $product) => $orderLineData-><hljs prop>productEquals</hljs>($product)
                ) !== null;
            })
            -><hljs prop>sum</hljs>(
                <hljs keyword>fn</hljs>(<hljs type>OrderLineData</hljs> $orderLineData) => $orderLineData-><hljs prop>totalPriceIncludingVat</hljs>
            );

        $this-><hljs prop>totalPrice</hljs> += $totalPriceForOrder;
    }
}
```

Note that these examples come from the [Event Sourcing in Laravel](*https://event-sourcing-laravel.com/) book.

### Aggregate Partials

Aggregate partials allow you to split large aggregate roots into separate classes, while still keeping everything contained within the same aggregate. Partials can record and apply events just like an aggregate root, and can share state between them and their associated aggregate root.

Here's an example of an aggregate partial that handles everything related to item management within a shopping cart:

```php
class CartItems extends AggregatePartial
{
    // …
    
    public function addItem(
        <hljs type>string</hljs> $cartItemUuid, 
        <hljs type>Product</hljs> $product, 
        <hljs type>int</hljs> $amount
    ): self {
        $this-><hljs prop>recordThat</hljs>(new <hljs type>CartItemAdded</hljs>(
            <hljs prop>cartItemUuid</hljs>: $cartItemUuid,
            <hljs prop>productUuid</hljs>: $product-><hljs prop>uuid</hljs>,
            <hljs prop>amount</hljs>: $amount,
        ));

        return $this;
    }

    protected function applyCartItemAdded(
        <hljs type>CartItemAdded</hljs> $cartItemAdded
    ): void {
        $this-><hljs prop>cartItems</hljs>[$cartItemAdded-><hljs prop>cartItemUuid</hljs>] = null;
    }
}
```

And this is how the cart aggregate root would use it:

```php
class CartAggregateRoot extends AggregateRoot
{
    protected <hljs type>CartItems</hljs> <hljs prop>$cartItems</hljs>;

    public function __construct()
    {
        $this-><hljs prop>cartItems</hljs> = new <hljs type>CartItems</hljs>($this);
    }

    public function addItem(
        <hljs type>string</hljs> $cartItemUuid,
        <hljs type>Product</hljs> $product,
        <hljs type>int</hljs> $amount
    ): self {
        if (! $this-><hljs prop>state</hljs>-><hljs prop>changesAllowed</hljs>()) {
            throw new <hljs type>CartCannotBeChanged</hljs>();
        }

        $this-><hljs prop>cartItems</hljs>-><hljs prop>addItem</hljs>($cartItemUuid, $product, $amount);

        return $this;
    }
```

Aggregate partials come with the same testing capabilities as aggregate roots, and are a useful way of keeping aggregate-related code maintainable.

### Command bus

We've added a command bus that can automatically map commands to handlers on aggregate roots:

```php
namespace <hljs type>Spatie\Shop\Cart\Commands</hljs>;

use <hljs type>Spatie\Shop\Support\EventSourcing\Attributes\AggregateUuid</hljs>;
use <hljs type>Spatie\Shop\Support\EventSourcing\Attributes\HandledBy</hljs>;
use <hljs type>Spatie\Shop\Support\EventSourcing\Commands\Command</hljs>;

#[<hljs type>HandledBy</hljs>(<hljs type>CartAggregateRoot</hljs><hljs text>::class</hljs>)]
class AddCartItem implements Command
{
    <hljs keyword>public</hljs> function __construct(
        <hljs comment>#[<hljs type>AggregateUuid</hljs>]</hljs> <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$cartUuid</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$cartItemUuid</hljs>,
        <hljs keyword>public</hljs> <hljs type>Product</hljs> <hljs prop>$product</hljs>,
        <hljs keyword>public</hljs> <hljs type>int</hljs> <hljs prop>$amount</hljs>,
    ) {
    }
}
```

Whenever this command is dispatched, it will automatically be captured and handled by the associated aggregate root. It even works with aggregate partials:

```php
class CartItems extends AggregatePartial
{
    // …
    
    public function addItem(<hljs type>AddCartItem</hljs> $addCartItem): self
    {
        // …
    }
    
    public function removeItem(<hljs type>RemoveCartItem</hljs> $removeCartItem): self
    {
        // …
    }
}    
```

---

Besides these new features, there are also some quality-of-life changes across the board:

- The minimum required PHP version is now 8.0
- The minimum required Laravel version is now 8.0
- There's better support for [handling concurrency](*https://github.com/spatie/laravel-event-sourcing/discussions/214) within the same aggregate root instance
- There's a way to [fake reactors and projectors](*https://github.com/spatie/laravel-event-sourcing/discussions/181)
- There's an [improved event query builder](*https://github.com/spatie/laravel-event-sourcing/blob/v5/src/StoredEvents/Models/EloquentStoredEventQueryBuilder.php)
- And [more](*https://github.com/spatie/laravel-event-sourcing/blob/v5/CHANGELOG.md)

---

All in all, I'm very exited for this new release. All the new features are also used in our real-life projects, so we know from experience how useful they are in complex applications. Of course, a blog post can't discuss all the details and the thought process behind this new version, so make sure to read [the book](*https://event-sourcing-laravel.com/) if you want in-depth knowledge about all of these features, and more.

{{ cta:mail }}
