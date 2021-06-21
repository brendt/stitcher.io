I once worked at a company that wrote and maintained an in-house framework. They probably made around 250 websites and applications with it over the course of ten years.
Despite many shortcomings, the company kept using it for a simple reason: they were in control.

Thanks to that control, they were able to tailor to their own needs without any overhead. While I would argue that using popular, community-backed frameworks instead of writing your own is almost always the better choice, I can appreciate some of the reasoning this company made.

Except, in the end, that company utterly failed in creating what they set out to do. Instead a tool shaped specifically for their needs, the core developers often wanted flexibility: they dreamt of open sourcing their framework and growing popular, so it needed to handle as many cases as possible. And thus, flexibility and configuration were often prioritized, even though they rarely added much — if any — value to company projects. The core developers were always able to convince the not-so-technical managers. But in reality, the design and flexibility of this framework was often just a burden for me and my colleagues to deal with.

Problems like premature optimizations and over-abstractions are unfortunately common in software design. I'm not sure why, but somehow programmers often tend to think they need to account for every possible outcome, even when those outcomes aren't relevant to their use cases. We're afraid of losing our audience because our solutions can't handle the most specific of specific edge cases — I've personally been there more than once.

In trying to create a solution that works for the 10%, we've made it worse for the other 90%.  

---

Don't take my word for it though, instead look at the greatest software architects that came before us: https://www.youtube.com/watch?v=Udi0rk3jZYM

**You're in charge**

opinion-driven
doesn't mean not doing any market research
opinions can evolve
the burden of legacy support
stability vs progression
determination to evolve
confidence
opinionated vs configurable
pleasing everyone
users don't want choice > can be confusing

problem - solution - witness
quote - problem - solution
story - problem - solution (statik?)

"by trying to solve as many problems as possible, it actually failed in solving any of them properly"

"do programmers actually want their framework to give them a choice?"

"do programmers really want a framework that gives them 3 or 4 or 5 ways to do the same thing?"

> Example from Laravel

Acknowledge that popular frameworks do have a legacy burden to carry, but breaking changes can be allowed
