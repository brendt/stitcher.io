


>> Now that we can work with data in a type-safe and transparent way, we need to start doing something with it.

Just like we don't want to work with random arrays full of data, we also don't want the most critical part of our project, the business functionality, to be spread throughout random functions and classes.

Here's an example: one of the user stories in your project might be for "an admin to create an invoice". This means saving an invoice in the database, but also a lot more:

- First: calculate the price of each individual invoice line and the total price
- Save the invoice to the database
- Create a payment via the payment provider 
- Create a PDF with all relevant information
- Send this PDF to the customer

A common practice in Laravel is to create "fat models" which will handle all this functionality.
In this chapter we will look at another approach to adding this behaviour into our codebase.

Instead of mixing functionality in models or controllers, we will treat these user stories as first class citizens of the project. I tend to call these "actions". 



## Terminology

Before looking at their use, we need to discuss how actions are structured. For starters, they live in the domain. 

Second, they are simple classes without any abstractions or interfaces. An action is a class that takes input, does something, and gives output. That's why an action usually only has one public method, and sometimes a constructor.

As a convention in our projects, we decided to suffix all of our classes. For sure `CreateInvoice` sounds nice, but as soon as you're dealing with several hundreds or thousands of classes, you'll want to make sure that no naming collisions can occur. You see, `CreateInvoice`, could very well also be the name of an invokable controller, of a command, of a job or of a request. We prefer to eliminate as much confusion as possible, hence, `CreateInvoiceAction` will be the name.

Evidently this means that class names become longer. The reality is that if you're working on larger projects, you can't avoid choosing longer names to make sure no confusion is possible. Here's an extreme example from one of our projects, I'm not kidding: `CreateOrUpdateHabitantContractUnitPackageAction`.

We hated this name at first. We desperately tried to come up with a shorter one. In the end though, we had to admit that clarity of what a class is about is the most important. Our IDE's autocompletion will take care of the inconvenience of the long names anyways.

When we're settled on a class name, the next hurdle to overcome is naming the public method to use our action. One option is to make it invokable, like so:

```php
class CreateInvoiceAction
{
    public function __invoke(<hljs type>InvoiceData</hljs> $invoiceData): Invoice
    {
        // …
    }
}
```

There's a practical problem with this approach though. Later in this chapter we'll talk about composing actions out of other actions, and how it's a powerful pattern. It would look something like this:

```php
class CreateInvoiceAction
{
    private $createInvoiceLineAction;

    public function __construct(
        <hljs type>CreateInvoiceLineAction</hljs> $createInvoiceLineAction
    ) { /* … */ }

    public function __invoke(<hljs type>InvoiceData</hljs> $invoiceData): Invoice
    {
        foreach ($invoiceData->lines as $lineData) {
            $invoice-><hljs prop>addLine</hljs>(
                ($this->createInvoiceLineAction)($lineData)
            );
        }
    }
}
```

Can you spot the issue? PHP does not allow to directly invoke an invokable when it's a class property, since PHP is looking for a class method instead. That's why you'll have to wrap the action in parentheses before calling it. 

While this is only a minor inconvenience, there's an additional problem with PhpStorm: it is not able to provide parameter autocompletion when calling the action this way.
Personally, I believe that proper IDE use is an integral part of the development of a project, and shouldn't be ignored. That's why at this time, our team decided not to make actions invokable.

Another option is to use `handle`, which is often used by Laravel as the default name in these kinds of cases. Once again there's a problem with it, specifically because Laravel uses it. 

Whenever Laravel allows you to use `handle`, in eg. jobs or commands, it will also provide method injection from the dependency container. In our actions we only want the constructor to have DI capabilities. Again we'll look closely into the reasons behind this later in this chapter.

So `handle` is also out. When we started using actions, we actually gave this naming conundrum quite a lot of thought. In the end we settled on `execute`. 
Keep in mind though that you're free to come up with your own naming conventions: the point here is more about the pattern of using actions than it is about their names.   

## Into practice

With all of the terminology out of the way, let's talk about why actions are useful, and how to actually use them.

First let's talk about re-usability. The trick when using actions is to split them in small enough pieces so that some things are reusable, while keeping them large enough to not end up with an overload of them. Take our invoice example: generating a PDF from an invoice is something that is likely to happen from within several contexts in our application. Sure there's the PDF that's generated when an invoice is actually created, but an admin might also want to see a preview or draft of it, before sending it. 

These two user stories: "creating an invoice" and "previewing an invoice" obviously require two entry points, two controllers. On the other hand though, generating the PDF based on the invoice is something that's done in both cases.

When you start spending time thinking about what the application actually will do, you'll notice that there are lots of actions that can be reused. Of course, we also need to be careful not to over-abstract our code. It's often better to copy-paste a little code than to make premature abstractions.

A good rule of thumb is to think about the functionality when making abstractions, instead of the technical properties of code. When two actions might do similar things, though they do it in completely different contexts, you should be careful not to start abstracting them too early.

On the other hand, there are cases where abstractions can be helpful. Take again our invoice PDF example: chances are you need to generate more PDFs than just for invoices — at least this is the case in our projects. It might make sense to have a general `GeneratePdfAction`, which can work with an interface, one that `Invoice` then implements.

But, let's be honest, chances are the majority of our actions will be rather specific to their user stories, and not be re-usable. You might think that actions, in these cases, are unnecessary overhead. Hang on though, because re-usability is not the only reason to use them. Actually, the most important reason has nothing to do with technical benefits at all: actions allow the programmer to think in ways that are closer to the real world, instead of the code.

Say you need to make changes to the way invoices are created. A typical Laravel application will probably have this invoice creation logic spread across a controller and a model, maybe a job which generates the PDF, and finally an event listener to send the invoice mail. That's a lot of places you need to know of. Once again our code is spread across the codebase, grouped by its technical properties, rather than its meaning.

Actions reduce the cognitive load that's introduced by such a system. If you need to work on how invoices are created, you can simply go to the action class, and start from there. 

Don't be mistaken: actions may very well work together with eg. asynchronous jobs and event listeners; though these jobs and listeners merely provide the infrastructure for actions to work, and not the business logic itself. This is a good example of why we need to split the domain and application layers: each has their own purpose.

So we got re-usability and a reduction of cognitive load, but there's even more! 

Because actions are small pieces of software that live almost on their own, it's very easy to unit test them. In your tests you don't have to worry about sending fake HTTP requests, setting up facade fakes, etc. You can simply make a new action, maybe provide some mock dependencies, pass it the required input data and make assertions on its output.

For example, the `CreateInvoiceLineAction`: it will take data about which article it will invoice, as well as an amount and a period; and it will calculate the total price and prices with and without VAT. These are things you can write robust, yet simple, unit tests for.

If all your actions are properly unit tested, you can be very confident that the bulk of the functionality that needs to be provided by the application actually works as intended. Now it's only a matter of using these actions in ways that make sense for the end user, and write some integration tests for those pieces.

## Composing actions

One important characteristic of actions that I already mentioned before briefly, is how they use dependency injection. Since we're using the constructor to pass in data from the container, and the `execute` method to pass in context-related data; we're free to compose actions out of actions out of actions out of…

You get the idea. Let's be clear though that a deep dependency chain is something you want to avoid — it makes the code complex and highly dependant on each other — yet there are several cases where having DI is very beneficial.

Take again the example of the `CreateInvoiceLineAction` which has to calculate VAT prices. Now depending on the context, an invoice line might have a price including or excluding VAT. Calculating VAT prices is something trivial, yet we don't want our `CreateInvoiceLineAction` to be concerned with the details of it. 

So imagine we have a simple `VatCalculator` class — which is something that might live in the `\Support` namespace — it could be injected like so:

```php
class CreateInvoiceLineAction
{
    private $vatCalculator;

    public function __construct(<hljs type>VatCalculator</hljs> $vatCalculator)
    { 
        $this->vatCalculator = $vatCalculator;
    }
    
    public function execute(
        <hljs type>InvoiceLineData</hljs> $invoiceLineData
    ): InvoiceLine {
        // …
    }
}
```

And you'd use it like so:

```php
public function execute(
    <hljs type>InvoiceLineData</hljs> $invoiceLineData
): InvoiceLine {
    $item = $invoiceLineData->item;

    if ($item-><hljs prop>vatIncluded</hljs>()) {
        [$priceIncVat, $priceExclVat] = 
            $this->vatCalculator-><hljs prop>vatIncluded</hljs>(
                $item-><hljs prop>getPrice</hljs>(),
                $item-><hljs prop>getVatPercentage</hljs>()
            );
    } else {
        [$priceIncVat, $priceExclVat] = 
            $this->vatCalculator-><hljs prop>vatExcluded</hljs>(
                $item-><hljs prop>getPrice</hljs>(),
                $item-><hljs prop>getVatPercentage</hljs>()
            );
    }

    $amount = $invoiceLineData->item_amount;
    
    $invoiceLine = new <hljs type>InvoiceLine</hljs>([
        'item_price' => $item-><hljs prop>getPrice</hljs>(),
        'total_price' => $amount * $priceIncVat,
        'total_price_excluding_vat' => $amount * $priceExclVat,
    ]);
}
```

The `CreateInvoiceLineAction` in turn would be injected into `CreateInvoiceAction`. And this one again has other dependencies, the `CreatePdfAction` and `SendMailAction`, for example.

You can see how composition can help you keep individual actions small, yet allow for complex business functionality to be coded in a clear and maintainable way.

## Alternatives to actions

There are two paradigms I need to mention at this point, two ways you wouldn't need a concept like actions.

The first one will be known to people who are familiar with DDD: commands and handlers. Actions are a simplified version of them. Where commands and handlers make a distinction between what needs to happen and how it needs to happen, actions combine these two responsibilities into one. It's true that the command bus offers more flexibility than actions. On the other hand it also requires you to write more code. 

For the scope of our projects, splitting actions into commands and handlers was taking it a step too far. We would almost never need the added flexibility, yet it would take a lot longer to write the code.

The second alternative worth mentioning is event driven systems. If you ever worked in an event driven system, you might think that actions are too directly coupled to the places where they are actually used. Again the same argument applies: event driven systems offer more flexibility, yet for our projects it would have been overkill to use them. Furthermore event driven systems add a layer of indirectness that makes the code more complex to reason about. While this indirectness does offer benefits, they wouldn't outweigh the cost of maintenance for us. 




---

I hope it's clear that I'm not suggesting we've got it all figured out and have the perfect solution for all Laravel projects. We don't. When you continue to read through this series, it's important that you keep an eye on the specific needs of your project. While you might be able to use some concepts proposed here, you might also need some other solutions to solve certain aspects.

For us, actions are the right choice because they offer the right amount of flexibility, re-usability and significantly reduce cognitive load. They encapsulate the essence of the application. They can, in fact, be thought of, together with DTOs and models, as the true core of the project.

That brings us to the next chapter, the last piece of the core: models. 
