I've been thinking about route attributes lately. By doing so, I came to realise that I've got a somewhat strange relation with [annotations a.k.a. attributes](/blog/attributes-in-php-8). Over the years, I've gone from loving them to hating them, to loving them again, to somewhere in between. I've seen them abused, both inside and outside of PHP and I've heard big proponents and opponents making good arguments for and against them.

I've did quite a lot of thinking about a specific use case for them. I've talked to quite a lot of people about it and I've tried to approach the question as rationally as possible: are route attributes a good or bad thing?

After months of thoughts and discussions, I've come to (what I think to be as objectively as possible) a conclusion that they are worth giving a try, albeit with some side notes attached. In this post, I'll share my thought process, as well as address all counterarguments I've heard against route attributes over these past years.

Let's get started.

---

To make sure we're on the same page, route attributes in their most basic form would look something like this:

```php
class PostAdminController
{
    #[<hljs type>Get</hljs>(<hljs text>'/posts'</hljs>)]
    public function index() {}
    
    #[<hljs type>Get</hljs>(<hljs text>'/posts/{post}'</hljs>)]
    public function show(<hljs type>Post</hljs> $post) {}
    
    // …
    
    #[<hljs type>Post</hljs>(<hljs text>'/posts/{post}'</hljs>)]
    public function store(<hljs type>Post</hljs> $post) {}
}
```

There are _a lot_ of issues with such a simplified example, so let's go through them one by one.

### Duplication

First of all, there's the issue of duplication. It might not seem like a problem in this example, but most projects definitely have more than "a few routes". I've counted them in two of the larger projects I'm working on: 470 and 815 routes respectively.

Both [Symfony](https://symfony.com/doc/current/routing.html#route-groups-and-prefixes) and [Laravel](https://laravel.com/docs/8.x/routing#route-groups) have a concept called "route groups" to combat these kinds of scaling issues.

I'm sure you can come up with quite a lot of different solutions to model route groups with attributes. I'm going to share two that I think are robust and qualitative approaches, but please do imagine other possibilities while you're at it.

You could manage "shared route configuration", stuff like prefixes and middlewares, on the controller level: 

```php
#[<hljs type>Prefix</hljs>(<hljs text>'/posts'</hljs>)]
#[<hljs type>Middleware</hljs>(<hljs type>AdminMiddleware</hljs><hljs text>::class</hljs>)]
class PostController
{
    #[<hljs type>Get</hljs>(<hljs text>'/posts'</hljs>)]
    public function index() {}
    
    #[<hljs type>Get</hljs>(<hljs text>'/posts/{post}'</hljs>)]
    public function show(<hljs type>Post</hljs> $post) {}
}
```

Or, taking it a step further; have a generic `<hljs type>Route</hljs>` attribute that can be used both as-is:

```php
<hljs comment>#[<hljs type>Route</hljs>(
    <hljs prop>prefix</hljs>: '/post',
    <hljs prop>middleware</hljs>: <hljs text>[</hljs><hljs type>AdminMiddleware</hljs><hljs text>::class</hljs><hljs text>]</hljs>
)]</hljs>
class PostController
{
    #[<hljs type>Get</hljs>(<hljs text>'/posts'</hljs>)]
    public function index() {}
    
    #[<hljs type>Get</hljs>(<hljs text>'/posts/{post}'</hljs>)]
    public function show(<hljs type>Post</hljs> $post) {}
}
```

But which could also be extended:

```php
#[Attribute]
class AdminRoute extends Route
{
    public function __construct(
        <hljs type>string</hljs> $prefix,
        <hljs type>array</hljs> $middleware,
    ) {
        parent::<hljs prop>__construct</hljs>(
            <hljs prop>prefix</hljs>: "/admin/{$prefix}",
            <hljs prop>middleware</hljs>: [
                <hljs type>AdminMiddleware</hljs>::class,
                ...$middleware
            ],
        )
    }
}
```

And be used like so:

```php
#[<hljs type>AdminRoute</hljs>]
class PostController
{
    #[<hljs type>Get</hljs>(<hljs text>'/posts'</hljs>)]
    public function index() {}
    
    #[<hljs type>Get</hljs>(<hljs text>'/posts/{post}'</hljs>)]
    public function show(<hljs type>Post</hljs> $post) {}
}
```

This last approach is definitely my favourite, but feel free to differ in that opinion. The main point here is: **excessive duplication doesn't have to be a problem with route attributes**.

### Discoverability

The second-biggest argument against route attributes comes from people who say that they prefer to keep their routes in a single file, so that they can easily search them, instead of spreading them across potentially hundreds of controller files.

Let's take a look at a real life example though. Here we have a contacts controller with an `<hljs prop>edit</hljs>` method:

```php
class ContactsController
{
    public function edit(<hljs type>Contact</hljs> $contact) { /* … */ };
}
```

People arguing for "a central place to manage their routes", in other words: _against_ route attributes; say that a central route file makes it easier to find what they are looking for. So, ok, let's click through to our routes file (in my case the Laravel IDEA plugin allows you to click the `<hljs prop>edit</hljs>` method and go straight to the route definition), and take a look at what's there:

```php
<hljs type>Route</hljs>::<hljs prop>get</hljs>('{contact}', [<hljs type>ContactsController</hljs>::class, 'edit']);
```

So, what's the URI to visit this page? Is it `/{contactId}`? Of course not, this route is part of a route group:

```php
<hljs type>Route</hljs>::<hljs prop>prefix</hljs>('people')-><hljs prop>group</hljs>(function (): <hljs type>void</hljs> {
    // …
    <hljs type>Route</hljs>::<hljs prop>get</hljs>('{contact}', [<hljs type>ContactsController</hljs>::class, 'edit']);
});
```

So, it's `/people/{contactId}`? Nope, because this group is part of another group:

```php
<hljs type>Route</hljs>::<hljs prop>prefix</hljs>('crm')
    // …
    -><hljs prop>group</hljs>(function (): <hljs type>void</hljs> {
        <hljs type>Route</hljs>::<hljs prop>prefix</hljs>('people')-><hljs prop>group</hljs>(function (): <hljs type>void</hljs> {
            // …
            <hljs type>Route</hljs>::<hljs prop>get</hljs>('{contact}', [<hljs type>ContactsController</hljs>::class, 'edit']);
        });
    }
```

Which is part of another group:

```php
<hljs type>Route</hljs>::<hljs prop>middleware</hljs>('can:admin,' . <hljs type>Tenant</hljs>::class)
    -><hljs prop>group</hljs>(function (): void {
        <hljs type>Route</hljs>::<hljs prop>prefix</hljs>('crm')
        // …
        -><hljs prop>group</hljs>(function (): <hljs type>void</hljs> {
            <hljs type>Route</hljs>::<hljs prop>prefix</hljs>('people')-><hljs prop>group</hljs>(function (): <hljs type>void</hljs> {
                // …
                <hljs type>Route</hljs>::<hljs prop>get</hljs>('{contact}', [<hljs type>ContactsController</hljs>::class, 'edit']);
            });
        }
```

Which is part of another group, defined in Laravel's route service provider:

```php
<hljs type>Route</hljs>::<hljs prop>middleware</hljs>(['web', 'auth', /* … */])
    -><hljs prop>prefix</hljs>('admin/{currentTenant}')
    -><hljs prop>group</hljs>(<hljs prop>base_path</hljs>('routes/admin_tenant.php'));
```

So, in fact, the full URI to this controller is `/admin/{tenantId}/crm/people/edit/{contactId}`. And now remember our route file actually contains somewhere between 700 and 1500 lines of code, not just the snippets I shared here.

I'd even argue that using dedicated route attributes like `<hljs type>CrmRoute</hljs>` extending `<hljs type>AdminRoute</hljs>` would be _much_ easier to work with, since you can simply start from the controller and click your way one level up each time, without manually looking for group configurations.

Furthermore, _adding_ a route to the right place in such a large route file poses the same issue: where exactly should my route be defined to fall in the right group? I'm not going to step through the same process again in reverse, I'm sure you can see the problem I'm pointing at.

Finally, some people mention splitting your route files into separate ones to partially prevent these problems. And I'd agree with them: that's exactly what route attributes allow you to do on a controller-based level.

In short, **dedicated route files do not improve discoverability and route attributes definitely don't worsen the situation**.

### Consistency

With the two main arguments against route attributes refuted, let's consider whether they have benefits compared to separated route files. 

In fact, they do. 

In the vast majority of cases, in experience and based on other's testimonies, controller methods and URI almost _always_ map one to one together. Then why shouldn't they be kept together?

When I'm writing a new controller method, the last thing I want to be bothered about is to create the controller method, and then think to myself "ok, which route file should I now go to that has the correct middleware groups setup, and where in that file should I register this particular method". There's so much unnecessary cognitive overload introduced because of separate route files, because they pull apart two concepts they tightly belong together. 

Let's just keep them together, so that we can focus on more important stuff.

Furthermore, any framework worth its salt will provide you with the tools necessary to generate any URI based on a controller method:

```php
<hljs prop>action</hljs>([<hljs type>PostController</hljs>::class, 'show'], $post);
```

If you're already working with controller methods as the "entry point" into your project's URI scheme, then why not keep relevant method data right there?

So yes, **route attributes do add value compared to route files, they reduce cognitive load while programming**.

### Performance

Finally a short one, but one that needs mentioning because some people are still afraid of attributes because of performance issues. 

First of all: reflection in PHP is pretty fast, all major frameworks use reflection extensively, and I bet you never noticed those parts being a performance bottleneck.

And, secondly: attribute discovery and route registration is something that very easily cacheable in production: Laravel already does this with event listeners and blade components, just to name two examples.

In fact, the concept of "a route cache" is already present in both Symfony and Laravel, and Symfony even already supports route attributes.

So no, **performance isn't a concern when using route attributes**.

---

So, what's left? The only argument I've heard that I didn't address here is that "people just don't like attributes".

There's very little to say against that. I think it mostly means that "people don't like change" in general. I've been guilty of this attitude myself. My only advice I can give you if you're in that situation is to try it out. Get out of your comfort zone, I find that liberating.

Now, maybe you want to tell me I'm wrong, or share your own 
