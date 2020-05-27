> Humans think in categories, our code should be a reflection of that.

First things first, I didn't come up with the term "domain" — I got it from the popular programming paradigm DDD, or "domain driven design". According to Oxford Dictionary, a "domain" can be described as *"A specified sphere of activity or knowledge"*.

While my use of the word "domain" won't exactly mean the same as when used within DDD, there are several similarities. If you're familiar with DDD, you'll discover these similarities throughout this book. I tried my best to mention any overlap and differences when relevant.

{{ ad:carbon }}

So, domains. You could also call them "groups", "modules"; some people call them "services". Whichever name you prefer, domains describe a set of the business problems you're trying to solve.

Hang on… I realise I just used my first "enterprisey" term in this book: "the business problem". Making your way through this book, you'll note that I did my best to steer away from the theoretical, upper-management, business side of things. I'm a developer myself and prefer to keep things practical. So another, simpler, name would be "the project". 

Let's give an example: an application to manage hotel bookings. It has to manage customers, bookings, invoices, hotel inventories, etc. 

Modern web frameworks teach you to take one group of related concepts, and split it across multiple places throughout your codebase: controller with controllers, models with models; you get the deal.

Has a client ever told you to "work on all controllers now", or to "spend some time in the models directory"? No — they ask you to work on invoicing, customer management or bookings features.

These groups are what I call domains. They aim to group together concepts within your project that belong together. While this might seem trivial at first, it's more complicated than you might think. That's why part of this book will focus on a set of rules and practices to keep your code nicely ordered.

Obviously there's no mathematical formula I can give you, almost everything depends on the specific project you're working on. So don't think of this book as giving a fixed set of rules. Rather think of it as handing you a collection of ideas that you can use and build upon, however you like.

It's a learning opportunity, much more than a solution you can throw at whatever problem you encounter.

## Domains and applications

If we're grouping ideas together, evidently the question arises: how far do we go? You could for example group everything invoice-related together: models, controllers, resources, validation rules, jobs, …

This raises a problem in classic HTTP applications: there often isn't a one-to-one mapping between controllers and models. Granted, in REST APIs and for the majority of your classic CRUD controllers there might be a strict one-to-one mapping, but unfortunately these are the exceptions to the rules that will give us a hard time. Invoices for example are simply not handled in isolation, they need a customer to be sent to, they need bookings to invoice, etc.

That's why we need to make a further distinction between what is domain code, and what is not.

On the one hand there's the domain, representing all the business logic; and on the other hand, we have code that uses — consumes — that domain to integrate it with the framework and exposes it to the end-user. Applications provide the infrastructure for end-users to use and manipulate the domain in a user-friendly way.

## In practice

So what does this look like in practice? The domain will hold classes like models, query builders, domain events, validation rules and more; we will look at all these concepts in-depth.

The application layer will hold one or several applications. Every application can be seen as an isolated app which is allowed to use all of the domain. In general, applications don't talk to each other.

One example could be a standard HTTP admin panel, and another one could be a REST API. I also like to think of the console, Laravel's artisan, as an application of its own.

As a high level overview, here's what the folder structure of a domain-oriented project might look like:

```txt
<hljs comment>One specific domain folder per business concept</hljs>
app/Domain/Invoices/
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

app/Domain/Customers/
    <hljs comment>// …</hljs>
```

And this is what the application layer would look like:

```txt
<hljs comment>The admin HTTP application</hljs>
app/App/Admin/
    ├── Controllers
    ├── Middlewares
    ├── Requests
    ├── Resources
    └── ViewModels

<hljs comment>The REST API application</hljs>
app/App/Api/
    ├── Controllers
    ├── Middlewares
    ├── Requests
    └── Resources

<hljs comment>The console application</hljs>
app/App/Console/
    └── Commands
```

## On the topic of namespaces

You might have noticed that the above example doesn't follow the Laravel convention of `\App` as the single root namespace. Since applications are only part of our project, and because there can be several, it doesn't make sense to use `\App` as the root for everything.

If you do prefer to stay closer to Laravel's default structure, you're allowed to do that. This means you'll end up with namespaces like `\App\Domain` and `\App\Api`. But you're free to do what you're comfortable with.

If you want to separate the root namespaces though, you can do so by making a slight change in `composer.json`:

```json
{
    // …

    "<hljs prop>autoload</hljs>" : {
        "<hljs prop>psr-4</hljs>" : {
            "<hljs prop>App\\</hljs>" : "app/App/",
            "<hljs prop>Domain\\</hljs>" : "app/Domain/",
            "<hljs prop>Support\\</hljs>" : "app/Support/"
        }
    }
}
```

Note that I also have a `\Support` root namespace, which for now you can think of as the dumping ground for all little helpers that don't belong anywhere.

--- 

Whatever folder structure you use, most important is that you start thinking in groups of related business concepts, rather than in groups of code with the same technical properties.

Within each group, each domain, there's room to structure the code in ways that make it easy to use within those individual groups though. The first part of this book will look closely at how domains can be structured internally and which patterns can be used to help you keep your codebase maintainable as it grows over time. After that, we'll look at the application layer, how the domain can be consumed exactly, and how we improve upon existing Laravel concepts by using for example view models.

There's a lot of ground to cover, and I hope you'll be able to learn many things from this that you'll be able to put into practice right away.
