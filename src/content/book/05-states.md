> The state pattern is one of the best ways to add state-specific behaviour to models, while still keeping them clean.

This chapter will talk about the state pattern, and specifically how to apply it to models. You can think of this chapter as an extension to [chapter 4](/blog/laravel-beyond-crud-04-models), where I wrote about how we aim to keep our model classes manageable by preventing them from handling business logic.

Moving business logic away from models poses a problem though with a very common use case: what to do with model states? 

An invoice can be pending or paid, a payment can be failed or succeeded. Depending on the state, a model must behave differently; how do we bridge this gap between models and business logic?

States and transitions between them, are a frequent use case in large projects; so frequent that they deserve a chapter on their own.

{{ ad:carbon }}

## The state pattern

At its core, the state pattern is a simple pattern, yet it allows for very powerful functionality. Let's take the example of invoices again: an invoice can be pending or paid. To start with, I will give a very simple example, because I want you to understand how the state pattern allows us lots of flexibility.

Say the invoice overview should show a badge representing the state of that invoice, it's coloured orange when pending and green when paid.

A naive fat model approach would do something like this:

```php
class Invoice extends Model
{
    // …
    
    public function getStateColour(): string
    {
        if ($this->state-><hljs prop>equals</hljs>(<hljs type>InvoiceState</hljs>::<hljs prop>PENDING</hljs>())) {
            return 'orange';
        }
    
        if ($this->state-><hljs prop>equals</hljs>(<hljs type>InvoiceState</hljs>::<hljs prop>PAID</hljs>())) {
            return 'green'
        }

        return 'gray';
    }
}
``` 

Since we're using some kind of enum class to represent the state value, we could improve on this like so:

```php
class Invoice extends Model
{
    // …
    
    public function getStateColour(): string
    {
        return $this->state->getColour();
    }
}
```

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

As a sidenote, I assume you'd be using the [myclabs/php-enum](*https://github.com/myclabs/php-enum) package in this case. One more improvement, for good measure, we could write the above a little shorter by using arrays.

```php
class InvoiceState extends Enum
{
    public function getColour(): string
    {
        return [
            self::PENDING => 'orange',
            self::PAID => 'green',
        ][$this->value] ?? 'gray';
    }
}
```

Whatever approach you prefer, in essence you're listing all available options, checking if one of them matches the current one, and doing something based on the outcome. It's a big if/else statement, whichever syntactic sugar you prefer.

Using this approach, we add a responsibility, either to the model or the enum class: _it_ has to know what a specific state should do, _it_ has to know how a state works. The state pattern turns this the other way around: it treats "a state" as a first-class citizen of our codebase. Every state is represented by a separate class, and each of these classes _acts_ upon a subject.

Is that difficult to grasp? Let's take it step by step.

We start with an abstract class `InvoiceState`, this class describes all functionality that concrete invoice states can provide. In our case we want a state to provide a colour.

```php
abstract class InvoiceState
{
    abstract public function colour(): string;
}
``` 

Next, we make two classes, each represents a concrete state.

```php
class PendingInvoiceState extends InvoiceState
{
    public function colour(): string
    {
        return 'orange';
    }
}
```

```php
class PaidInvoiceState extends InvoiceState
{
    public function colour(): string
    {
        return 'green';
    }
}
```

The first thing to notice is that each of these classes can easily be unit tested on their own.

```php
class InvoiceStateTest extends TestCase
{
    /** @test */
    public function the_colour_of_pending_is_orange
    {   
        $state = new <hljs type>PendingInvoiceState</hljs>();
        
        $this-><hljs prop>assertEquals</hljs>('orange', $state-><hljs prop>colour</hljs>());
    }
}
```

Second, you should note that colours is a naive example used to explain the pattern. You might as well have more complex business logic encapsulated by a state. Take this example: must an invoice be paid? This of course depends on the state, whether it was already paid or not, but might as well depend on the type of invoice we're dealing with. Say our system supports credit notes which don't have to be paid, or it allows for invoices with a price of 0. This business logic can be encapsulated by the state classes. 

There's one thing missing to make this functionality work though: we need to be able to look at the model from within our state class, if we're going to decide whether or not that invoice must be paid. This is why we have our abstract `InvoiceState` parent class; let's add the required methods over there.


```php
abstract class InvoiceState
{
    /** @var Invoice */
    protected $invoice;

    public function __construct(<hljs type>Invoice</hljs> $invoice) { /* … */ }

    abstract public function mustBePaid(): bool;
    
    // …
}
```

And implement them for each concrete state.

```php
class PendingInvoiceState extends InvoiceState
{
    public function mustBePaid(): bool
    {
        return $this->invoice->total_price > 0
            && $this->invoice->type-><hljs prop>equals</hljs>(<hljs type>InvoiceType</hljs>::<hljs prop>DEBIT</hljs>());
    }
    
    // …
}
```

```php
class PaidInvoiceState extends InvoiceState
{
    public function mustBePaid(): bool
    {
        return false;
    }
    
    // …
}
```

Again we can write simple unit tests for each state, and our invoice model can simply do this.

```php
class Invoice extends Model
{
    public function getStateAttribute(): InvoiceState
    {
        return new $this->state_class($this);
    }
    
    public function mustBePaid(): bool
    {
        return $this->state-><hljs prop>mustBePaid</hljs>();
    } 
}
```

Finally, in the database we can save the concrete model state class in the `state_class` field and we're done. Obviously doing this mapping manually (saving and loading from and to the database) gets tedious very quickly. That's why I wrote [a package](*https://github.com/spatie/laravel-model-states) which takes care of all the grunt work for you.

State-specific behaviour, in other words "the state pattern", is only half of the solution though; we still need to handle transitioning the invoice state from one to another, and ensuring only specific states may transition to others. So let's look at state transitions.

## Transitions

Remember how I talked about moving business logic away from models, and only allowing them to provide data in a workable way from the database? The same thinking can be applied to states and transitions. We should avoid side effects when using states, things like making changes in the database, sending mails, etc. States should be used to _read_ or provide data. Transitions on the other hand don't provide anything. Rather, they make sure our model state is correctly transitioned from one to another leading to acceptable side effects.

Splitting these two concerns in separate classes gives us the same advantages I wrote about again and again: better testability and reduced cognitive load. Allowing a class to only have one responsibility makes it easier to split a complex problem into several easy-to-grasp bits. 

So transitions: a class which will take a model, an invoice in our case, and change that invoice's state — if allowed — to another one. In some cases there might be small side effects like writing a log message or sending a notification about the state transition. A naive implementation might look something like this.

```php
class PendingToPaidTransition
{
    public function __invoke(<hljs type>Invoice</hljs> $invoice): Invoice
    {
        if (! $invoice-><hljs prop>mustBePaid</hljs>()) {
            throw new <hljs type>InvalidTransitionException</hljs>(self::class, $invoice);
        }

        $invoice->status_class = <hljs type>PaidInvoiceState</hljs>::class;
        $invoice-><hljs prop>save</hljs>();
    
        <hljs type>History</hljs>::<hljs prop>log</hljs>($invoice, "Pending to Paid");
    }
}
``` 

Again there are many things you can do with this basic pattern:

- Define all allowed transitions on the model
- Transition a state directly to another one, by using a transition class under the hood
- Automatically determine what state to transition to based on a set of parameters

Again the package I mentioned before adds support for transitions, as well as basic transition management. If you want complex state machines though, you might want to look at other packages. I listed an example in the footnotes below. 

## States without transitions

When we think of "state", we often think they cannot exist without transitions. However, that's not true: an object can have a state that never changes and transitions aren't required to apply the state pattern. Why is this important? Well, take a look again at our `PendingInvoiceState::mustBePaid` implementation:

```php
class PendingInvoiceState extends InvoiceState
{
    public function mustBePaid(): bool
    {
        return $this->invoice->total_price > 0
            && $this->invoice->type-><hljs prop>equals</hljs>(<hljs type>InvoiceType</hljs>::<hljs prop>DEBIT</hljs>());
    }
}
```

Since we want to use the state pattern to reduce brittle if/else blocks in our code, can you guess where I'm going with this? Have you considered that `$this->invoice->type->equals(InvoiceType::DEBIT())` is in fact an if statement in disguise?

`InvoiceType` in fact could very well also apply the state pattern! It's simply a state that likely will never change for a given object. Take a look at this.

```php
abstract class InvoiceType
{
    /** @var Invoice */
    protected $invoice;
    
    // …

    abstract public function mustBePaid(): bool;
}
``` 

```php
class CreditInvoiceType extends InvoiceType
{
    public function mustBePaid(): bool
    {
        return false
    }
}
```

```php
class DebitInvoiceType extends InvoiceType
{
    public function mustBePaid(): bool
    {
        return true;
    }
}
```

Now we can refactor our `PendingInvoiceState::mustBePaid` like so.

```php
class PendingInvoiceState extends InvoiceState
{
    public function mustBePaid(): bool
    {
        return $this->invoice->total_price > 0
            && $this->invoice->type-><hljs prop>mustBePaid</hljs>();
    }
}
```

Reducing if/else statements in our code allows that code to be more linear, which in turn is easier to reason about. I would highly recommend to take a look at [Sandi Metz's talk](*https://www.youtube.com/watch?v=29MAL8pJImQ) about this exact topic.

--- 

The state pattern is, in my opinion, awesome. You're never stuck again writing huge if/else statements — in real life there are often more than two invoice states — and it allows for clean and testable code.

It's a pattern that you can incrementally introduce in your existing code bases, and I'm sure it will be a huge help keeping the project maintainable in the long run.
