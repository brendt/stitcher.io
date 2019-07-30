If you're using any modern framework, chances are you're heavily relying on dependency injection. But do you know what dependency injection _actually_ is about — or better: what it's _not_? 

## The dependency container

While every modern framework ships with a dependency container — a big box that knows how to construct objects for you — it doesn't guarantee you'll actually be using the dependency injection pattern the way it's supposed to be.

The container _can_ make it much more easy to have dependencies injected into a class, but it can also be abused quite a lot.

{{ ad:carbon }}

## Service location

One way to (ab)use the container is to _pull_ objects from it, instead of having them injected into the current context. This pattern is called "service location", and is the opposite of dependency injection. It looks like this:

```php
class MyController
{
    public function indexAction()
    {
        $service = <hljs prop>app</hljs>(<hljs type>Service</hljs>::class);

        // …        
    }
}
``` 

Service location will ask the container for a specific object. This makes the context you're pulling this service from a difficult point to test, as well as a black box to the outside: you're unable to know what kind of external dependencies `MyController` uses, without looking at all of the code.

Some frameworks promote this use of the container, because it can be simple and fast at the start of a project. In projects with hundreds, maybe even thousands of classes registered in the container, the use of service location can and will become a mess; one that proper use of dependency injection would solve.

I also recommend you to read my post on [why service location is an anti-pattern](*/blog/service-locator-anti-pattern).

## Shared dependencies

Moving on to some more positive vibes: making use of the container in a good way.

When dependency injection is properly used, the outside context — in many cases the container — has control over the concrete dependency that it's injecting into a class. This means that the same object can be injected into several other contexts, without those contexts having to know anything about them being "singletons" or "shared dependencies".

Even though sharing dependencies can be a good and powerful thing to do, it is still _not_ what dependency injection is about, but rather a beneficial side effect.

## Auto wiring

Finally, another useful feature that, again, isn't what dependency injection is about: autowiring.

To give developers more flexibility, some containers allow 
for smart, automatically determined, class definitions. 
This means you don't have to manually describe how every class should be constructed.
These containers will scan your code, and determine which dependencies are needed
by looking at type hints and doc blocks.

A lot of magic happens here, but auto wiring can be a useful tool for rapid application development.

---

If by now, you want a refresher on the basics of what dependency injection is about. You can go read up on it [here](*/blog/dependency-injection-for-beginners).
