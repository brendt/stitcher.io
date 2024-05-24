I've added a pretty interesting feature to Tempest: tagged singletons. They are pretty simple to explain: Tempest allows you to attach a tag to a singleton definition, which means you can have multiple singletons of the same class, as long as their tags differ.

Tagged singletons can be registered like so:

```php
$container->singleton(
    Highlighter::class, 
    new Highlighter(new LightTerminalTheme()), 
    tag: 'cli',
);
```

Or by using an initializer:

```php
#[Singleton(tag: 'web')]
final readonly class WebHighlighterInitializer implements Initializer
{
    public function initialize(Container $container): Highlighter
    {
        return new Highlighter(new CssTheme());
    }
}
```

And you can retrieve a specific tagged singleton like so:

```php
$container->get(Highlighter::class, tag: 'cli');
```

Or by using the `{php}#[Tag]` attribute for autowired dependencies:

```php
class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        #[Tag('web')] private Highlighter $highlighter,
    ) {
    }
}
```

Ok but, singletons should be… singletons, right? How can you have multiple singletons for the same class? And what about tight coupling between abstracts and implementations?

Well that's why I'm writing this blog post.

## Let's work through it

In order to understand the usefulness of tagged singletons, I'll have to make sure we're on the same page about a couple of things regarding DI: the dependency injection pattern, the container, autowiring, singletons, and finally the topic of "object identity". 

It's a multi-layered answer, but I hope it'll be an interesting read — I definitely find it an interesting topic. 

### Dependency Injection

Let's start from the beginning: what is dependency injection? Here's the definition from [Wikipedia](https://en.wikipedia.org/wiki/Dependency_injection):

> dependency injection is a programming technique in which an object or function receives other objects or functions that it requires, as opposed to creating them internally. Dependency injection aims to separate the concerns of constructing objects and using them, leading to loosely coupled programs

Note how this definition says nothing about _how_ dependencies are injected. The pattern is about the fact the dependencies are passed from the outside into an object. Following that definition, this is an example of dependency injection:

```php
$object = new HttpExceptionHandler(
    highlighter: new Highlighter(
        theme: new CssTheme(),
    ),
);
```

In this example, `{php}HttpExceptionHandler` has a dependency on `{php}Highlighter`, which has a dependency on `{php}CssTheme`. We've manually constructed all dependencies needed, but it's still dependency injection.

As a side note; I like to think of dependencies as more than only objects, a dependency could  be a scalar value as well — which is extremely useful for injecting config values, but that's a story for another time.

So: dependency injection is about _injecting dependencies_ from the _outside_ into a class, so that that class doesn't have to worry about having to construct and manage those dependencies itself.

### The container

Next is the container, which is what most people think about when they say "dependency injection". The container is no more than a tool to make dependency injection more convenient. A container could be as simple as this:

```php
class Container
{
    private array $instances;
    
    public function register(string $key, Closure $definition): self
    {
        $this->instances[$key] = $definition($this);
        
        return $this;
    }
    
    public function get(string $key): mixed
    {
        return $this->instances[$key] ?? throw new UnknownDependency();
    }
}
```

Setting up the container would look something like this:

```php
$container = (new Container())
    ->register('cssTheme', fn (Container $container) => new CssTheme)
    ->register('highlighter', fn (Container $container) => new Highlighter($container->get('cssTheme')))
    ->register('httpExceptionHandler', fn (Container $container) => new HttpExceptionHandler($container->get('highlighter')));

$container->get('httpExceptionHandler');
```

Now, this isn't how most real-life containers work, for good reasons: it's a rather inconvenient way of managing dependencies. I deliberately wrote it like this though to clarify how a container, in its core, is a key/value store of objects. You can register objects in it, and you can get objects from it.

A slightly more convenient way to register object is if you wouldn't have to specify textual keys, but instead used the class name of the object:


```php
class Container
{
    private array $instances;
    
    public function register(Closure $definition): self
    {
        $instance = $definition($this);
        
        $this->instances[$instance::class] = $instance;
        
        return $this;
    }
    
    public function get(string $key): mixed
    {
        return $this->instances[$key] ?? throw new UnknownDependency();
    }
}

$container = (new Container())
    ->register(fn (Container $container) => new CssTheme)
    ->register(fn (Container $container) => new Highlighter($container->get(CssTheme::class)))
    ->register(fn (Container $container) => new HttpExceptionHandler($container->get(Highlighter::class)));
```

So, that's the container: a key/value store with the goal of making dependency injection more convenient.

### Autowiring

Speaking of convenience, real dependency containers do a lot more than just keeping track of keys and values. Autowiring is probably one of its biggest advantages. Autowiring is a mechanism that allows the container to come up with definitions for dependencies itself, instead of them having to be manually defined. You can still define definitions for specific cases if you need to, but only for the cases where autowiring isn't smart enough. 

Essentially, you'd write this:

```php
$container->get(HttpExceptionHandler::class);
```

And the container would use reflection to find the right dependencies:

```php
class Container
{
    // …
    
    public function get(string $key): mixed
    {
        return $this->instances[$key] ?? $this->autowire($key);
    }
    
    private function autowire(string $class): object
    {
        $constructor = (new ReflectionClass($class))->getConstructor();
        
        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[$parameter->getName()] = $this->get($parameter->getType()->getName());
        }
        
        return new $class(...$dependencies);
    }
}
```

That's autowiring: adding convenience for developers, so that they don't have to specify every definition manually.

### Singletons

### Object identity

- What defines "identity"?
  - We just happen to use class names for dependency IDs because of convenience
  - If we'd always use string keys, we wouldn't even have this discussion
  - But imagine a world where every injected dependency needed manual defined keys?
- Tighter coupling
  - True, which means that tagged singletons are a mechanism that doesn't suit all purposes
- Alternative: provide empty interfaces? 
  - Is that better?
  - We're abusing interfaces
- Alternative: not using singletons and always constructing in real time. 
  - What about dependencies that are expensive to construct?
  - In some cases it's difficult/not possible to provide the context information needed, especially during autowiring
- Apparently, I'm not the first one doing it: https://learn.microsoft.com/en-us/dotnet/core/extensions/dependency-injection#keyed-services