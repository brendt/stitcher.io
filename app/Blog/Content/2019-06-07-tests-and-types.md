---
title: 'Tests and types'
next: liskov-and-type-safety
meta:
    description: 'Thoughts on strong and weakly typed programming languages'
footnotes:
    - { link: /blog/liskov-and-type-safety, title: 'What makes a type system safe; how does the Liskov substitution principle help with this; and what does it mean regarding type variance?' }
    - { link: /blog/combining-event-sourcing-and-stateful-systems, title: 'Bridging the gap between stateful and event sourced sub-systems' }
    - { link: /blog/what-php-can-be, title: "Thoughts on PHP's type system and what it means for the language as a whole" }
---

Imagine a simple function: `rgbToHex`. 
It takes three arguments, integers between 0 and 255; and converts it to a hexadecimal string.

Here's what this function's definition might look like in a dynamic, weakly typed language:

```txt
rgbToHex(red, green, blue) {
    // …
}
```

I think we all agree that "program correctness" is essential. 
We don't want any bugs, so we write tests.

```txt
assert(rgbToHex(0, 0, 0) == '000000')

assert(rgbToHex(255, 255, 255) == 'ffffff')

assert(rgbToHex(238, 66, 244) == 'ee42f4')
```

Because of our tests, we can be sure our implementation works as expected. Right? 

Well… We're actually only testing three out of the 16,777,216 possible colour combinations. 
But human reasoning tells us that if these three cases work, all probably do.

What happens though if we pass doubles instead of integers? 

```txt
rgbToHex(1.5, 20.2, 100.1)
``` 

Or numbers outside of the allowed range?

```txt
rgbToHex(-504, 305, -59)
```

What about `null`?

```txt
rgbToHex(null, null, null)
```

Or strings?

```txt
rgbToHex("red", "green", "blue")
```

Or the wrong amount of arguments?

```txt
rgbToHex()

rgbToHex(1, 2)

rgbToHex(1, 2, 3, 4)
```

Or a combination of the above? 

<a name="read-on"></a>

I can easily think of five edge-cases we need to test, 
before there's relative certainty our program does what it needs to do.
That's at least eight tests we need to write — and I'm sure you can come up with a few others given the time.

These are the kind of problems a type system aims to _partially_ solve. 
And note that word _partially_, we'll come back to it.

If we filter input by a type — you can think of it as a subcategory of all available input — many of the tests become obsolete.

Say we'd only allow integers:

```txt
rgbToHex(Int red, Int green, Int blue) 
{
    // …
}
```
 
Let's take a look at the tests that aren't necessary anymore thanks to the `Int` type:

- Whether the input is numeric
- Whether the input is a whole number
- Whether the input isn't null

To be honest, we can do better than this: 
we still need to check whether the input number is between 0 and 255.

Unfortunately at this point, we run against the limitations of many type systems. 
Sure we can use `Int`, though in many cases (as with ours) 
the category described by this type is still too large for our business logic.
Some languages have a `UInt` or "unsigned integer" type;
yet this still too large a subset of "numeric data".

Luckily, there are ways to address this issue. 

One approach could be to use "configurable" or generic types, for example `Int<min, max>`. 
The concept of generics is known in many programming languages, 
though I'm unaware of any language that let's you configure scalar types such as integers.

Edit: one of my readers let me know this _is_ possible in Ada. Thanks, Adam!

Nevertheless in theory, a type could be preconfigured in such a way that it's smart enough to know about your business logic. 

Languages that lack these kinds of generic types, often need to build custom types.
Being an OO programmer myself, I would use classes to do this.

```txt
class MinMaxInt
{
    public MinMaxInt(Int min, Int max, Int value)
    {
        assert(min <= value <= max)
        
        this.value = value
    }
}
```

If we're using an instance of `MinMaxInt`, we can be sure its value is constrained within a subset of integers.

Still, this `MinMaxInt` class is too generic for our case. 
If we were to type `rgbToHex` with it, we're still not sure what the exact boundaries are:

```txt
rgbToHex(MinMaxInt red, MinMaxInt green, MinMaxInt blue) 
{
    // …
}
```

We need a more specific type: `RgbValue`. 
Adding it depends, again, on the programming language and personal preference. 
I would extend `MinMaxInt`, but feel free to do whatever fits you best.

```txt
class RgbValue extends MinMaxInt
{
    public RgbValue(Int value)
    {
        parent(0, 255, value)
    }
}
```

Now we've arrived at a working solution. 
By using the `RgbValue` type, most of our tests become redundant.

```txt
rgbToHex(RgbValue red, RgbValue green, RgbValue blue) 
{
    // …
}
```

We can now have one test to test the business logic: "given three ((RGB))-valid colors, 
does this function return the correct HEX value?" — a great improvement!

## Caveats

Close readers can already think of one or two counter arguments. 
Let's address them.

### Tests are just moved

If we're building custom types, we still have to test those. 
That's true in my example, which is influenced by the languages I work in.

It depends on the capabilities of the language though. 
Given a language that allows this:

```txt
rgbToHex(
    Int<0, 255> red, 
    Int<0, 255> green, 
    Int<0, 255> blue
) {
    // …
}
```

You'd need zero extra tests, as the features are baked into the language itself.

But even if we're stuck with having to build custom types and testing them: 
don't forget they are reusable throughout the code base.

Chances are you'll be able to re-use most of the types you're making;
as these custom categories most likely apply to your business, and are used throughout it.

{{ cta:mail }}

### Verbosity

Next, many would consider my solution too verbose when actually using it:

```txt
rgbToHex(
    new RgbValue(60),
    new RgbValue(102),
    new RgbValue(79)
);
```

While I personally don't mind this verbosity — I know the benefits of a stronger type system — I'd like to ask you to think out of your box for a moment.
This argument isn't against stronger types, it's one against your programming language.

The verbosity is caused by the lack of proper syntax provided by the language.
Fortunately I can think of ways the problem could be solved.

One solution is type juggling.
Dynamic languages are actually pretty good at it. 
Say you'd pass a simple integer as the input, 
the compiler can try and cast that integer to an object of `RgbValue`.
It could even be aware of possible types which could be cast to `RgbValue`,
so you'd still have compile-time error detection.

### Example in isolation

Another objection might be that your real-life code base obviously differs from a simple `rgbToHex` function.

I want to argue the opposite though: the reasoning behind this example can be applied to any part of your code.
The actual difficulty lies in the languages and frameworks used:
if strong types aren't built in from the ground up, 
you're gonna have a hard time getting the most use out of them.

This is where I should recommend you to watch [this talk](*https://www.destroyallsoftware.com/talks/ideology) by Gary Bernhardt, 
it's less than 30 minutes long.
In it, he takes the topic of type systems and confronts us with our own prejudices and ideologies about them.

Afterwards you can apply this thinking on the current frameworks and languages you're using. 

While my example is an example in isolation, the underlying problems solved by stronger types can easily be scaled, 
_if_ the infrastructure supports it.

So am I suggesting you should ditch your whole stack, or that you're a bad programmer for using a weakly typed language?
Definitely not! 

I myself program in PHP every day, it's [not as bad as it used to be](/blog/php-in-2019).
PHP introduced an opt-in type system, so it's possible to write fairly strongly typed code, 
even though the language wasn't originally built for it.
Another example coming to mind is JavaScript with TypeScript.

So it is possible to leverage a type system, even in many languages that weren't originally built for it.
But it will require a mind shift from your side. 
In my experience, it's worth the effort.

### Limitations

Finally, let's address the elephant in the room when it comes to type systems.
I hope it's clear that, while many tests may be omitted thanks to a strong type system, some still need to be written.

People claiming you don't have to write tests in strongly typed languages are wrong.

Remember the _partially_ I mentioned earlier? 

In an ideal world, the perfect type system would be able to account for all specific categories required by your business. 
This is impossible to do though, as computers and programming languages only have limited resources to work with.

So while strong types can help us to ensure program correctness, 
some tests will always be a necessity to ensure business correctness.
It's a matter of "both and", not "either or".

## Closing Remarks

I mentioned several concepts in this posts, 
but I also mentioned I didn't know of programming languages using some of the concepts I described.
I'd love to give some concrete examples though. 

So if you're working in a language that should be mentioned in this post, 
please let me know via [Twitter](*https://twitter.com/brendt_gd), [e-mail](mailto:brendt@stitcher.io), or wherever you read this post on social media.

You can of course also reach out to share other thoughts on this topic, I'd love to hear from you!
