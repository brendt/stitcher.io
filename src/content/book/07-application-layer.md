<div class="author footnotes">
<p>I'm working on a brand new version of this series, it will contain a video course, ebook and a project built from the ground up with these principles!
</p>
<br>

<a class="cta cta-small cta-bold" target="_blank" rel="noopener noreferrer" href="https://laravel-beyond-crud.com/">Read more</a>
</div>

Back in chapter 1, I stated that one of the characteristics of domain oriented Laravel projects is the following:

> […] most important is that you start thinking in groups of related business concepts, rather than in groups of code with the same technical properties.

In other words: group your code based on what it resembles in the real world, instead of its purpose in the code base.

I also wrote that domain- and application code are two separate things. Moreover, applications are allowed to use 
several groups of the domain at once if they need to in exposing the domain functionality to the end user.

But what exactly belongs in this application layer? How do we group code over there? These questions will be answered in this chapter. 

We're entering the application layer.



## Several applications

The first important thing to understand is that one project can have several applications. In fact, every Laravel project already has two by default: the HTTP- and console apps. Still, there are several more parts of your project that can be thought of as a standalone app: every third party integration, a REST API, a front-facing client portal, an admin back office, and what-not.

All of these can be thought of as separate applications, exposing and presenting the domain for their own unique use cases. In fact, I tend to think of the artisan console as just another one in this list: it's an application used by developers to work with and manipulate the project.

Since we're in web development, our main focus will probably be within HTTP-related apps though. So what's included in them? Let's have a look:

- Controllers
- Requests
- Application-specific validation rules
- Middleware
- Resources
- ViewModels
- QueryBuilders — the ones that [parse URL queries](*https://github.com/spatie/laravel-query-builder)

I would even argue that blade views, JavaScript- and CSS files belong within an application, and shouldn't be put aside in a `resources` folder. I realise this is a step too far for many people, but I wanted to mention it, at least.

Remember, an application's goal is to get the user's input, pass it to the domain and represent the output in a usable way for the user. After several chapters deep in the domain code, it shouldn't be a surprise that most of the application code is merely structural, often boring code; passing data from one point to another.

Nevertheless there's lots to tell about several of the concepts mentioned above: ViewModels, third party integrations, what about jobs?; we will tackle these subjects in future chapters, but for now we want to focus on the main ideas behind the application layer, and a general overview of them.

## Structuring HTTP applications

There's one very important point we need to discuss before moving on: how will an HTTP application generally be structured? Should we follow Laravel's conventions, or do we need to give it some more thought? 

Since I'm dedicating a section of a chapter to this question, you can probably guess the answer. So let's look at what Laravel would recommend you doing by default.

```php
App/Admin
├── Http
│   ├── Controllers
│   ├── Kernel.php
│   └── Middleware
├── Requests
├── Resources
├── Rules
└── ViewModels
```  

This structure is fine in small projects, but honestly it doesn't scale well. To clarify what I mean by this, I'll show you the document structure of an admin application in one of our client projects. Obviously I can't reveal too much information about this project, so I blacked out most of the class names.
Since we've been using invoicing as the example throughout this series though, I highlighted some invoice related classes within the admin application. Have a look.
 
Oh and, happy scrolling! 

```
App/Admin
├── <hljs purple>Controllers</hljs>
│   ├── <hljs textgrey>█████████</hljs>
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████</hljs>
│   │   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████</hljs>
│   │   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>███</hljs>
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>
│   │   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   │   ├── <hljs blue>Invoices</hljs>
│   │   │   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   │   ├── <hljs blue>IgnoreMissedInvoicesController</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   │   ├── <hljs blue>InvoiceStatusController</hljs>.php
│   │   │   ├── <hljs blue>InvoicesController</hljs>.php
│   │   │   ├── <hljs blue>MissedInvoicesController</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   │   └── <hljs blue>RefreshMissedInvoicesController</hljs>.php
│   │   ├── <hljs textgrey>████████</hljs>
│   │   │   └── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   └── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████</hljs>
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   └── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████</hljs>
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   └── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████</hljs>
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   └── <hljs textgrey>███████████████</hljs>.php
├── <hljs darkblue>Filters</hljs>
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs blue>InvoiceMonthFilter</hljs>.php
│   ├── <hljs blue>InvoiceOfferFilter</hljs>.php
│   ├── <hljs blue>InvoiceStatusFilter</hljs>.php
│   ├── <hljs blue>InvoiceYearFilter</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   └── <hljs textgrey>███████████████████</hljs>.php
├── <hljs grey>Middleware</hljs>
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs blue>EnsureValidHabitantInvoiceCollectionSettingsMiddleware</hljs>.php
│   ├── <hljs blue>EnsureValidInvoiceDraftSettingsMiddleware</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs blue>EnsureValidOwnerInvoiceCollectionSettingsMiddleware</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   └── <hljs textgrey>█████████████████</hljs>.php
├── <hljs cyan>Queries</hljs>
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs blue>InvoiceCollectionIndexQuery</hljs>.php
│   ├── <hljs blue>InvoiceIndexQuery</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   └── <hljs textgrey>███████████████</hljs>.php
├── <hljs yellow>Requests</hljs>
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs blue>InvoiceRequest</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   └── <hljs textgrey>████████████████████████</hljs>.php
├── <hljs green>Resources</hljs>
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs blue>Invoices</hljs>
│   │   ├── <hljs blue>InvoiceCollectionDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceCollectionResource</hljs>.php
│   │   ├── <hljs blue>InvoiceDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceDraftResource</hljs>.php
│   │   ├── <hljs blue>InvoiceLineDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceLineResource</hljs>.php
│   │   ├── <hljs blue>InvoiceResource</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs blue>InvoiceIndexResource</hljs>.php
│   ├── <hljs blue>InvoiceLabelResource</hljs>.php
│   ├── <hljs blue>InvoiceMainOverviewResource</hljs>.php
│   ├── <hljs blue>InvoiceeResource</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   └── <hljs textgrey>█████████████████████</hljs>.php
└── <hljs red>ViewModels</hljs>
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>
    │   ├── <hljs textgrey>████████████████</hljs>.php
    │   ├── <hljs textgrey>█████████████████</hljs>.php
    │   ├── <hljs textgrey>██████████████████</hljs>.php
    │   └── <hljs textgrey>███████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs blue>InvoiceCollectionHabitantContractPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceCollectionOwnerContractPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceCollectionPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceDraftViewModel</hljs>.php
    ├── <hljs blue>InvoiceIndexViewModel</hljs>.php
    ├── <hljs blue>InvoiceLabelsViewModel</hljs>.php
    ├── <hljs blue>InvoiceStatusViewModel</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████</hljs>.php
    ├── <hljs textgrey>██████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>██████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████</hljs>.php
    └── <hljs textgrey>█████████████████</hljs>.php
```

Hi again!

That was quite a lot to scroll through. I'm not kidding though - this is what one of our projects actually looked like after a year and a half of development. And keep in mind that this is _only_ the admin application code, it doesn't include anything domain related.

So what is the core problem here? It's actually the same as with our domain code back in chapter 1: we're grouping our code based on technical properties, instead of their real-world meaning; controllers with controllers, requests with requests, view models with view models, and so on.

Once again a concept like invoices is spread across multiple directories, and mixed with dozens of other classes. Even with the best IDE support, it's very difficult to wrap your head around the application as a whole and there's no way to get a general overview of what's happening.

The solution? No surprises here, I hope; it's the same as we did with domains: group together code that belongs together. In this example, invoices:

```
Admin
└── <hljs blue>Invoices</hljs>
    ├── <hljs purple>Controllers</hljs>
    │   ├── IgnoreMissedInvoicesController.php
    │   ├── InvoiceStatusController.php
    │   ├── InvoicesController.php
    │   ├── MissedInvoicesController.php
    │   └── RefreshMissedInvoicesController.php
    ├── <hljs darkblue>Filters</hljs>
    │   ├── InvoiceMonthFilter.php
    │   ├── InvoiceOfferFilter.php
    │   ├── InvoiceStatusFilter.php
    │   └── InvoiceYearFilter.php
    ├── <hljs grey>Middleware</hljs>
    │   ├── EnsureValidHabitantInvoiceCollectionSettingsMiddleware.php
    │   ├── EnsureValidInvoiceDraftSettingsMiddleware.php
    │   └── EnsureValidOwnerInvoiceCollectionSettingsMiddleware.php
    ├── <hljs cyan>Queries</hljs>
    │   ├── InvoiceCollectionIndexQuery.php
    │   └── InvoiceIndexQuery.php
    ├── <hljs yellow>Requests</hljs>
    │   └── InvoiceRequest.php
    ├── <hljs green>Resources</hljs>
    │   ├── InvoiceCollectionDataResource.php
    │   ├── InvoiceCollectionResource.php
    │   ├── InvoiceDataResource.php
    │   ├── InvoiceDraftResource.php
    │   ├── InvoiceIndexResource.php
    │   ├── InvoiceLabelResource.php
    │   ├── InvoiceLineDataResource.php
    │   ├── InvoiceLineResource.php
    │   ├── InvoiceMainOverviewResource.php
    │   ├── InvoiceResource.php
    │   └── InvoiceeResource.php
    └── <hljs red>ViewModels</hljs>
        ├── InvoiceCollectionHabitantContractPreviewViewModel.php
        ├── InvoiceCollectionOwnerContractPreviewViewModel.php
        ├── InvoiceCollectionPreviewViewModel.php
        ├── InvoiceDraftViewModel.php
        ├── InvoiceIndexViewModel.php
        ├── InvoiceLabelsViewModel.php
        └── InvoiceStatusViewModel.php
```

How about that? When you're working on invoices, you've got one place to go to know what code is available to you. I tend to call these groups "application modules", or "modules" for short; and I can tell you from experience that they make life a lot easier when you're working in projects of this scale.

Does this mean modules should be one-to-one mapped on the domain? Definitely not! Mind you, there could be some overlap, but it's not required. For example: we've got a settings module within the admin application, which touches several domain groups at once. It wouldn't make sense to have separate settings controllers, view models etc spread across multiple modules: when we're working on settings, it's one feature on its own; not one spread across several modules just to be in sync with the domain.

Another question that might arise looking at this structure, is what to do with general purpose classes. Stuff like a base request class, middleware that's used everywhere,… Remember the `Support` namespace back in chapter one? That's what it is for! `Support` holds all code that should be globally accessible but it could just as well have been part of the framework.

<div class="author footnotes">
<p>I'm working on a brand new version of this series, it will contain a video course, ebook and a project built from the ground up with these principles!
</p>
<br>

<a class="cta cta-small cta-bold" target="_blank" rel="noopener noreferrer" href="https://laravel-beyond-crud.com/">Read more</a>
</div>

Now that you have a general overview of how we can structure applications, it's time to look at some of the patterns we use over there to make our lives easier. We'll start with that next time, when we talk about view models.
