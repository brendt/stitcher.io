I've been fascinated by type systems in programming languages for a few years now. 
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

Before exploring the details of type safety with inheritance
–which is a very interesting topic– 
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

Now that we've established what the LSP is, and what its goal is; 
we'll have to go one step further to fully grasp the consequences of a type-safe system.

We've seen the LSP being used from the context of passing arguments to functions.
Now we'll look at the function definitions themselves, and how the LSP applies there.

We'll work with these functions:

```txt
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

The LSP only defines rules about objects, so, on first sight, the function definition itself doesn't break the LSP.
The real question is: does this function allow for proper use of the LSP when it's called?

```txt
cat = bar (Cat)
```

We know that `bar` extends from `foo`, and thus provides the same contract –or more– as its parent.
We also know that `foo` allowed for types of `Animal` to be used.
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

This especially makes sense in an OO language where these functions are no standalone entities in your code,
but rather parts of a class, which represents a type itself.

The conclusion for the argument list is that, in order to keep your language type-safe,
it may not allow for child implementations to make the method signature more specific, 
as it breaks the promises gave by the parent.

However, take a look at the following definition:

```txt
foo > bar (Organism) : Animal
```

Does this definition ensures type safety? 
It may seem backwards at first, but it does.
`bar` still follows the contract specified by `foo`.
It can take `Animal` as an argument, and work just fine.

In this case, `bar` widened the types it allows as its parameters, 
while still respecting the parent's contract.

In this case, we're speaking of contravariance.
Types in argument lists should be contravariant for a type system to be safe.

Let's look at return types next.

 

## otherss

```txt
ƒ: A (Animal): Animal

ƒ: B < A (Animal): Animal
```

```txt
ƒ: B < A (Cat): Animal
```

```
animal = new Animal
a = A (animal)
```

LSP says that the following should be possible

```
a = B (animal)
```

which breaks because B expects `Cat` and we're giving `Animal`. 

What would work is the following

```txt
ƒ: A > B (Organism): Animal
```

> Argument list types should be contravariant.

Return types are the opposite:

```
a = new A (animal)
```

We expect A to return an animal here.

if B were to return an organism, our assumptions break, however, B may return `Cat`, 
as it derives from `Animal`

```txt
ƒ: A > B (Animal): Cat
```

Because now we're can still be sure that, even if A is switched out for B, we'll still have some kind of `Animal` as the result.

> Return types are covariant.
