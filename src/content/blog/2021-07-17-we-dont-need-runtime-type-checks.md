Do you want to make a guess about when I last encountered a `<hljs type>TypeError</hljs>` in one of my projects? To be honest, I can't remember, so it's probably a few years. Coincidentally, I also started relying on static analysis around the same time.

I'm fairly certain that I could disable PHP's runtime type checking altogether — if that was a thing — and have a perfectly working codebase. 

Because, here's the thing about runtime type checks: they are a debugging device, not a safety net. Runtime type errors make it easier for us to detect and fix bugs, but the reality is that whenever a type error is trigged, our code still crashed at runtime. If a type error occurs in production, the end result is the program crashing, nothing you can do about it.

Now, I've written about type systems before ([here](/blog/tests-and-types), [here](/blog/liskov-and-type-safety) and [here](/blog/the-case-for-transpiled-generics)), so if you want more background information about them be sure to do some followup reading. Today, I want to show how static analysis has the power to revolutionise the way we write PHP code much more than it already does today and how it can open doors to many new possibilities.

The tradeoff? We need a community-wide mind shift: there are still many PHP developers (including internal developers) who are taken aback by the idea of static type checking. I believe the reason for that has more to do with the lack of practical experience with static analysis instead of problems with static analysis itself.

My only goal today is to encourage you to think outside your box, to imagine what would be possible if PHP shifted towards a statically type-checked model.

---

A while back, it became clear that generics in PHP are probably [not coming any time soon](*https://github.com/PHPGenerics/php-generics-rfc/issues/45), the reason being that the possible ways to implement them either have too large an impact on runtime performance, or that the implementation is just way too complex to get right.

Both approaches to generics did assume a runtime type-checker implementation though. So I shared a thought experiment with internals: what if we only need to support the syntax for generics, and have static analysers do all the checks? I called them [transpiled generics](/blog/the-case-for-transpiled-generics) back then, but "runtime-erased generics" is probably a better term.

Adding support for generic syntax shouldn't be all that hard, and without runtime type checks, there shouldn't be any performance impact. It makes sense if you think about it from a static analysis point of view: your code has already been analysed, and it's been proven to work correctly, so there's no more need for runtime type checks. On top of that, most developers only want generics as a way to get better code insights _while_ coding. Does the "I want to know what items are in an array" argument ring a bell?

Of course, not having runtime type checks is a major paradigm shift that most PHP developers aren't used to. Here's [Sara's response](*https://www.reddit.com/r/PHP/comments/iuhtgd/ive_proposed_an_approach_to_generics_on_internals/g5pgkbn/) on my "transpiled generics" idea (Hack already does this):

> Oh, I agree that there's real value in HackLang's approach. It's just that there is a mountain of inertia around "The PHP Way" and it's going to take an equal and opposite mountain to alter course.
> Entirely possible, even probable, but we won't see that level of shift in the next five years.

It'll take a few more years, but it's probably the way PHP will evolve anyway, according to Sara. The reason it works for Hack, by the way, is because the static analysis step is _required_ with Hack at server startup. You cannot "forget" to run an external tool and end up with buggy code.

That seems to be the biggest problem people have with static analysis: it's opt-in. 

And now, before I get an angry mob chasing me: I'm not suggesting we build in a required static analyser in PHP (which would mean a "compilation" step in practice). What I _am_ suggesting is that we can disable PHP's runtime type checks if we want to, and deal with the consequences ourselves. If you want to use generics then, yes, you'll have to use a static analyser. If you don't want to then, fine, you won't be able to use generics.

Of course, in an ideal world, PHP would ship with such a built-in, opt-in static analyser; instead of users having to rely on third party tools. That idea isn't new, by the way, but you can imagine it's a massive undertaking to get right. Here's [Rasmus](*https://externals.io/message/101477#101592) on the matter a few years ago:

> Now if the RFC was a plan for baking a compile-time static analysis engine
> into PHP itself, that would be interesting. But that is a massive project.

So in my opinion, we could start with using third party tools, and work our way towards a built-in engine over the years to come, that shouldn't be a blocker.

---

When I picked [Nikita's brain](*https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/) a little more on the idea, he described the main problem with runtime generics as so:

> Complexity is a pretty big problem for us, and I think severely underestimated by non-contributors. Feature additions that seem simple on the surface tend to interact with other existing features in ways that balloon the complexity.

He also called runtime-erased generics, quote: "the cowards way out". The reason Nikita says that is because, if runtime-erased generics were supported, it would mean there's a huge inconsistency within PHP's type system: parts are checked at runtime, and other parts are checked statically.

So to be clear: I don't think runtime-erased generics are the way to go. We first need PHP without runtime type checks whatsoever, and then we can think about building on top of that.

When I asked Nikita about his opinion if he thought such a version of PHP would have merit, he said this:

> No, I think that would be a good thing... but then again, lots of things would be different in PHP if we'd do a clean-slate redesign now. We have to work within the constraints we have, somehow.

From a userland-developer point of view, I think we _can_ work with the given constraints, as long as there's a large enough user base supporting these ideas.

---

So what if internals don't think of it as achievable to optionally step away from PHP's runtime type system? And what if such a mind shift won't happen within the next decade?

Well, there _is_ another approach, one that actually has been tried and proven before. Do you know of TypeScript? The power and immense popularity of TS comes from its static type checker. Sure, there's an extra compilation step to transform TS to regular JavaScript, but developers seem to manage that just fine — because they know how much they gain from proper static analysis.

The same has been tried in PHP before, by the way: there was the [preprocess](*https://preprocess.io/#/) project by [Christopher Pitt](*https://twitter.com/assertchris). Unfortunately, the project halted, not because of implementation problems, but because of lack of support in IDEs and the wider community. 

_If_ transpiling PHP was the way to go, it'll definitely need proper IDE support if it'll ever wants a chance to succeed. That's the benefit of an internals-backed implementation: if it's in PHP core, IDE's and other external tooling can't do anything but to follow along. A community-driven transpiler wouldn't have that benefit.

That brings me to a very last point that some of you were probably thinking when reading this post: "we already have runtime-erased types! That's what doc blocks are used for". That's exactly how Psalm and PhpStan already work today: by adding custom doc block type annotations. The difference though, is that there's no consensus 


Downsides:
    - No more runtime type casting, that's a price I'm fine paying
