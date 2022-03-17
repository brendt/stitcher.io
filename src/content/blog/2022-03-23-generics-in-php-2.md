I showed a very boring example of generics in the previous video, we’re going to do better in this one.

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();

$slugs = new <hljs type>Collection</hljs><<hljs generic>string</hljs>>();
```

Collections; they are probably the easiest way to explain what generics are about, but they also are the example that everyone talks about when discussing generics. It’s actually not uncommon for people to think that “generics” and “collections with a type” are the same thing. That’s definitely not the case.

So let’s take a look at two more examples.

<div class="sidenote">
<div class="center">
    <a href="https://www.youtube.com/watch?v=5CwOuHCp29I&list=PL0bgkxUS9EaKyOugEDffRzsvupBE2YEoD&index=2&ab_channel=BrentRoose" target="_blank" rel="noopener noreferrer">
        <img class="small" src="/resources/img/static/generics-thumb-2.png">
        <p><em class="center small">You can watch the video instead of reading a blog post — if you prefer that!</em></p>
    </a>
</div>
</div>

Here’s a function called `<hljs prop>app</hljs>` — if you work with a framework like Laravel, it might look familiar: this function takes a class name, and will resolve an instance of that class using the dependency container:

```php
function app(<hljs type>string</hljs> $className): mixed
{
    return <hljs type>Container</hljs>::<hljs prop>get</hljs>($className);
}
```

Now, you don’t need to know how the container works, what’s important is that this function will give you an instance of the class that you request.

So, basically, it’s a generic function; one whose return type will depend on what kind of class name you gave it. And it would be cool if our IDE and other static analysers also understand that if I give the classname “UserRepository” to this function, I expect an instance of UserRepository to be returned, and nothing else:

```php
function app(<hljs type>string</hljs> $className): mixed
{ /* … */ }

<hljs prop>app</hljs>(<hljs type>UserRepository</hljs>::class); // ?
```

Well, generics allow us to do that.

And I guess this is a good time to mention that I’ve been keeping a secret, kind of: I [previously said](/blog/generics-in-php-1) that generics don’t exist in PHP; well, that’s not entirely true. All static analysers out there — the tools that read your code without running it, tools like your IDE — they have agreed to use doc block annotation for generics:

```php
/**
 * @template <hljs generic>Type</hljs>
 * @param <hljs type>class-string</hljs><<hljs generic>Type</hljs>> $className
 * @return <hljs generic>Type</hljs>
 */
function app(<hljs type>string</hljs> $className): mixed
{ /* … */ }
```

Granted: it’s not the most pretty syntax, and all static analysers are relying on a simple agreement that this is the syntax — there’s no official specification; but nevertheless: it works. Both PhpStorm, Psalm and PhpStan — those are the three largest static analysers in the PHP world — understand this syntax to some degree.

IDEs like PhpStorm use it to give the programmer feedback when they are writing code, and tools like Psalm and PhpStan use it to analyse your codebase in bulk and detect potential bugs, mostly based on type definitions.

So actually, we can build this `<hljs prop>app</hljs>` function in such a way that our tools aren’t operating in the dark anymore. Of course, there’s no guarantee by PHP itself that the return type will be the correct one — PHP won’t do any runtime type checks for this function; but if we can trust our static analysers to be right, there’s very little — or even no chance of this code breaking when running it.

This is the incredible power of static analysis: we can actually be sure that, without running our code; most of it will work as intended. All of that thanks to types — including generics.

Let’s look at an even more complex example:

```php
<hljs type>Attributes</hljs>::<hljs prop>in</hljs>(<hljs type>MyController</hljs>::class)
    -><hljs prop>filter</hljs>(<hljs type>RouteAttribute</hljs>::class)
    -><hljs prop>newInstance</hljs>()
    ->
```

Here we have a class that can “query” attributes and instantiate them on the fly. If you’ve worked with attributes before you know that their reflection API is rather verbose, so I find this kind of helper class very useful.

When we use the `<hljs prop>filter</hljs>` method, we give it an attribute’s class name; and afterwards calling the `<hljs prop>newInstance</hljs>` method, we know that the result will be an instance of our filtered class. And again: it would be nice if our IDE understood what we’re talking about.

You guessed it: generics allow us to do that:

```php
/** @template <hljs generic>AttributeType</hljs> */
class Attributes
{
    /**
     * @template <hljs generic>InputType</hljs>
     * @param <hljs type>class-string</hljs><<hljs generic>InputType</hljs>> $className
     * @return <hljs type>self</hljs><<hljs generic>InputType</hljs>>
     */
    public function filter(<hljs type>string</hljs> $className): self
    { /* … */ }
 
    /**
     * @return <hljs generic>AttributeType</hljs> 
     */   
    public function instanceOf(): mixed
    { /* … */ }
    
    // …
}
```

I hope you start to see how powerful simple type information can be. A couple of years ago, I would have needed an IDE plugin for these kinds of insights to work, now I just need to add some type information.

This latest example doesn’t only rely on generics though, there’s another equally important part that’s in play. Type inference: the ability of a static analyser to “guess” — or reliably determine — a type without the user specifying it. That’s what’s happening with that class-string annotation over there. Our IDE is able to recognise the input we give this function as a class name, and infer that type as the generic type.

So, everything’s solved, right: generics are available in PHP and all major static analysers know how to work with them. Well… there’s a couple of caveats.

First of, there’s no official spec of what generics should look like, right now every static analyser could push its own syntax; they happen to have agreed on one, for now; but there are little future guarantees.

Second: doc blocks are, in my opinion, suboptimal. They feel like a less important part of our codebase. And granted: generic annotations only provide static insights and no runtime functionality, but we’ve seen how powerful static analysis can be, even without runtime type checks. I think it’s unfair to treat type information as “doc comments”, it doesn’t communicate the importance of those types within our code. That’s why we got attributes in PHP 8: all functionality that attributes provide, was already possible with docblock annotations, but that just didn’t feel good enough. The same goes for generics.

And finally: without a proper specification, all three major static analysers have differences between their generics implementations. PhpStorm being the one most lacking at the moment. Ideally, there would be an official specification coming from PHP’s internals. Right now, there isn’t.

These are the main reasons why I believe that it’s worth investing time in a more permanent and sustainable solution. So why doesn’t PHP have proper generics yet? Why do we rely on doc blocks without a clear specification?

That’s for the next post!

{{ cta:mail }}
