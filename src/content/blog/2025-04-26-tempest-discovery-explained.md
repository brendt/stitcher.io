_This post was originally published on the [Tempest blog](https://tempestphp.com/blog/discovery-explained)._

At the very core of Tempest lies a concept called "discovery". It's _the_ feature that sets Tempest apart from any other framework. While frameworks like Symfony and Laravel have limited discovery capabilities for convenience, Tempest starts from discovery, and makes into what powers everything else. In this blog post, I'll explain how discovery works, why it's so powerful, and how you can easily build your own.

## How discovery works

The idea of discovery is simple: make the framework understand your code, so that you don't have to worry about configuration or bootstrapping. When we say that Tempest is "the framework that gets out of your way", it's mainly thanks to discovery.

Let's start with an example: a controller action, it looks like this:

```php
use Tempest\Router\Get;
use Tempest\View\View;

final class BookController
{
    #[Get('/books')]
    public function index(): View
    { /* … */ }
}
```

You can place this file anywhere in your project, Tempest will recognise it as a controller action, and register the route into the router. Now, that in itself isn't all that impressive: Symfony, for example, does something similar as well. But let's take a look at some more examples.

Event handlers are marked with  the `#[EventHandler]` attribute, the concrete event they handle is determined by the argument type:

```php
use Tempest\EventBus\EventHandler;

final class BooksEventHandlers
{
    #[EventHandler]
    public function onBookCreated(BookCreated $event): void
    {
        // …
    }
}
```

Console commands are discovered based on the `#[ConsoleCommand]` attribute. The console's definition will be generated based on the method definition:

```php
use Tempest\Console\ConsoleCommand;

final readonly class BooksCommand
{
    #[ConsoleCommand]
    public function list(): void
    {
        // ./tempest books:list
    }

    #[ConsoleCommand]
    public function info(string $name): void
    {
        // ./tempest books:info "Timeline Taxi"
    }
}
```

View components are discovered based on their file name:

```html
<!-- x-button.view.php -->

<a :if="isset($href)" class="button" :href="$href">
    <x-slot/>
</a>

<div :else class="button">
    <x-slot/>
</div>
```

And there are quite a lot more examples. Now, what makes Tempest's discovery different from eg. Symfony or Laravel finding files automatically? Two things:

1. Tempest's discovery works everywhere, literally _everywhere_. There are no specific folders to configure that need scanning, Tempest will scan your whole project, including vendor files — we'll come back to this in a minute.
2. Discovery is made to be extensible. Does your project or package need something new to discover? It's one class and you're done.

These two characteristics make Tempest's discovery really powerful and flexible. It's what allows you to create any project structure you'd like without being told by the framework what it should look like, something many people have said they love about Tempest.

So, how does discovery work? There's are essentially three steps to it:

1. First, Tempest will look at the installed composer dependencies: any project namespace will be included in discovery, and on top of that all packages that require Tempest will be as well.
2. With all the discovery locations determined, Tempest will first scan for classes implementing the `Discovery` interface. That's right: discovery classes themselves are discovered as well.
3. Finally, with all discovery classes found, Tempest will loop through them, and pass each of them all locations to scan. Each discovery class has access to the container, and register whatever it needs to register in it.

As a concrete example, let's take a look at how routes are discovered. Here's the full implementation of `RouteDiscovery`, with some comments added to explain what's going on.

```php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class RouteDiscovery implements Discovery
{
    use IsDiscovery;

    // Route discovery requires two dependencies,
    // they are both injected via autowiring
    public function __construct(
        private readonly RouteConfigurator $configurator,
        private readonly RouteConfig $routeConfig,
    ) {
    }

    // The `discover` method is called
    // for every possible class that can be discovered
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        // In case of route registration,
        // we're searching for methods that have a `Route` attribute
        foreach ($class->getPublicMethods() as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $routeAttribute) {
                // Each method with a `Route` attribute
                // is stored internally, and will be applied in a second
                $this->discoveryItems->add($location, [$method, $routeAttribute]);
            }
        }
    }

    // The `apply` method is used to register the routes in `RouteConfig`
    // The `discover` and `apply` methods are separate because of caching,
    // we'll talk about it more later in this post
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $routeAttribute]) {
            $route = DiscoveredRoute::fromRoute($routeAttribute, $method);
            $this->configurator->addRoute($route);
        }

        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }
}
```

As you can see, it's not all too complicated. In fact, route discovery is already a bit more complicated because of some route optimizations that need to happen. Here's another example of a very simple discovery implementation, specific to this documentation website (so, a custom one). It's used to discover all classes that implement the `Projector` interface:

```php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ProjectionDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly StoredEventConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Projector::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->projectors[] = $className;
        }
    }
}
```

Pretty simple — right? Even though simple, discovery is really powerful, and sets Tempest apart from any other framework.

## Caching and performance

"Now, hang on. This _cannot_ be performant" — is the first thing I thought when Aidan suggested that Tempest's discovery should scan _all_ project and vendor files. Aidan, by the way, is one of the two other core contributors for Tempest.

Aidan said: "don't worry about it, it'll work". And yes, it does. Although there are a couple of considerations to make.

First, in production, all of this "code scanning" doesn't happen. That's why the `discover()` and `apply()` methods are separated: the `discover()` method will determine whether something should be discovered and prepare it, and the `apply()` method will take that prepared data and store it in the right places. In other words: anything that happens in the `discover()` method will be cached.

Still, that leaves local development though, where you can't cache files because you're constantly working on it. Imagine how annoying it would be if, anytime you added a new controller action, you'd have to clear the discovery cache. Well, true: you cannot cache _project_ files, but you _can_ cache all vendor files: they only update when running `composer up`. This is what's called "partial discovery cache": a caching mode where only vendor discovery is cached and project discovery isn't. Toggling between these modes is done with an environment variable:

```env
{:hl-comment:# .env:}

{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:false:}
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:true:}
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:partial:}
```

Now if you're running full or partial discovery cache, there is one more step to take: after deployment or after updating composer dependencies, you'll have to regenerate the discovery cache:

```console
~ ./tempest discovery:generate

  │ <em>Clearing discovery cache</em>
  │ ✔ Done in 132ms.

  │ <em>Generating discovery cache using the all strategy</em>
  │ ✔ Done in 411ms.
```

For local development, the [`tempest/app`](https://github.com/tempestphp/tempest-app) scaffold project already has the composer hook configured for you, and you can easily add it yourself if you made a project without `tempest/app`:

```json
{
  "scripts": {
    "post-package-update": [
      "@php ./tempest discovery:generate"
    ]
  }
}
```

Oh, one more thing: we did benchmark non-cached discovery performance with thousands of generated files to simulate a real-life project, you can check the source code for those benchmarks [here](https://github.com/tempestphp/tempest-benchmark). The performance impact of discovery on local development was negligible.

That being said, there are improvements we could make to make discovery even more performant. We could, for example, only do real-time discovery on files with actual changes based on the project's git status. These are changes that might be needed in the future, but we won't make any premature optimizations before we've properly tested our current implementation. So if you're playing around with Tempest and running into any performance issues related to discovery, definitely [open an issue](https://github.com/tempestphp/tempest-framework/issues) — that would be very much appreciated!

So, that concludes this dive into discovery. I like to think of it as Tempest's heartbeat. Thanks to discovery, we can ditch most configuration because discovery looks at the code itself and makes decisions based on what's written. It also allows you to structure your project structure any way you want; Tempest won't push you into "controllers go here, models go there".

Do whatever you want, Tempest will figure it out. Why? Because it's **the framework that truly gets out of your way**.
