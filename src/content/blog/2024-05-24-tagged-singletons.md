I've added a pretty interesting feature to [Tempest](https://tempest.stitcher.io): tagged singletons. Let me give you the tl;dr: Tempest allows you to attach a tag to a singleton definition, which means you can have multiple singletons of the same class, as long as their tags differ.

They can be registered manually like so:

```php
$container->singleton(
    Highlighter::class, 
    new Highlighter(new LightTerminalTheme()), 
    tag: 'cli',
);
```

Or you could use an initializer:

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
    ) {}
}
```

Ok but, singletons should be… singletons, right? How can you have multiple singletons for the same class? And what about tight coupling between abstracts and implementations?

Well that's why I'm writing this blog post.

## Let's work through it

In order to understand the usefulness of tagged singletons, I'll have to make sure we're on the same page about a couple of things regarding dependency injection: the pattern itself, the container, autowiring, singletons, and finally the topic of "object identity". 

It's a multi-layered answer, but I hope it'll be an interesting read — I definitely found it an interesting topic to write about.

### Dependency Injection

Let's start from the beginning: what is dependency injection? Here's the definition from [Wikipedia](https://en.wikipedia.org/wiki/Dependency_injection):

> dependency injection is a programming technique in which an object or function receives other objects or functions that it requires, as opposed to creating them internally. Dependency injection aims to separate the concerns of constructing objects and using them, leading to loosely coupled programs

Note that this definition says nothing about _how_ dependencies are injected. The pattern is about the fact the dependencies are passed from the _outside into_ an object. 

Following that definition, this is an example of dependency injection:

```php
$object = new HttpExceptionHandler(
    highlighter: new Highlighter(
        theme: new CssTheme(),
    ),
);
```

In this example, `{php}HttpExceptionHandler` has a dependency on `{php}Highlighter`, which has a dependency on `{php}CssTheme`. We've manually constructed all dependencies needed, but it's still dependency injection.

As a side note; I like to think of dependencies as more than only objects, a dependency could  be a scalar value as well — which is extremely useful for injecting config values, but that's a story for another time.

So: dependency injection is about _injecting dependencies_ from the _outside_ into a class, so that that class doesn't have to worry about constructing and managing those dependencies by itself.

### The container

Next is the container, which is what most people think about when they say "dependency injection". The container is no more than a tool to make dependency injection more convenient. A container could be as simple as this, a class that's essentially a key/value store of dependency definitions: 

```php
class Container
{
    private array $definition;
    
    public function register(string $key, Closure $definition): self
    {
        $this->instances[$key] = $definition;
        
        return $this;
    }
    
    public function get(string $key): mixed
    {
        $definition = $this->instances[$key] ?? throw new UnknownDependency();
        
        return $definition($this);
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

Now, this isn't how most real-life containers work; and that's for good reasons: this is a rather inconvenient way of managing dependencies. I deliberately wrote it like this though to clarify how a container, in its core, is a key/value store of definition functions. You can register objects in it, and you can get objects from it.

A slightly more convenient way to register objects is by not specifying textual keys, but instead using the class name of the object:


```php
class Container
{
    private array $instances;
    
    public function register(string $className, Closure $definition): self
    {
        $this->instances[$instance::class] = $definition;
        
        return $this;
    }
    
    // …
}
```

That way, you don't have to come up with textual IDs for every dependency you want to register, you can simply use its class name:

```php
$container = (new Container())
    ->register(CssTheme::class, fn (Container $container) => new CssTheme)
    ->register(Highlighter::class, fn (Container $container) => new Highlighter($container->get(CssTheme::class)))
    ->register(HttpExceptionHandler::class, fn (Container $container) => new HttpExceptionHandler($container->get(Highlighter::class)));
```

Even more convenient: you could infer the class name from the closure's definition with just a little bit of reflection on the definition's return type:

```php
class Container
{
    private array $instances;
    
    public function register(Closure $definition): self
    {
        $reflection = new ReflectionFunction($definition);
        
        $this->instances[$reflection->getReturnType()->getName()] = $definition;
        
        return $this;
    }
    
    // …
}
```

Which leads to even less work!

```php
$container = (new Container())
    ->register(fn (Container $container): CssTheme => new CssTheme)
    ->register(fn (Container $container): Highlighter => new Highlighter($container->get(CssTheme::class)))
    ->register(fn (Container $container): HttpExceptionHandler => new HttpExceptionHandler($container->get(Highlighter::class)));
```

Remember, our main goal is developer experience at this point. We'd probably cache the final or "compiled" key/value store for production, so why not use a little reflection to make our lives easier?

In the end though, the only thing we're doing is figuring out which key to use for which dependency. Most containers happen to use the class name because it's convenient, but it could be anything you'd like. It's just a key.

### Autowiring

Speaking of convenience, real dependency containers do a lot more than just keeping track of keys and values. Autowiring is probably one of the container's biggest advantages. Autowiring is a mechanism that allows the container to come up with definitions for dependencies itself, instead of needing each definition to be manually defined. You can still define definitions for specific cases if you need to, but only for the cases where autowiring isn't smart enough.

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
        $definition = $this->instances[$key] 
            ?? $this->autowire($key);
        
        return $definition($this);
    }
    
    private function autowire(string $class): Closure
    {
        $constructor = (new ReflectionClass($class))->getConstructor();
        
        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[$parameter->getName()] = $this->get($parameter->getType()->getName());
        }
        
        // The autowire definition is created on the fly
        return fn () => new $class(...$dependencies);
    }
}
```

That's autowiring: adding convenience for developers, so that they don't have to specify every definition manually. The reason the container can do autowiring, is because we've agreed to use class names as dependency keys, and those class names happen to be available within object constructors as well:

```php
final readonly class HttpExceptionHandler
{
    // Highlighter is both the parameter's type,
    // but also the dependency's key:
    
    public function __construct(
        private Highlighter $highlighter,
    ) {}
}
```

You don't _need_ to use class names, by the way. You _could_ make a container that only uses manual defined dependency IDs like in the very first example, and use attributes to handle autowiring:

```php
final readonly class HttpExceptionHandler
{
    public function __construct(
         #[Autowire('highlighter')] private Highlighter $highlighter,
    ) {}
}
```

But honestly, using the class name works and requires less typing. So why bother?

### Singletons

Well, we _do_ sometimes bother, which is why I added tagged singletons in Tempest, but we're getting ahead of ourselves. Let's first discuss singletons on their own. Singletons are a special kind of dependencies: as soon as the container has constructed a singleton, it will be cached, and that cached version is used whenever the same class is requested again.

A simplistic implementation could look something like this:

```php
class Container
{
    // …

    private array $singletons;
    
    // Registering the singleton is very similar
    // to registering normal definitions
    public function singleton(Closure $definition): self
    {
        $reflection = new ReflectionFunction($definition);
        
        $this->singletons[$reflection->getReturnType()->getName()] = $definition;
        
        return $this;
    }
    
    // We need to have some additions in our get method
    public function get(string $className): mixed
    {
        // If there's a singleton definition
        if ($singleton = ($this->singletons[$className] ?? null)) {
        
            // We'll check whether it's still in its definition form
            if ($singleton instanceof Closure) {
            
                // If so, we'll execute the definition once,
                // and store its result (the singleton object)
                $this->singletons[$className] = $singleton($this);
            }
            
            // Finally we'll return the singleton object itself
            return $this->singletons[$className];
        }
        
        // If there's no singleton,
        // we'll just resolve the dependency as normal
        $definition = $this->instances[$className] 
            ?? $this->autowire($className);
        
        return $definition($this);
    }
}
```

Singletons can be super useful in cases where constructing objects take time, like for example objects that represent over-the-wire connections to external services, or when a dependency is used in many places throughout your codebase, in which case it might be better to have one shared instance of it, instead of numerous copies of the same class. 

The highlighter example falls in the latter category: it's used all over the place, including loops, so I'd rather have one instance of it, instead of possibly hundreds.

So, now that we know about the container, autowiring, and singletons, we can finally discuss _tagged singletons_.

### Tagged singletons

In essence, tagged singletons are no different from normal singletons, they just don't use the class name as their identifier. The reason is simple: binding singletons to class names can sometimes be limiting. 

Take our highlighter example: I actually need _two_ singleton instances of it: one for highlighting code on the web, and one for highlighting code in the console. And I actually sometimes need _both_ of them, within the same request or command invocation. Going into the details of why would lead us too far, but I'm fairly certain that everyone who has ever worked on a larger codebase has a similar example: you need _two_ single instances of the same class, slightly configured differently.

One solution people come up with is to have dedicated interfaces for each responsibility. These interfaces don't _do_ anything, they are only there to help the container figure out which class to provide:

```php
interface CliHighlighter extends Highlighter {}
 
interface WebHighlighter extends Highlighter {} 
```

People then provide custom implementations for these interfaces, wrapping the original highlighter object:

```php
final readonly class GenericCliHighlighter implements CliHighlighter
{
    private Highlighter $highlighter;
    
    public function __construct()
    {
        $this->highlighter = new Highlighter(new LightTerminalTheme());
    }
    
    public function parse(string $content): string
    {
        return $this->highlighter->parse($content);
    }
}

final readonly class GenericWebHighlighter implements WebHighlighter
{
    private Highlighter $highlighter;
    
    public function __construct()
    {
        $this->highlighter = new Highlighter(new CssTheme());
    }
    
    public function parse(string $content): string
    {
        return $this->highlighter->parse($content);
    }
}
```

In some cases you'd need to extend the parent class instead of using an interface if the code you're integrating with has chosen inheritance over composition, but it would look quite similar: a _lot_ of code, just to make sure the container understands what we want.

Let's say we don't have two variations of our singleton, but five. What about the overhead then? Remember how the container's task was to make our lives easier? Now we find ourselves making our lives more difficult, just because the container isn't smart enough.

Ok, what about an alternative? We could have a factory class, something like this:

```php
final readonly class HighlighterFactory
{
    public function make(string $key): Highlighter
    {
        return match($key) {
            'cli' => new Highlighter(new LightTerminalTheme()),
            'web' => new Highlighter(new CssTheme()),
        };
    }
}
```

Well… this "solution" is basically extracting part of the responsibility from the container, because it's falling short. It _should_ be the container's task to know how to construct objects, but apparently it doesn't know how to do so in all cases. And thus we provide a custom factory — a mini-container in disguise — to "solve" our problem.

Oh, by the way, have you spotted how this factory doesn't support singletons? Should we implement it? Making it even more a mini-container?

```php
final class HighlighterFactory
{
    private static {:hl-type:?Highlighter:} $cli = null;
    private static {:hl-type:?Highlighter:} $web = null;
    
    public function make(string $key): Highlighter
    {
        return match($key) {
            'cli' => self::$cli ??= new Highlighter(new LightTerminalTheme()),
            'web' => self::$web ??= new Highlighter(new CssTheme()),
        };
    }
}
```

I think you see what I'm getting at, right? Either solutions are truly suboptimal. Remember that we have to write this code for every dependency that needs singleton variations. All because our container lacks.

Ok so, tagged singletons solve this problem. The only thing they do is to optionally define another identifier for a singleton:

```php
$container->singleton(
    Highlighter::class, 
    new Highlighter(new LightTerminalTheme()), 
    tag: 'cli',
);
```

The identifier of this singleton would be `Highlighter#cli` (technically it would be the FQCN of `{php}Highlighter`, but you get the point). Apart from that, you have two ways to specify the tag when requesting a dependency, either when manually resolving a dependency from the container:

```php
$container->get(Highlighter::class, tag: 'cli');
```

Or by using the `{php}#[Tag]` attribute while autowiring:

```php
class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        #[Tag('web')] private Highlighter $highlighter,
    ) {}
}
```

In short, tagged singletons give a programmer control over the dependency's identifier, _only_ when needed. It's the next logical step in making our lives more convenient, which is all the container is about.

## But this is wrong!

I'll list some of the counterarguments I got, and try to defend my case as well as possible. 

**There can only be one singleton per class!** — Well this statement actually mixes two ideas together. The [singleton pattern](https://en.wikipedia.org/wiki/Singleton_pattern) indeed describes one instance per class. However, this isn't necessarily what the container is doing. A more apt name would be _cached dependencies_, but unfortunately we've gotten so used to the term "singleton", which is now causing confusion. 

Also, thanks to modern containers, we've gotten used to _the classname_ being the dependency key, which causes the misconception that the class name can be the _only_ viable dependency key. I hope this blogpost has giving you some food for thought and will allow you to [challenge any preconceptions](/blog/rational-thinking).

**Tagged singletons tie a concrete implementation to an abstract!** — True. By introducing a tag, we request _one specific_ instance from the container. We're doing micromanagement and not programming to abstractions or interfaces anymore. This isn't any different from the two solutions others proposed though. Creating empty interfaces or subclasses just to please the container is equally _concrete_. It's not because we slammed the keyword `{php}interface` in front of it that it suddenly is the perfect design. The factory solution is exactly the same: you request a very _specific_ implementation. The only difference with tagged singletons is that there is more code to write in both other solutions, and more places to fail. 

The reality is, sometimes we _need_ a very specific thing. Adding layers of abstraction just to be able to say you're programming "the right way" — well, it isn't "the right way".

## In closing

I had a lot of fun writing this post, and I hope you enjoyed reading it, even if you disagreed with some parts. I'd love to further talk about it, so feel free to come say hi on the [Tempest Discord](/discord), and subscribe to my [mailing list](/mail) to be kept into the loop about future content. 