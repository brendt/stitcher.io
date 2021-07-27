Do you want to make a guess about when I last encountered a `<hljs type>TypeError</hljs>` in one of my projects? To be honest, I can't remember, so it's probably a few years. Coincidentally, I also started relying on static analysis around the same time.

I'm fairly certain that I could disable PHP's runtime type checking altogether — if that was a thing — and have a perfectly working codebase. 

Because, here's the thing about runtime type checks: they are a debugging device, not so much a safety net. Runtime type errors make it easier to detect and fix bugs, but the reality is that whenever a type error is trigged, our code still crashed at runtime. When type errors occur in production, the end result is the program crashing, nothing you can do about it.

Now, I've written about type systems before ([here](/blog/tests-and-types), [here](/blog/liskov-and-type-safety) and [here](/blog/the-case-for-transpiled-generics)), so if you want more background information about them be sure to do some followup reading. Today, I want to discuss how static analysis has the power to revolutionize the way we write PHP code much more than it already does today, and how it can open doors to many new possibilities.

The tradeoff? We need a community-wide mind shift: there are still many PHP developers (including internal developers) who are taken aback by the idea of static type checking. My only goal today is to encourage you to think outside your box, to imagine what would be possible if PHP shifted towards a built-in, statically type-checked model.

Whether you're into static type systems or not, I promise it'll be interesting nevertheless. Let's dive in!

---

A while back, it became clear that generics in PHP are probably [not coming any time soon](*https://github.com/PHPGenerics/php-generics-rfc/issues/45). One of the main reasons being that there are two ways to implement them, and both have significant problems. Either there's too large an impact on runtime performance, or the implementation is just way too complex to get right.

Both approaches did assume a runtime type-checked implementation though. So I shared a thought experiment with internals: what if we only need to support the syntax for generics, and have static analysers do all the checks? I called them [transpiled generics](/blog/the-case-for-transpiled-generics) back then, but _runtime-erased_ or _runtime-ignored_ generics is probably a better name.

My thinking was that adding support for generic syntax shouldn't be all that hard, and without runtime type checks, there shouldn't be any performance impact. It makes sense if you think about it from a static analysis point of view: your code has already been analysed, and it's been proven to work correctly, so there's no more need for runtime type checks. On top of that, most developers only want generics as a way to get better code insights _while_ coding; does the "I want to know what items are in an array" argument ring a bell?

Of course there could still be _some_ type information exposed at runtime via reflection, but we can't deny that having types ignored at runtime is a major paradigm shift that most PHP developers aren't used to. Here's [Sara's response](*https://www.reddit.com/r/PHP/comments/iuhtgd/ive_proposed_an_approach_to_generics_on_internals/g5pgkbn/) on my runtime-ignored generics idea (which Hack already does) and how it requires a mind-shift:

> Oh, I agree that there's real value in HackLang's approach. It's just that there is a mountain of inertia around "The PHP Way" and it's going to take an equal and opposite mountain to alter course.
> Entirely possible, even probable, but we won't see that level of shift in the next five years.

It'll take a few more years, but it's probably the way PHP _will_ evolve anyway, according to Sara. The reason it works for Hack, by the way, is because the static analysis step is _required_ by Hack at server startup. You cannot "forget" to run an external tool and end up with buggy code.

That seems to be the biggest problem people have with static analysis in PHP today: it's opt-in. 

And now, before I get an angry mob chasing me: I'm not suggesting we bundle a static analyser in PHP that you're required to run (that would mean a "compilation" step in practice). What I _am_ suggesting is that we can disable PHP's runtime type checks if we want to, and deal with the consequences ourselves. If you want to use generics in such a scenario then, yes, you'll have to use a static analyser. If you don't want to, that's fine, but you won't be able to use generics.

Of course, in an ideal world, PHP would ship with such a built-in, opt-in static analyser; instead of users having to rely on third party tools. Because the main problem with third party tools is consistency between them. Case in point: PhpStorm will support a basic form a generic-type doc blocks in their [next release](*https://blog.jetbrains.com/phpstorm/2021/07/phpstorm-2021-2-beta/), years after Psalm and PHPStan added support for them.

If there was an official spec supported by internals, static analysis vendors wouldn't have any choice but to follow that spec. That's the major problem with doc block type checks at the moment: there are no rules, so every vendor does whatever they want.

The idea of a centralised static analyser isn't new, by the way, but you can imagine it's a massive undertaking to get right. Here's [Rasmus](*https://externals.io/message/101477#101592) on the matter a few years ago:

> Now if the RFC was a plan for baking a compile-time static analysis engine
> into PHP itself, that would be interesting. But that is a massive project.

{{ cta:mail }}

When I asked Nikita about the idea of runtime-ignored types and generics, he described the main problem with generics that _have_ a runtime implementation [like so](*https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/):

> Complexity is a pretty big problem for us, and I think severely underestimated by non-contributors. Feature additions that seem simple on the surface tend to interact with other existing features in ways that balloon the complexity.

He also called runtime-erased generics "the cowards way out". The reason Nikita says that is because, if runtime-erased generics were supported, it would mean there's a huge inconsistency within PHP's type system where some parts are checked at runtime, and other parts are checked statically.

So to be clear: I don't think runtime-erased or runtime-ignored generics are where we should start. We first need PHP without runtime type checks whatsoever, and then we can think about building on top of that.

I asked Nikita if he thought such a version of PHP would have merit, he [said this](*https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g83vvav/):

> I think that would be a good thing... but then again, lots of things would be different in PHP if we'd do a clean-slate redesign now. We have to work within the constraints we have, somehow.

From a userland-developer point of view, I think we _can_ work with the given constraints, as long as there's a large enough user base supporting these ideas.

---

So what if internals doesn't think of it as achievable to optionally step away from runtime type checks? Or what if such a mind shift won't happen within the next decade?

Well, there _is_ another approach, one that actually has been tried and proven before in another language: TypeScript. The power and immense popularity of TypeScript comes from its static type checker. Sure, there's an extra compilation step to transform TypeScript code to regular JavaScript, but developers seem to manage that just fine — because they know how much they gain from proper static analysis.

The same has been tried in PHP before, by the way: there was Hack that at one point _did_ compile to PHP, and there was [preprocess](*https://preprocess.io/#/), a project by [Christopher Pitt](*https://twitter.com/assertchris). Unfortunately, Hack took another direction, and preprocess halted; not because of implementation problems, but because of lack of support in IDEs and the wider community. 

_If_ transpiling PHP gains more traction again, it'll definitely need proper IDE support if we ever want a chance for it to succeed. That's the benefit of an internals-backed implementation: when it's in PHP core, IDEs and other external tooling can't do anything but to follow along. A community-driven transpiler wouldn't have that benefit.

---

So, this is where we are today:

- PHP's runtime type checker is reaching its limitations (generics being the most obvious example)
- There _are_ already runtime-ignored types (doc blocks), but there's no consensus on syntax and usage across static analysis communities
- Runtime-ignored types require a mind-shift that many developers find difficult at this point
- Transpiling PHP is possible, it's been done before, but it's a massive undertaking an likely to fail again if tried without proper support

I see much more potential for static analysis. It has made my code more stable and easier to write, and I couldn't do without it anymore. On the other hand, the community and toolset has still a long way to go, and we're all playing a part in that journey. 

I'd love for internals to further explore the static analysis side of PHP: it's more than just a userland addon to the language, and will only continue to grow tighter to PHP in the future. 

I'd want to see these changes to the language today, though I know that's an unrealistic expectation. I hope Sara's assessment is right in that this _is_ the form PHP is evolving to, but unfortunately it'll take a few more years to get there. This blog post is just an attempt to give one more little push in the right direction.

What's your opinion? [Let me know](*https://twitter.com/brendt_gd).

{{ cta:like }}

{{ cta:mail }}

## Sidenotes

I'll probably add some more information to this section when people read this post and share their feedback, though I could already think of a couple of things.

### Psalm + Rector as a "transpiler" ?

Someone mentioned the idea about using [PHP without runtime type checks](*https://www.youtube.com/watch?v=N2PENQpQVjQ&t=2454s) by using a combination of [Psalm](*https://psalm.dev/) and [Rector](*https://getrector.org/): Psalm first analysed the codebase, and Rector removed all type hints afterwards to generate a "compiled" production build. 

It's an interesting step to further explore the problem space, and while it doesn't mean there's support for custom syntax, there might be potential in the Psalm + Rector combo.

### Why not use … ?

The classic question that gets asked by skeptics: why not use Java or C# or whatever other language if you want to rely on static analysis so much?

Well, the answer is simple: the ecosystem.

### What about the FIG?

The main problem with doc block types is one of consistency, which maybe the FIG could solve?

I'm not sure about the relevance of the FIG these days: I can't imagine the FIG having an influence over the development of, for example, PhpStorm; and there are very little significant frameworks still following PSRs to the rule. 

So, yes, maybe? I'd love to be proven wrong. 

{{ cta:mail }}
