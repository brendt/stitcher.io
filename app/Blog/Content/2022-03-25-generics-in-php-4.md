---
title: 'The case for PHP generics'
---

I started [this series](https://stitcher.io/blog/generics-in-php-1) by saying it’s not just about teaching you, it’s also about making my case for what I think is the most viable and the most logical path to adding generics in PHP.

It’s up to you to decide after this video whether you agree or not. So, your honor, I’d like to start my closing statements.

<div class="sidenote">
<div class="center">
    <a href="https://www.youtube.com/watch?v=2o8A9AgccKs&list=PL0bgkxUS9EaKyOugEDffRzsvupBE2YEoD&index=4&ab_channel=BrentRoose" target="_blank" rel="noopener noreferrer">
        <img class="small" src="/resources/img/static/generics-thumb-4.png">
        <p><em class="center small">You can watch the video instead of reading a blog post — if you prefer that!</em></p>
    </a>
</div>
</div>

Adding [monomorphized or reified generics](/blog/generics-in-php-3) won’t happen. At least not according to Nikita who did an extensive amount of research on the topic. Both options either pose performance problems or simply require too much core code refactoring of PHP’s runtime type checker to be achievable within a reasonable amount of time.

However, if we think about the true value that generics bring, it’s not about runtime type checks. By the time PHPs runtime type checker kicks in and possibly throws a type error, we’re already running our code. Our program will crash. And I’ve never heard any user of my programs say “oh, it’s a type error, it’s ok”. No. The program crashed, and that’s the end of story.

Runtime type checks in PHP are a very useful debugging tool, I give you that, and in some cases required for type juggling. But most of the value of PHP’s type system comes from static analysis.

So, if we want generics in PHP we need a mind shift:

First, developers need to **embrace static analysis**. The irony here is that developers who want generics and understand their value, also understand the value of static type checkers. So while there is a group of PHP developers who don’t care about static analysis, they also shouldn’t care about the value that generics bring. Because, these two: generics and static type checking, simply cannot be separated.

Second, if PHP internals decide that statically checked generics have their place in PHP; they should wonder whether static analysis should be left as a responsibility with the community, or whether they should play a role in it. Either by creating **a specification** that every static analyser should follow, or by shipping their **own static type checker**. The second one would definitely be preferable, but you can imagine what an undertaking that would be. I don’t think that relying on proven third part tools should be an issue.

Third, **type juggling simply wouldn’t be possible anymore**, at least not when using generics. You’d have to trust your static type checker. This is a way of programming that PHP developers aren’t really used to, but many other languages do exactly this, and it works fine. A static type checker is incredibly powerful and accurate. I can imagine it’s difficult for PHP developers to understand the power of statically typed languages without having used one before. It’s worth looking into a language like Rust, Java, or even TypeScript, just to appreciate the power of static type systems. Or you could start using one of PHP’s third party static analysers: Psalm or PHPStan.

To summarize: if we want generics in PHP, with all the benefits they bring to static analysis, we need to accept the fact that runtime erased generics are the only viable path.

In closing, a few more remarks I’d address.

First there’s the argument that what I’m describing is already possible with docblocks. If you go back to the [second post in this series](/blog/generics-in-php-2), you’ll find me explaining the differences in detail, but let me quickly summarize:

- Docblocks don’t communicate the same importance to developers as built-in syntax does, which is also why we got attributes in PHP 8; built-in syntax has a value over docblocks
- And, also, there’s no official specification of what generic annotations should look like when using doc blocks. That’s a big issue today, with all three major static analysers having slightly different implementations.

A second remark is that, even with type erasure, we could still expose generic type information via the reflection API. I’m not saying that the type information should be completely gone at runtime, my foremost concern is that PHP shouldn’t check generic types at runtime. I’m not sure what the impact would be on PHP’s core to have generic type information available via reflection; so I’m just putting it out there that I’m not against that idea.

And finally, there is of course another solution. One that anyone could pursue in theory. One that has proven itself in the past: TypeScript. It could be possible for [a superset of PHP](https://www.youtube.com/watch?v=kVww3uk7HMg&list=PL0bgkxUS9EaKyOugEDffRzsvupBE2YEoD&index=5&ab_channel=BrentRoose) to exist that compiles to normal PHP, and while compiling doing lots of type checks and other cool stuff. TypeScript is immensely popular, and I think that if there’s room for a similar approach in serverside languages, PHP is probably a good candidate. However, TypeScript didn’t just magically appear overnight. It was created by experienced language designers, it’s magnitudes larger than adding runtime-ignored generics in PHP. But who knows, maybe one day.

With all of that being said, I hope that you found this series useful and educational; I said everything I wanted to about generics. I’d appreciate it if you shared this series with your colleagues and followers — I believe it’s an important topic and I want to see things change.

I rest my case.

{{ cta:mail }}
