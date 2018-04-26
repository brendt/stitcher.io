You're in the car business, 
your job is to make cars on-demand. 
The object-oriented programmer in you says: 
"no problem, I'll make a blueprint that I can use to make as much cars as I want!".

```php
class Car
{
    public function drive()
    {
        // ...
    }
}
```

For this car to work, it needs an engine and wheels.
Now, there are several approaches to achieve that goal.
You could, for example, do the following:

```php
class Car
{
    public function __construct()
    {
        $this->engine = new Engine();
        
        $this->wheels = [
            new Wheel(), new Wheel(), 
            new Wheel(), new Wheel(),
        ];
    }
    
    public function drive() { ... }
}
```

There's the blueprint for every car you'll make!—Next up, 
your boss comes to you and says there's a new client and he wants an electrical car.

So you end up doing this.

```php
class ElectricalCar extends Car
{
    public function __construct()
    {
        parent::__construct();
        
        $this->engine = new ElectricalEngine();
    }
}
```

"Beautifully solved"—you think. 
There's of course that redundant normal engine that's created when calling `parent::__construct()`,
but at least you could re-use the wheels!

I think you can see where this is going.
The next client wants a car with some fancy wheel covers,
another one would like a diesel engine with those same wheel covers,
another one requests a race car,
and the last one wants a self driving car. 
<br>
Oh—there also was a client who wanted to buy an engine to build a boat with himself,
but you told your boss that wouldn't be possible.

After a while, there's a ton of blueprints in your office, 
each describing a very specific variation of a car.
You started with a neatly ordered pile of blueprints.
But after a while you had to group them in different folders and boxes,
because it was taking too long to find the blueprint you're looking for.

Object oriented programmers often fall into this trap of inheritance, 
ending in a completely messed up codebase.
So let's look at a better approach.
Maybe you've heard about "composition over inheritance" before? 

> Composition over inheritance is the principle that classes should achieve polymorphic behavior 
> and code reuse by their composition rather than inheritance from a base or parent class—[Wikipedia](*https://en.wikipedia.org/wiki/Composition_over_inheritance)

That's a lot of buzzwords. Let's just look at our car example.
The principle states that `Car` should achieve its polymorphic behaviour 
by being composed of other classes.

The word *polymorphic* literally means "many shapes" 
and implies that `Car` should be able to do `drive` in many different ways,
depending on the context it's used in.

With *code reuse*, we're trying to make code reusable; 
so that we don't end up with tens of classes doing almost exectly the same.

## What does this have to do with dependency injection?

Instead of making a unique blueprint that describes every single possible variation of a car,
we'd rather have `Car` do one thing, and do it good: drive.

This means it shouldn't be the car's concern how its engine is built, 
what wheels it has attached. 
It should only know the follwing thing:

> Given a working engine and four wheels, I'm able to drive!

We could say that in order for `Car` to work, it *needs* an engine and wheels.
In other words: `Car` depends on `Engine` and a collection of `Wheels`.

Those dependencies should be *given* to the car. Or, said otherwise: injected.

```php
class Car
{
    public function __construct(
        Engine $engine, 
        array $wheels
    ) {
        $this->engine = $engine;
        $this->wheels = $wheels;
    }
    
    public function drive()
    {
        $this->engine->connectTo($this->wheels);
        
        $this->engine->start();
        
        $this->engine()->accelerate();
    }
}
```

Would you like a race car? No problem!

```php
$raceCar = new Car(new TurboEngine(), [
    new RacingWheel(), new RacingWheel(),
    new RacingWheel(), new RacingWheel(),
]);
```

That client who wanted special wheel covers? You've got that covered!

```php
$smugCar = new Car(new Engine(), [
    new FancyWheel(), new FancyWheel(),
    new FancyWheel(), new FancyWheel(),
]);
```

You've got *a lot* more flexibility now!

Dependency injection is the idea of giving a class its requirements form the outside,
instead of having that class being responsible for them itself.

## What dependency injection is not

Built upon this simple principle, there are frameworks and tools that take it to the next level.
You might, for example, have heard about the following things before.

### Shared dependencies

One of the most beneficial side effects of injecting dependencies,
is that the outside context can control them. 
This means that you can give the same instance of a class 
to several others that have a dependency on that class.

Shared- or reusable dependencies are the ones most often getting the label "dependecy injection".
Though it's certainly a very good practice, 
sharing a dependency is not actually the core meaning of dependency injection. 

### The dependency container

Sometimes it's also called "inversion of control" container, though that's not an accurate name.

Whatever the exact name, the conainer is a set of class definitions. 
It's a big box that knows how objects in your application can be constructed with other dependencies.
While such a container definitely has a lot of use cases, it's not necessary to do dependency injection.

### Auto wiring

To give developers even more flexibility, some containers allow 
for smart, automatically determined, class definitions. 
This means you don't have to manually describe how every class should be constructed.
These containers will scan your code, and determine which dependencies are needed
by looking at type hints and doc blocks.

A lot of magic happens here, but auto wiring can be a useful tool for rapid application development.

### Service location

Instead of injecting dependencies into a class, 
there are some tools and frameworks that allow a class to ask the container 
to "give it an instance of another class". 

This might seem beneficial at first, 
because the class doesn't need to know how to construct a certain dependency.
However: by allowing a class to ask for dependencies on its own account,
we're back to square one. 

For service location to work, our class needs to know about the systems on the outside.
It doesn't differ a lot from calling `new` in the class itself. 
This idea is actually the opposite of when dependency injection tries to achieve. 

### Inject everything

As it goes in real-life project, you'll notice that dependency injection
in not *always* the solution for your problem.

It's important to realise that there's limits to the benefits of everything.
You should always be alert that you're not taking this to the extreme,
as there are valid cases in which a pragmatic approach *is* the better solution.   

## In closing

The core idea behind dependency injection is very simple, 
yet it allows for better maintainable, testable and decoupled code to be written. 

Because it's such a powerful pattern, 
it's only natural that lots of tools emerge around it. 
I believe it's a good thing to first understand the underlying principle, 
before using the tools built upon it. 
And I hope this blog post has helped with that.

If there are any thoughts coming to your mind that you want to share,
feel free to reach out to me on via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

Special thanks to [/u/ImSuperObjective2](*https://www.reddit.com/user/ImSuperObjective2) on Reddit
for proof reading this post.

---
