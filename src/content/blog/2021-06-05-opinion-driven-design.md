
I once worked at a company that wrote and maintained an in-house framework. Over the course of ten years, they probably
made around 250 websites and applications with it. Despite many shortcomings, the company kept using their framework for
a simple reason: they were in control.

Thanks to that control, they were able to tailor their framework to their own needs without any overhead. And, while I
would argue that using popular, community-backed frameworks is almost always the better
choice over writing your own , I can appreciate some of their reasoning.

{{ cta:dynamic }}

Except, in the end, they utterly failed in creating what they originally set out to do. Instead of a tool shaped
specifically for their needs, the framework's core developers often wanted flexibility: they dreamt of open sourcing
their framework and it growing popular, so it needed to handle as many cases as possible to reach as wide an audience as
possible. And thus, flexibility and configuration were often prioritized, even though they rarely added much — if any —
value to company projects. The core developers were always able to convince the not-so-technical managers; while in
reality, the design and flexibility of this framework was often just a burden for me and my colleagues to deal with.

This mindset of "high configurability and flexibility" is, unfortunately, common in software design. I'm not sure
why, but somehow programmers — myself included — often think they need to account for every possible outcome, even when those
outcomes aren't relevant to their use cases. Many of us deal with some kind of fear of losing our audience if the code we're writing isn't able to handle the most specific of specific edge cases. A very counter-productive thought.

---

Lately I've come to appreciate an opinion-driven approach to software design. Especially in the [open source](*https://spatie.be/open-source?search=&sort=-downloads) world, where you're writing code for others to use. I used to tell myself I'd need to write more code for more flexibility "if I want this package to grow popular".

I don't believe that anymore.

These days, I prefer one way of solving a problem, instead of offering several options. As an open source maintainer, I realise that not everyone might like the solutions I come up with as much as I do; but in the end, if the job gets done, if my code is reliable, clear and useful; there rarely are any complaints. So I started to prefer opinion-driven design when I realised that flexibility comes with a price that is often not worth paying.

I'm not the only one benefiting by the way. When users of my open source code only get one way of doing something, they
don't have to be worried about micro-decisions that wouldn't affect the end result. And that, for me, is good software
design: allowing programmers to focus on decisions that really matter and offer value to their projects and clients;
instead of wasting time on unnecessary details.

{{ cta:like }}

{{ cta:diary }}
