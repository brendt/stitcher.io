There's a new feature in PHP 8.3: the `#[<hljs type>Override</hljs>]` attribute. It's a feature already known in other languages, but let me summarize in case you're unaware of what it does.

Marking a method with the `#[<hljs type>Override</hljs>]` attributes signifies that you _know_ this method is overriding a parent method. So the only thing it does, is show intent.

Why does that matter? You already know you're overriding a method, don't you? Well, let's imagine these two classes:

```php
abstract class Parent
{
    public function methodWithDefaultImplementation(): int
    {
        return 1;
    }
}

final class Child extends Parent
{
    #[<hljs type>Override</hljs>]
    public function methodWithDefaultImplementation(): int
    {
        return 2; // The overridden method
    }
} 
```

Now, let's imagine at one point the parent method changes its method name:

```php
abstract class Parent
{
    public function methodWithNewImplementation(): int
    {
        return 1;
    }
}
```

Before the `#[<hljs type>Override</hljs>]` attribute, there was no way of knowing that `<hljs type>Child</hljs>::<hljs prop>methodWithDefaultImplementation</hljs>()` doesn't override the renamed method anymore, which could lead to unforeseen bugs.  

Thanks to `#[<hljs type>Override</hljs>]` though, PHP now knows something is wrong, thanks to that attribute. It basically says "I know this method should override a parent method. If that would ever change, please let me know".

## Some thoughts

What strikes me most about this RFC, is how irrelevant it could be. Once again we're adding runtime checks for something that could be determined by static analysers. 

I don't want to repeat every argument I made in the past, so I'll just [link to my previous thoughts on the topic](/blog/we-dont-need-runtime-type-checks), and summarise: we're missing out. PHP internals should either come with an official spec for static analysers, or with a first-party static analyser. Why? Because so much more would be possible, and it would drive PHP forward tremendously.

Anyway, it seems like many people have tried to make the same argument about this RFC in particular [on the Internals mailing list](https://externals.io/message/120233), to no avail.

I don't mind this feature, although I'll probably never use it myself (I use an IDE that prevents me from making these kinds of mistakes, I don't need PHP's runtime to double-check it for me). What I am sad about, is how the PHP community is [divided](/blog/thoughts-on-asymmetric-visibility) between the static-analysis and non-static-analysis camps, and I don't know if there will ever be someone who'll be able to unify and steer the language into a next decade of progression.

{{ cta:mail }}
