I made this video about generics last week, you can watch it (make sure to like and subscribe if you liked it), or you can read the transcript here if you don't like watching videos. Also make sure to share your opinions on the topic via [Twitter](*https://twitter.com/brendt_gd) or [email](mailto:brendt@stitcher.io)!

<iframe width="560" height="435" src="https://www.youtube.com/embed/FiQdmnnIpEY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

---

Generics. We all want them, they are probably not going to be built-into PHP any time soon. BUT there is proper support for generics using docblocks; both by static analysers like PHPStan and Psalm, but also — and this is a big one — by PhpStorm.

You see, only a few months ago, PhpStorm added basic support for generics using docblocks.
And it does in fact work with quite a lot of cases, PhpStorm can tell us — in real time, while coding — what kind of generic type we're dealing with. It's even smart enough to infer generic types in some occasions.

If you have no clue what I'm talking about right now, I would suggest doing some reading on my blog — I've added some useful links in the description for you. But I also want to give an example.

Here we have a kind of "attribute query" class: a class that can filter and instantiate attributes:

```php
$routeAttributes = <hljs type>Attributes</hljs>::<hljs prop>new</hljs>(<hljs type>MyController</hljs>::class)
    -><hljs prop>instanceOf</hljs>(<hljs type>Route</hljs>::class)
    -><hljs prop>first</hljs>();
```

It provides a slightly cleaner API compared to straight up using PHP's built-in reflection classes.

Now, after running this query here, I want my IDE to know, in real time, that I have an instance of the Route attribute here. And this is exactly the kind of complex example that PhpStorm is now able to detect, and it's a huge time saver.

Let me show you the query itself, or at least: the interesting parts of it.

```php
/**
 * @template <hljs generic>AttributeType</hljs>
 */
class Attributes
{
    /**
     * @return <hljs generic>AttributeType</hljs>
     */
    public function first(): mixed
    { /* … */ }
}
```

Here we have our attribute class, with a generic `<hljs generic>AttributeType</hljs>`, and that's the type that's returned by the first method, in this example. The problem here: how do we actually set that generic type? It's actually pretty straight forward when you know about generic type inference.

```php
/**
 * @template <hljs generic>AttributeType</hljs>
 */
class Attributes
{
    /**
     * @template <hljs generic>InstanceOfType</hljs>
     *
     * @param <hljs type>class-string<</hljs><hljs generic>InstanceOfType</hljs><hljs type>></hljs> $className
     *
     * @return <hljs type>self<</hljs><hljs generic>InstanceOfType</hljs><hljs type>></hljs>
     */
    public function instanceOf(<hljs type>string</hljs> $className): self
    { /* … */ }
}
```

Here we have our `<hljs prop>instanceOf</hljs>` method, and you can see it defines another generic type, it's called `<hljs generic>InstanceOfType</hljs>`. Now, PhpStorm, and other static analysers; are smart enough to detect — or infer the type of the input that's passed to this function — that's the name of the attribute we want to filter on — and use that type as the generic type for our attributes class, when we return it. This kind of generic type inference is an incredibly powerful tool.

You might need to read this example a few times before getting it, but it essentially allows us to create classes like this attribute query class where the end user still has lots of information available to them while using this package.

Now, this is actually not what I wanted to talk about today. It's all pretty cool, and I'm very excited about it; but if you know me, you know that I like to think about the details. And one of those details that we need to talk about, now that generics in PHP are much more accessible; is how to name them.

I used `<hljs generic>AttributeType</hljs>` and `<hljs generic>InstanceOfType</hljs>` as the generic type placeholders, but that's kind of not the convention in most programming languages.

I looked at quite a lot of them, and by far, the most popular convention — I think that's thanks to Java — is to use a single letter for generic types; so T, or E or V or K or U — those are some of the popular choices. And that are quite a lot of languages that follow this convention: Java, Kotlin, Rust, Go, also C# and Swift.

And I don't know about you, but I find that this makes my code so much harder to read. And the reasoning behind using these single letters, is to make it clear, by their name, that these are generic types and not real types. But as you can see in my screenshots, you could very well just use a different colour for generic types to differentiate them.

I need to mention though that PhpStorm [doesn't support that yet](https://youtrack.jetbrains.com/issue/WI-63801), but I hope that they'll change it after seeing this video.

Another convention that I've seen around — especially in the Psalm and PHPStan's documentation is to prefix the generic name with an uppercase letter T. But is that really more readable than suffixing it with "Type"? `<hljs generic>TAttribute</hljs>` or `<hljs generic>AttributeType</hljs>`; `<hljs generic>TInstanceOf</hljs>` or `<hljs generic>InstanceOfType</hljs>`? The second one sounds much more like we'd say it in human language, no?

So yeah, these are the kinds of details I'm thinking about, because I feel like they genuinely affect the readability of my code, and code of others that I need to work in.

So, what I would like to know: what's your opinion?

I made [a poll on Twitter](https://twitter.com/brendt_gd/status/1455760170752036867), and was surprised that so many people — more than 50% — preferred the single letter approach. That's terrible for code readability — especially if you're working with multiple generic types within the same context.

Anyway; I'll probably stick with what feels best for me and what I think is the most readably; but do share your opinions in the comments or on reddit or twitter wherever you're watching this; maybe someone is able to change my mind; or maybe I just changed yours?

{{ cta:like }}

{{ cta:mail }}
