I've been fascinated by type systems in programming languages for a while now. 
Recently, something clicked for me regarding inheritance and types.

Not only did it clarify type variance, 
I also understood what the Liskov substitution principle actually is about.
Today, I'm going to share these insights with you.
 
## Prerequisites

I'll be writing pseudo code to make clear what I'm talking about. 
So let's make sure you know what the syntax of this pseudo code will be.

A function will be defined like so.

```txt
foo (T) : T
```

First comes the function name, second the argument list with types as parameters,
and finally the return type.
A function can extend another function, as can types. 
Inheritance is defined like so. 

```txt
foo > bar (T) : T

T > S
```

In this example, `bar` extends `foo`, and `S` is a subtype of `T`.
The last step is being able to invoke the function, which is done like so.

```txt
a = bar (T)
``` 

Once again: it's just pseudo code and I'll use it to demonstrate what types are,
how they can and cannot be defined in combination with inheritance, and 
how this results in type-safe systems.

## Liskov substitution principle

Let's look at the official definition of the LSP.

> If `S` is a subtype of `T`, then objects of type `T` may be replaced with objects of type `S`.

Instead of using `S` and `T`, I'll be using more concrete types in my examples.

```txt
Organism > Animal > Cat
```

These are the three types we'll be working with.
Liskov tells us that wherever objects of type `Organism` appear in our code, 
they must be replaceable by subtypes of `Organism`. 

So given the following function:

```txt
foo (Organism) : Organism
```

It must be possible to call it like so:

```txt
a = foo (Animal)
b = foo (Cat)
```

I like to see a function definition as a contract, a promise; for the programmer to be used. 
The contract states:

> Given an object of the type `Organism`, 
> I'll be able to execute and return an object of type `Organism`.

Because `Animal` and `Cat` are subtypes of `Organism`, 
the LSP states that this function should also work when one of these subtypes are used. 

This brings us to one of the key properties of inheritance. 
If Liskov states that objects of type `Organism` must be replaceable by objects of type `Animal`, 
it means that `Animal` may not change the expectations we have of `Organism`.
`Animal` may extend `Organism`, meaning it may *add* functionality,
but `Animal` may not change the certainties given by `Organism`.

This is where many OO programmers make mistakes.
They see inheritance more like
"re-using parts of the parent type, and overriding other parts in the sub-type",
rather than extending the behaviour defined by its parent.
This is what the LSP guards against.

## Benefits of the LSP

Before exploring the details of type safety with inheritance, a very interesting topic; 
we should stop and ask ourselves what's to gain by following this principle.

I've explained what Barbara Liskov meant when she defined her substitution principle,
but why is it necessary? Is it bad to break it?

I mentioned the idea of a "promise" or "contract" before.
If a function or type makes a promise, a guarantee; we should be able to blindly trust it.
If we can't rely on function `foo` being able to handle all `Organisms`,
there's a piece of undocumented behaviour in our code.
 
Without looking at the implementation of a function, there's a level of security 
that this function will do the thing we expect. 
When this contract is breached, for example if `foo` cannot handle subtypes of `Organism`;
there's a chance of runtime errors we, and the compiler, cannot anticipate.

There's two areas in which this promise can be broken: by the programmer itself 
and by the language's design.
It's the programmer's responsibility to write code that adheres to the LSP, 
and the language can be designed as a type-safe language or not.

## Type safety

We've established what the LSP is, and its goal; 
now we'll have to go one step further to fully grasp the consequences of a type-safe system.

We've seen the LSP being used in the context of passing arguments to functions.
Next we'll look at the function definitions themselves, and how the LSP applies there.

We'll work with these functions:

```php
foo (Animal) : Animal

foo > bar (Animal) : Animal
```

As you can see, `bar` extends `foo` and follows its parent signature one-to-one.
Some programming languages don't allow children to change the type signature of their parent.
This is what's called type invariance.
It's the easiest approach to handle type safety with inheritance.

But when you look back at how our example types are related to each other,
we know that `Cat` extends `Animal`.
Let's think about whether the following is possible.

```txt
foo > bar (Cat) : Cat
```

The LSP only defines rules about objects, so on first sight, the function definition itself doesn't break the LSP.
The real question is: does this function allow for proper use of the LSP when it's called?

```txt
cat = bar (Cat)
```

We know that `bar` extends from `foo`, and thus provides the same contract –or more– as its parent.
We also know that `foo` allows `Animal` and its sub-types to be used.
So `bar` should also be able to take an `Animal` type.

```txt
cat = bar (Animal)

// Type error
```

Unfortunately, this is not the case. Can you see what we're doing here? 
Instead of applying the LSP only to the parameters of a function, 
we're also applying the same principles it to the function itself.

> Wherever an invocation of `foo` is used, we must be able to replace it 
> by an invocation of `bar`.

This especially makes sense in an OO language where a function is no standalone entity in your code,
but rather part of a class, which represents a type itself.

In order to keep a system type-safe,
it may not allow children to make the parameter types more specific.
This breaks the promises given by the parent.

However, take a look at the following definition:

```txt
foo > bar (Organism) : Animal
```

Does this definition ensures type safety? 
It may seem backwards at first, but it does.
`bar` still follows the contract specified by `foo`.
It can take `Animal` as an argument, and work just fine.

In this case, `bar` widens the parameter types allowed, 
while still respecting the parent's contract.
These is called contravariance.
Types in argument lists should be contravariant for a type system to be safe.

Moving on, we'll apply the same thinking to return types:

```txt
foo > bar (Organism) : Organism
```

The same question: is this type safe? Again, the answer is no.

From its parent definition, `bar` should return the type `Animal`. 
We can see the opposite problem arising if we're widen the return type of this child implementation.

```txt
animal = foo (Animal)

// vs

animal = bar (Animal)
```

We'd expect bar to return `Animal`, based on the signature of `foo`. 
In the above example though, calling `bar` allows to return `Organism`!

Because `Organism` doesn't describe fully what `Animal` does, 
there's again an area of undefined behaviour, which can cause runtime errors.
The above example is not type-safe.

This, however, does respect the parent's signature:

```txt
foo > bar (Animal) : Cat
``` 

Because `Cat` is a subtype of `Animal`, we can be a 100% sure that whatever `bar` returns, 
it will be within the category of `Animal`.

You see the opposite rule applies for return types compared to function parameters.
In the case of return types, we're calling it covariance, or covariant types.

## Real-life impact

There' no guarantee that a type-safe language will always write a bug-free program.
We've seen that the language design only carries half the responsibility regarding the LSP.
The other half is the programmer's task.

Languages differ though, all have their own type system, 
and each will have a different level of type safety.

Eiffel, for example, allows for parameter covariance. 
By now you know this means there's an area of wrong behaviour possible that's undetectable by the compiler.
Hence there's the possibility of runtime errors.

PHP allows for constructors of child classes to have another signature, 
while keeping an invariant type system for all other functions.
As with many things PHP, this inconsistency increases confusion of many developers.

Some languages like Java, C# and Rust have a concept that I didn't cover today: generics. 
Type variance also plays a big role there.
The topic is out of the scope for this blog post, but I might cover it in the future.

With all these differences, there's one thing to keep in mind.
The safety of a type system doesn't mean a language is better or worse.
I think it's fair to say that some use cases would benefit from a very strong type system, 
while others need the exact opposite. 
The key takeaway is that every programmer 
should learn more than only the concepts and paradigms of the languages they are used to the most.
A broadened view will be beneficial, now and in the future.

So what's your opinion on type safety? 
If you're up for it, I'd love to talk about it even more: 
you can reach me on [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).
