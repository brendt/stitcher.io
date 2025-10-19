I've been fascinated by type systems in programming languages for a while now. 
Recently, something clicked for me about inheritance and types.

Not only did it clarify type variance, 
I also understood what the Liskov substitution principle actually is about.
Today, I'm going to share these insights with you.
 
{{ ad:carbon }}

## Prerequisites

I'll be writing pseudo code to make clear what I'm talking about. 
So let's make sure you know what the syntax of this pseudo code will be.

A function is defined like so.

```txt
<hljs prop>foo</hljs>(<hljs type>T</hljs>) : <hljs type>void</hljs>

<hljs prop>bar</hljs>(<hljs type>S</hljs>) : <hljs type>T</hljs>
```

First comes the function name, second the argument list with types as parameters,
and finally the return type.
When a function returns nothing, it's indicated as `void`.

A function can extend — overwrite — another function, as can types. 
Inheritance is defined like so. 

```txt
<hljs prop>bar</hljs> > <hljs prop>baz</hljs>(<hljs type>S</hljs>) : <hljs type>T</hljs>

<hljs type>T</hljs> > <hljs type>S</hljs>
```

In this example, `baz` extends `bar`, and `S` is a subtype of `T`.
The last step is being able to invoke the function, which is done like so.

```txt
<hljs prop>foo</hljs>(<hljs type>T</hljs>)

a = <hljs prop>bar</hljs>(<hljs type>S</hljs>)
``` 

Once again: it's all pseudo code and I'll use it to show what types are,
how they can and cannot be defined in combination with inheritance, and 
how this results in type-safe systems.

## Liskov substitution principle

Let's look at the official definition of the LSP.

> If `S` is a subtype of `T`, then objects of type `T` may be replaced with objects of type `S`
><br>—[Wikipedia](*https://en.wikipedia.org/wiki/Liskov_substitution_principle)

Instead of using `S` and `T`, I'll be using more concrete types in my examples.

```txt
<hljs type>Organism</hljs> > <hljs type>Animal</hljs> > <hljs type>Cat</hljs>
```

These are the three types we'll be working with.
Liskov tells us that wherever objects of type `Organism` appear in our code, 
they must be replaceable by subtypes like `Animal` or `Cat`. 

Let's say there's a function used to `feed` an `Organism`. 

```txt
<hljs prop>feed</hljs>(<hljs type>Organism</hljs>) : <hljs type>void</hljs>
```

It must be possible to call it like so:

```txt
<hljs prop>feed</hljs>(<hljs type>Animal</hljs>)
<hljs prop>feed</hljs>(<hljs type>Cat</hljs>)
```

Try to think of function definition as a contract, a promise; for the programmer to be used. 
The contract states:

> Given an object of the type `Organism`, 
> I'll be able to execute and `feed` that `Organism`.

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

Before exploring the details of type safety with inheritance, 
we should stop and ask ourselves what's to gain by following this principle.
I've explained what Barbara Liskov meant when she defined it,
but why is it necessary? Is it bad to break it?

I mentioned the idea of a "promise" or "contract".
If a function or type makes a promise about what it can do,
we should be able to blindly trust it.
If we can't rely on function `feed` being able to feed all `Organisms`,
there's a piece of undocumented behaviour in our code.
 
If we know that the LSP is respected, there's a level of security.
We may trust that this function will do the thing we expect; 
even without looking at the implementation of that function. 
When the contract is breached, however; there's a chance of runtime errors 
that both the programmer and the compiler could not –or did not– anticipate for.

In the above examples, we looked at respecting the LSP form the developer's point of view.
There's another party involved though: a language's type system.
A language can be designed in a type-safe way or not. 
Types are the building blocks to mathematically proof whether a function will do the thing you want it to do.

So, next up; we're going to look at the other side: type-safety on the language level.

## Type safety

To understand how type safety can –or cannot– be guaranteed by a language,
let's look at these functions. 

```php
<hljs prop>take_care</hljs>(<hljs type>Animal</hljs>) : <hljs type>void</hljs>

<hljs prop>take_care</hljs> > <hljs prop>feed</hljs>(<hljs type>Animal</hljs>) : <hljs type>void</hljs>
```

As you can see, `feed` extends `take_care` and follows its parent signature one-to-one.
Some programming languages don't allow children to change the type signature of their parent.
This is called type invariance.

It's the easiest approach to handle type safety with inheritance,
as types are not allowed to *vary* when inheriting.

But when you think back at how our example types are related to each other,
we know that `Cat` extends `Animal`.
Let's see whether the following is possible.

```txt
<hljs prop>take_care</hljs>(<hljs type>Animal</hljs>) : <hljs type>void</hljs>

<hljs prop>take_care</hljs> > <hljs prop>feed</hljs>(<hljs type>Cat</hljs>) : <hljs type>void</hljs>
```

The LSP only defines rules about objects, so on first sight, the function definition itself doesn't break any rules.
The real question is: does this function allow for proper use of the LSP when it's called?

We know that `feed` extends from `take_care`, and thus provides at least the same contract as its parent.
We also know that `take_care` allows `Animal` and its sub-types to be used.
So `feed` should also be able to take an `Animal` type.

```txt
<hljs prop>feed</hljs>(<hljs type>Animal</hljs>)

<hljs error>// Type error</hljs>
```

Unfortunately, this is not the case. There's a type error occurring.
Can you see what we're doing here? 
Instead of applying the LSP only to the parameters of a function, 
we're also applying the same principles to the function itself.

> Wherever an invocation of `take_care` is used, we must be able to replace it 
> with an invocation of `feed`.

This especially makes sense in an OO language where a function is no standalone entity in your code,
but rather part of a class, which represents a type itself.

To keep a system type-safe, it may not allow children to make the parameter types more specific.
This breaks the promises given by the parent.

However, take a look at the following definition:

```txt
<hljs prop>take_care</hljs>(<hljs type>Animal</hljs>) : <hljs type>void</hljs>

<hljs prop>take_care</hljs> > <hljs prop>feed</hljs>(<hljs type>Organism</hljs>) : <hljs type>void</hljs>
```

Does this definition ensures type safety? 
It may seem backwards at first, but it does.
`feed` still follows the contract specified by `take_care`.
It can take `Animal` as an argument, and work just fine.

In this case, `feed` widens the parameter types allowed, 
while still respecting the parent's contract.
This is called contravariance.
Types in argument lists should be contravariant for a type system to be safe.

## Return type variance

Moving on to return types. 
There are a few more types we'll have to define, in order for the examples to make sense. 
I'm sorry in advance for the choice of words!

```txt
<hljs type>Excretion > <hljs type>Poop</hljs>
``` 

And these are the functions we're working with. 

```txt
<hljs prop>take_care</hljs>(<hljs type>Animal</hljs>) : <hljs type>Excretion</hljs>

<hljs prop>take_care</hljs> > <hljs prop>feed</hljs>(<hljs type>Animal</hljs>) : <hljs type>Poop</hljs>
```

The question now: is the overridden return type safe?
In contrast to the contravariance for the argument list, 
this example actually is type safe!

The parent definition `take_care` tells us that this function will always return 
an object of type `Excretion`. 

```txt
excretion = <hljs prop>take_care</hljs>(<hljs type>Animal</hljs>)

excretion = <hljs prop>feed</hljs>(<hljs type>Animal</hljs>)
```

Because `Poop` is a subtype of `Excretion`, we can be a 100% sure that whatever `feed` returns, 
it will be within the category of `Excretion`.

You see the opposite rule applies for return types compared to function parameters.
In the case of return types, we're calling it covariance, or covariant types.

## Real-life impact

There' no guarantee that a type-safe language will always write a bug-free program.
We've seen that the language design only carries half the responsibility of respecting the LSP.
The other half is the programmer's task.

Languages differ though, all have their own type system, 
and each will have a different level of type safety.

Eiffel, for example, allows for parameter covariance. 
By now you know this means there's an area of wrong behaviour possible that's undetectable by the compiler.
Hence there's the possibility of runtime errors.

PHP allows for constructors of child classes to have another signature, 
while keeping an invariant type system for all other functions.
As with many things PHP, this inconsistency increases the confusion for developers.

Some languages like Java, C# and Rust have a concept that I didn't cover today: generics. 
Type variance also plays a big role there.
That topic is out of scope for this blog post, but I might cover it in the future.

With all these differences, there's one thing to keep in mind.
The safety of a type system doesn't mean a language is better or worse.
I think it's fair to say that some cases would benefit from a very strong type system, 
while others need the exact opposite. 
The key takeaway is that every programmer 
should learn more than just the concepts and paradigms of the languages they are used to the most.
A broadened view will be beneficial, now and in the future.

So what's your opinion on type safety? 
If you're up for it, I'd love to talk about it even more: 
you can reach me on [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).
