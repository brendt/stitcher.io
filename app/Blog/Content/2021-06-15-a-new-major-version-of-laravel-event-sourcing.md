---
title: 'A new major version of Laravel Event Sourcing'
meta:
    description: "A look at what's new in spatie/laravel-event-sourcing"
footnotes:
    - { title: 'Opinion-driven design', link: /blog/opinion-driven-design }
    - { title: 'What about typed request classes?', link: /blog/what-about-request-classes }
    - { title: 'What about config builders?', link: /blog/what-about-config-builders }
---

Today, we released a new version of [spatie/laravel-event-sourcing](*https://github.com/spatie/laravel-event-sourcing), version 5 is probably one of the largest releases since the beginning of the package, we've worked several months on it and have been testing it extensively already in our own projects.

Credit where credit is due, many new features were inspired by [Axon](*https://docs.axoniq.io/reference-guide/),  
a popular event sourcing framework in the Java world, and several people pitched in during the development process.

In this post, I'll walk you through all significant changes, but first I want to mention the course that we've built at Spatie over the last months about event sourcing. If you're working on an event sourced project or thinking about starting one, this course will be of great help. Check it out on [https://event-sourcing-laravel.com/](*https://event-sourcing-laravel.com/)!

{{ cta:like }}

### Consistent event handling

If you've used previous versions of our package, you might have struggled with how event handlers were registered across classes. Aggregate roots required you to write `applyEventName` functions, while projectors and reactors had an explicit event mapping. 

Whatever class you're writing will now register event handlers the same way: by looking at the type of the event. You don't need any more configuration or naming conventions anymore. 

```php
class CartAggregateRoot extends AggregateRoot
{
    // …
    
    public function onCartAdded(CartAdded $event): void
    {
        // Any `CartAdded` event will automatically be matched to this handler
    }
}
```
```php
class CartProjector extends Projector
{
    public function onCartAdded(CartAdded $event): void
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
    private int $totalPrice = 0;
    
    public function __construct(
        private Period $period,
        private Collection $products
    ) {
        EloquentStoredEvent::query()
            ->whereEvent(OrderCreated::class)
            ->whereDate('created_at', '>=', $this->period->getStart())
            ->whereDate('created_at', '<=', $this->period->getEnd())
            ->cursor()
            ->each(
                fn (EloquentStoredEvent $event) => $this->apply($event)
            );
    }

    protected function applyOrderCreated(OrderCreated $orderCreated): void 
    {
        $orderLines = collect($orderCreated->orderData->orderLineData);

        $totalPriceForOrder = $orderLines
            ->filter(function (OrderLineData $orderLineData) {
                return $this->products->first(
                    fn(Product $product) => $orderLineData->productEquals($product)
                ) !== null;
            })
            ->sum(
                fn(OrderLineData $orderLineData) => $orderLineData->totalPriceIncludingVat
            );

        $this->totalPrice += $totalPriceForOrder;
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
        string $cartItemUuid, 
        Product $product, 
        int $amount
    ): self {
        $this->recordThat(new CartItemAdded(
            cartItemUuid: $cartItemUuid,
            productUuid: $product->uuid,
            amount: $amount,
        ));

        return $this;
    }

    protected function applyCartItemAdded(
        CartItemAdded $cartItemAdded
    ): void {
        $this->cartItems[$cartItemAdded->cartItemUuid] = null;
    }
}
```

And this is how the cart aggregate root would use it:

```php
class CartAggregateRoot extends AggregateRoot
{
    protected CartItems $cartItems;

    public function __construct()
    {
        $this->cartItems = new CartItems($this);
    }

    public function addItem(
        string $cartItemUuid,
        Product $product,
        int $amount
    ): self {
        if (! $this->state->changesAllowed()) {
            throw new CartCannotBeChanged();
        }

        $this->cartItems->addItem($cartItemUuid, $product, $amount);

        return $this;
    }
```

Aggregate partials come with the same testing capabilities as aggregate roots, and are a useful way of keeping aggregate-related code maintainable.

### Command bus

We've added a command bus that can automatically map commands to handlers on aggregate roots:

```php
namespace Spatie\Shop\Cart\Commands;

use Spatie\Shop\Support\EventSourcing\Attributes\AggregateUuid;
use Spatie\Shop\Support\EventSourcing\Attributes\HandledBy;
use Spatie\Shop\Support\EventSourcing\Commands\Command;

#[HandledBy(CartAggregateRoot::class)]
class AddCartItem implements Command
{
    public function __construct(
        #[AggregateUuid] public string $cartUuid,
        public string $cartItemUuid,
        public Product $product,
        public int $amount,
    ) {
    }
}
```

Whenever this command is dispatched, it will automatically be captured and handled by the associated aggregate root. It even works with aggregate partials:

```php
class CartItems extends AggregatePartial
{
    // …
    
    public function addItem(AddCartItem $addCartItem): self
    {
        // …
    }
    
    public function removeItem(RemoveCartItem $removeCartItem): self
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
- There's an [improved event query builder](*https://github.com/spatie/laravel-event-sourcing/blob/main/src/StoredEvents/Models/EloquentStoredEventQueryBuilder.php)
- And [more](*https://github.com/spatie/laravel-event-sourcing/blob/v5/CHANGELOG.md)

---

All in all, I'm very exited for this new release. All the new features are also used in our real-life projects, so we know from experience how useful they are in complex applications. Of course, a blog post can't discuss all the details and the thought process behind this new version, so make sure to read [the book](*https://event-sourcing-laravel.com/) if you want in-depth knowledge about all of these features, and more.

{{ cta:mail }}
