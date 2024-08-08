This post is a followup on a [previous post](/blog/organise-by-domain) I wrote on how to organise your Laravel projects by domains. You're not required to read that post beforehand, though it might offer some more context to what I'll write today.

{{ ad:carbon }}

Let's start by describing the goals of domain oriented projects, what they are, and what not.

This paradigm describes a set of rules and patterns to structure Laravel projects in such a way that they stay maintainable in the long run, even when several developers work on them. You'll notice that many practices I'll share today differ quite a lot from the default Laravel way.

While Laravel's default project structure might give you a very quick start and almost no overhead, we know from experience that this approach doesn't scale very well in larger projects, especially when you're working with a team of multiple developers.

To be clear: we're talking about projects that take more than a few months of development time, and teams of three or more developers. If you want a more in-depth feel about the size and scope of the projects where we use this approach, you can check out my blog post [here](/blog/a-project-at-spatie).

On a final note: many of the principles I describe today are borrowed from DDD and hexagonal architectures. We find that some principles in these paradigms are overkill for the scope of our projects, which is why we took the liberty to taylor them to our needs.

So let's dive in.

## Structure and patterns

At the end of this post, you'll have noticed that domain oriented projects differ in two areas from default Laravel projects: 

- Their directory structure, to better group related business concepts together, instead of splitting them over several namespaces.
- The use of specific design patterns, which often differ from the default Laravel approach.

Here's a trimmed down version of what a domain oriented project looks like:

```txt
<hljs comment>One specific domain folder per business concept</hljs>
\Domain\Contracts\
    ├── Actions
    ├── QueryBuilders
    ├── Collections
    ├── DataTransferObjects
    ├── Events
    ├── Exceptions
    ├── Listeners
    ├── Models
    ├── Rules
    └── States

<hljs comment>The admin HTTP application</hljs>
\App\Admin\
    ├── Controllers
    ├── Middlewares
    ├── Requests
    ├── Resources
    └── ViewModels

<hljs comment>The API HTTP application</hljs>
\App\Api\
    ├── Controllers
    ├── Middlewares
    ├── Requests
    └── Resources

<hljs comment>The console application</hljs>
\App\Console\
    └── Commands
```

> Note that if you want two different root namespaces, you'll need to make some changes in how Laravel boots itself. While it's possible, you might prefer too keep everything in the `\App` root namespace: `\App\Domain\`. I find this a little more confusing, but it might be preferable to stay closer to Laravel's default namespacing convention.

Here's the general rule: all business logic is grouped together into domain groups, and a project can have multiple applications consuming the domain.

How domains are grouped together will entirely depend on the scope of your project. We often started by grouping concepts together, and refactoring them to separate domains afterwards when it became clear that the current group was growing too large. 

Here are few examples of domain groups: `Contracts`, `Invoices`, `Users`, `Reservations`, … All will depend on the scope of your project. There are no strict boundaries that define one domain group. It's possible to have two or three "entry point" models in one group, if it makes sense to keep them together.

Another characteristic of our approach is that communication between domains is allowed, although you're encouraged to keep it to a minimum. If you don't want to cross domain boundaries, you're free to use an event-based system, but it's not required.

These two characteristics mentioned above: multiple entry points and cross domain communication; are examples of why our approach is less strict than DDD. Because of the scope of our projects — they are big but not ginormous — we feel that allowing this kind of flexibility is ok.

We've talked about domains, so what about applications?

Think of an application as a standalone app, living in the project, being able to consume everything the domain exposes. An "application" could be an admin app, an API, a console application, a third party integration. They generally don't talk to each other, but will use the domain extensively. 

Later in this post I will zoom into the structure of an HTTP application, and share thoughts on how to structure it internally. First, we'll look at each common domain folder in depth. Note that this is not an exhaustive list, it's entirely possible you'll add your own concepts to it, which again is fine.

### Models, QueryBuilders and Collections

The center point of almost every project, is data. An application's goal is to provide, interpret and manipulate data in whatever way the business wants. That's why almost always, models are the starting point of your project.

Most domain groups will represent one or more models that work together. A good example would be the `Invoice`, `InvoiceLine` and `Invoicee` models, all belonging in the `Invoices` domain.

If you've worked in a large Laravel project, you know it's possible for models to grow very large, very fast. That's why it's a good rule of thumb to separate several concerns, concerns that Laravel groups together by default: data, queries and collections.

Try to think of a model class's responsibility as to represent and work with the data of your database in a convenient way. This means you can add simple getters and setters, accessors and mutators, casts and relations. 

Scopes on the other hand should go in a separate builder class. Believe it or not: custom builder classes are actually the normal way of using eloquent; scopes are simply syntactic sugar on top of them. Here's how you create and use custom builder classes:

```php
namespace <hljs type>Domain\Invoices\QueryBuilders</hljs>;

use <hljs type>Domain\Invoices\States\Paid</hljs>;
use <hljs type>Illuminate\Database\Eloquent\Builder</hljs>;

final class InvoiceQueryBuilder extends Builder
{
    public function wherePaid(): self
    {
        return $this-><hljs prop>whereState</hljs>('status', <hljs type>Paid</hljs>::class);
    }
}

```

By overriding the `newEloquentBuilder` method in your model, Laravel will use your custom query builder from now on:

```php
namespace <hljs type>Domain\Invoices\Models</hljs>;

use <hljs type>Domain\Invoices\QueryBuilders\InvoiceQueryBuilder</hljs>;

final class Invoice extends Model 
{
    public function newEloquentBuilder($query): <hljs type>InvoiceQueryBuilder</hljs>
    {
        return new <hljs type>InvoiceQueryBuilder</hljs>($query);
    }
}
```

Using a similar approach, we can also provide custom collection classes for relations. Laravel has great collection support, though you often end up with long chains of collection functions either in the model or in the application layer. This is something we want to avoid. 

Here's an example of a custom collection class, note that it's entirely possible to combine several methods into new ones, avoiding long collection function chains in other places:

```php
namespace <hljs type>Domain\Invoices\Collections</hljs>;

use <hljs type>Domain\Invoices\Models\InvoiceLines</hljs>;
use <hljs type>Illuminate\Database\Eloquent\Collection</hljs>;

final class InvoiceLineCollection extends Collection
{
    public function creditLines(): self
    {
        return $this-><hljs prop>filter</hljs>(function (<hljs type>InvoiceLine</hljs> $invoiceLine) {
            return $invoiceLine-><hljs prop>isCreditLine</hljs>();
        });
    }
}
```

This is how you link a collection to a model; `InvoiceLine`, in this case:

```php
namespace <hljs type>Domain\Invoices\Models</hljs>;

use <hljs type>Domain\Invoices\Collection\InvoiceLineCollection</hljs>;

final class InvoiceLine extends Model 
{
    public function newCollection(<hljs type>array</hljs> $models = []): <hljs type>InvoiceLineCollection</hljs>
    {
        return new <hljs type>InvoiceLineCollection</hljs>($models);
    }

    public function isCreditLine(): <hljs type>bool</hljs>
    {
        return $this->price < 0.0;
    }
}
```

And here's how it's used:

```php
$invoice
    ->invoiceLines
    -><hljs prop>creditLines</hljs>()
    -><hljs prop>map</hljs>(function (<hljs type>InvoiceLine</hljs> $invoiceLine) {
        // …
    });
```

Try to keep your models clean and data-oriented, instead of making them business logic heavy. If you wonder where business logic is handled, just keep on reading.

### States

One piece of the puzzle in handling business logic are states. Model states are essential in grouping state-related logic, and also to ensure valid state transitions.

In the projects we work on, there's a lot of state changes going on all the time. Think back to the example of an invoice: it can be pending, paid or canceled. Each state comes with its own set of rules, and each transition between states requires side effects to happen.

This is where we apply the [state pattern](*https://en.wikipedia.org/wiki/State_pattern) and use a light version of [state machines](*https://www.youtube.com/watch?v=N12L5D78MAA).

Since we've been applying these two patterns in several projects, it made sense for us to extract some functionality into [a reusable package](*https://github.com/spatie/laravel-model-states). As of writing though, this package is still a work in progress, but you'll be able to read up on how we use states in depth over there.

I do want to give a quick demonstration still, of how states can be leveraged:

```php
$invoice->status-><hljs prop>transitionTo</hljs>(<hljs type>Paid</hljs>::class);

$invoice->status-><hljs prop>transitionTo</hljs>(<hljs type>Canceled</hljs>::class, $reason);
```

Behind the scenes we ensure that the current state can be transitioned to the next one, and also handle side effects like writing a reason for cancelling an invoice to the database.

Also note the power of the state pattern itself, where state-specific functionality can be added on models, without having to rely on complex conditional logic:

```php
if($invoice->status-><hljs prop>mustBePaid</hljs>()) {
    // …
}
```

Be sure to check out our state package to learn more about how you'd use these patterns in practice.

### Actions

While states and transitions can be very powerful, we must make sure we don't abuse or overuse them. State transitions are allowed to handle small side effects like updating a database field or writing a log entry, but they should not handle anything heavily business related.

For modeling the actual business logic, we use a concept called "actions". An action represents a complex business process, for example `CreateInvoiceAction`. An action can be composed out of several small actions if you want to, to ensure that an action itself stays small and properly testable.

Actions encapsulate the core of the business. They often work with models and data, they can be executed right now or asynchronously. Most importantly: they are thoroughly tested. Actions are where all the important stuff happens.

An action itself is nothing more than a simple class. It can be an invokable class, or have an `execute` or `handle` method. In our projects we prefer the `execute` methods, for reasons that are beyond the scope of this article. You're free to use what's best for your projects. 

Here's what an action might look like:

```php
class CreateInvoiceAction
{
    private $createPaymentAction;

    public function __construct(
        <hljs type>CreatePaymentAction</hljs> $createPaymentAction
    ) {
        $this->createPaymentAction = $createPaymentAction;
    }

    public function execute(
        <hljs type>Invoicable</hljs> $invoicable, 
        <hljs type>User</hljs> $user
    ): Invoice {
        $invoice = <hljs type>Invoice</hljs>::<hljs prop>createForUserAndInvoicable</hljs>(
            $user, 
            $invoicable
        );
        
        $payment = $this->createPaymentAction-><hljs prop>execute</hljs>($invoice);
        
        $user-><hljs prop>notify</hljs>(new <hljs type>InvoiceCreatedNotification</hljs>($invoice));

        return $invoice-><hljs prop>refresh</hljs>();
    }
}
```

Keep in mind that this is an oversimplified example. For example: in real life you're have several `Invoiceable` items which would be added to the invoice.

But for the sake of the example, let's keep it at this. If you want to dive deeper into the subject of actions, you can read up on them [here](*http://stitcher.io/blog/laravel-queueable-actions). 

People coming from the DDD scene might recognise this pattern as "commands" and "handlers" combined. Once again, we choose to simplify two powerful concepts, because it is flexible enough for our projects.  

### DataTransferObjects

Let's backtrace a minute to what we started with: data. While eloquent models represent data from a database, we often have to deal with more than models alone. Think about validated request data and data read from external sources like JSON files or excel files.

One of the keys in domain oriented projects is that we try our absolute best to always know exactly what data we're dealing with. This is where data transfer objects — (DTOs in short — come into play.

 (DTOs are simple objects representing data in a strongly typed manner. Here's an example:
 
 ```php
use <hljs type>Spatie\DataTransferObject\DataTransferObject</hljs>;

class InvoiceLineData extends DataTransferObject
{
    /** @var \Domain\Invoices\Invoicable */
    public $invoicable;

    /** @var int */
    public $price_per_item;

    /** @var int */
    public $amount_of_items;

    /** @var bool */
    public $include_vat;
}
```

We try our best to transform whatever unstructured data we're dealing with, as fast as possible to DTOs. There's a lot to tell about DTOs, which why you can read up on them in a [dedicated post](*http://stitcher.io/blog/structuring-unstructured-data).

In short: working with predictable and strongly typed data offers a lot of benefits. PHP doesn't offer a native `struct` type, so we made [a package](*https://github.com/spatie/data-transfer-object) that will take care of type safety for us.

In general when dealing with data, wherever it comes from, we'll be working with a DTO instead of arrays or objects.
While this pattern requires you to write more code up front, it makes using the data in, for example, actions a lot more easy.

### Rules

While DTOs represent data in a structured way, their goal is not to validate the correctness of the data within your business's context. This is what plain old Laravel rules can be used for.

Most of the time we think about validation rules within the context of a request, though Laravel perfectly allows you to validate any array of data, outside the request.

That's why business specific validation rules are also kept into their domain groups. The goal of these rules is to take data from an "unknown" source, and make sure we're able to use that data correctly within our domain.

### Events and Listeners

If you have experience with DDD or an event-driven system, you might feel uncomfortable with the idea of crossing boundaries between domain groups. After all, you're tightly coupling groups together, making maintenance harder.

Event-driven systems solve this by adding a layer of indirectness, and thus flexibility. However, they also greatly increase the complexity of your code. 

For the scope of our projects, we realised that full blown event-driven systems would be overkill. The benefits they offered wouldn't outweigh the extra time spent on maintaining the system.

Still I find it important to mention that many of the rules and patterns we've covered can easily be applied to an event-driven system.

For now this is all I can say on the topic, but I might revise it at a later point. If you have any thoughts on this specifically, feel free to [send me an email](mailto:brent@stitcher.io).

### Exceptions

Lastly I should mention domain-specific exceptions. They provide detailed feedback about what exactly went wrong, something that's very beneficial when testing and debugging large applications.

Don't underestimate the value of proper exceptions and exception messages. It's best to spend some time on providing valuable feedback when something goes wrong, your future self will be thankful.

Technically there's little to tell about making exceptions, I do assume that you're familiar with how to extend or implement existing throwables in PHP; the hard part is making exceptions which are actually helpful to you during development and in production. This again depends on the project, just make sure you spend enough time on this topic when coding.

### Tests

Finally we've come to one of the main benefits the domain code offers: since it's all very loosely coupled and doesn't depend on any application code, all elements of the domain are easy to test on their own, with isolated unit tests.

When you're sure all of the business logic works correct, you only need a few integration tests to ensure their valid use in applications.

In the end this means you're able to write more and better tests in a shorter amount of time, resulting in a more robust codebase that's less likely to break or have bugs. 

## Entering the application layer

Finally we've arrived at the application layer. Note that it's entirely possible for one developer on the team to only work in the domain layer, while others use that code in their applications. Since the domain layer is fully unit tested, there's no need for domain developers to venture outside that domain.

Application developers, on the other hand, do have to use the domain code. In case of HTTP or API applications, everything starts with the controller.

You should try to keep your controllers as slim as possible, their responsibility is to receive a request, pass the data from it to the domain layer, and parse the result back into a response.

This is what a typical controller might look like:

```php
class CreateInvoiceController
{
    public function __invoke(
        <hljs type>CreateInvoiceRequest</hljs> $request,
        <hljs type>CreateInvoiceAction</hljs> $createInvoiceAction
    ) {
        $invoiceData = <hljs type>InvoiceData</hljs>::<hljs prop>fromRequest</hljs>($request);
        
        $invoice = $createInvoiceAction
            -><hljs prop>execute</hljs>($invoiceData);
        
        return new <hljs type>InvoiceViewModel</hljs>($invoice);
    }
}
```

You might have noticed a few new concepts popping up. Let's discuss them one by one.

### Transforming request data to DTOs

The first step is to take a validated request — I assume you know how these work in Laravel — and transform the data to one or more DTOs.

Did you notice the `::fromRequest()` method on our `InvoiceData` DTO? Its implementation would look something like this:

```php
class InvoiceData extends DataTransferObject
{
    /** @var InvoiceeData */
    public $invoicee;
    
    /** @var \Carbon\Carbon */
    public $invoice_date;
    
    /** @var InvoiceLineData[] */
    public $invoice_lines;
    
    public static function fromRequest(<hljs type>InvoiceRequest</hljs> $request): self
    {
        return new self([
            'invoicee' => <hljs type>InvoiceeData</hljs>::<hljs prop>make</hljs>($request-><hljs prop>get</hljs>('invoicee')),
            'invoice_date' => <hljs type>Carbon</hljs>::<hljs prop>make</hljs>($request-><hljs prop>get</hljs>('invoice_date')),
            'invoice_lines' => <hljs prop>array_map</hljs>(function (<hljs type>array</hljs> $data) {
                return <hljs type>InvoiceLineData</hljs>::<hljs prop>make</hljs>($data);
            }, $request-><hljs prop>get</hljs>('invoice_lines')),
        ]);
    }
}
```

This static constructor takes the validated request and will transform its data to a DTO. 

But hang on a second: by adding this static constructor, we're tightly coupling our DTO, which lives in the domain, to the request, which is specific to our application.

I personally like the static constructor on the DTO, because it allows you to map the data in the same place as where the available properties are defined. Unfortunately we're limited by PHP itself in this area, because we need to pass the properties for this DTO via an array. This means there's no autocompletion possible, and hence it's useful to keep all construction logic in the same class.

If PHP were to ever add named parameter support, or proper structs, it would be a whole other story. Though for now I prefer this pragmatic solution. If you want to keep things neatly ordered though, there are two alternatives:

- Allow the request to transform itself to a DTO, eg. `$invoiceRequest->toDto()`
- Provide a factory in the application layer which can transform this request to an `InvoiceData` DTO

The second option is "the most correct", though also requires you to write more code.

### Executing actions

Once we've got our data in place, it's time to pass it to the domain layer so that it can do its thing. Did you notice that the `CreateInvoiceAction` was injected into the controller? By doing this, we're able to make full use of Laravel's dependency container, and also have constructor injection within our action.

However, from the point of view of the application developer, actions should be black boxes. You shouldn't need to worry about how exactly they do what they do, and only need to know which input they require, and which output they provide.

### Returning results

Once we get the output of our action, we can return it in the form of a response to the client. In our example above I made use of so called "view models", to simplify this even more.

In short: view models are classes which are responsible for representing the data from a controller in such a way that the client can use it. 

In a classic HTTP application, this response would be a rendered ((HTML)) view, hence the name "view model"; though it's perfectly fine to also have a JSON representation for ((AJAX)) requests.

Once again, view models are a topic worth their own post; so be sure to go read about them [here](/blog/laravel-view-models).

---

Having extracted all business related logic from the application layer, we're able to focus on what's most important in it: interfaces, UX, performance.
