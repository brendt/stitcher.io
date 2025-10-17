---
title: 'Extend or implement'
disableAds: true
---

I'm probably part of a small group that actually cares about this: the difference between extending an abstract class and implementing an interface (ideally with a trait to provide part of the default implementation). I don't expect many people to follow my reasoning, but hey, it's my blog, I can write whatever thoughts are coming to my mind 😅 

I also made a video about it, by the way:

<iframe width="560" height="345" src="https://www.youtube.com/embed/HK9W5A-Doxc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

---

I've been wrestling with code that _abuses inheritance_ for a couple of years now. Granted: I don't really like the word _abuse_ because it sounds very heavy, and code that _abuses_ inheritance can still work perfectly well. But I also struggle to find a better word for it. Maybe "code that doesn't fit _my_ expectations of what inheritance should be used for". I'm frustrated with myself because I can't seem to put into words exactly _why_ I dislike code that's intentionally designed to be extended. It happens a lot in Laravel, but I'm sure it happens in Symfony and other places as well. 

"Extend from a base class", "override a method" — all in the name of "flexibility" and "configurability". It's a common pattern, but it feels so… icky to me. The practice is so common that a whole community of developers is vocally against using `{php}final`, because it blocks them from accessing that flexibility inheritance is giving them.

All the while, I'm thinking "this isn't the way inheritance was meant to be used…"

Like I said, it's difficult to put into words, but I think the problem for me started when I watched [Alan Kay's talk about his vision for OOP](https://youtu.be/oKg1hTOQXoY?si=wAIxjBuzmwWiR6Ml&t=811). Alan is the inventor of object-oriented programming, by the way. He describes how you can build a dog house using only a hammer, nails, planks, and just a little bit of skill. Once you've built it, you've earned the skills and know-how, and you can apply that knowledge to other projects. Next, you want to build a cathedral, using the same approach with your hammer, nails, and planks. It's a 100 times larger, but you've done this before — right? It'll only take a little longer.

This is where Alan says OOP started to derail: languages like C++ took a relatively simple concept, and started to do a bunch of things with it in a way it wasn't supposed to be used. Granted, the code works, but it's lacking in many areas. Honestly, it's a fascinating talk and I highly recommend watching it.

Back to modern OOP: we've taken a concept like inheritance, and made it almost equivalent to "an easy way of configuring and plugging into vendor code". Open source packages are designed deliberately to extend and overwrite different parts of their codebase, hoping all goes well. The sad part, to me, is that there are better solutions to solve these problems. Other ways than to rely on inheritance. There's a range of patterns that help us solve whatever issues we run into — including configuration and "plugability" of code that's out of our control. Alan talks about this as well, by the way: proper architecture is a thing.

I was thinking about inheritance, comparing it to interfaces, and I came up with a definition that somewhat makes sense to me: **I want to use inheritance to reflect real-world relations; I want to use interfaces to describe technical behaviour**. 

A class that implements an interface doesn't necessarily describe the essence of that class, it only promises it can perform the tasks defined by that interface. It could do a lot more things, for all I know, but I don't care about that within a specific context. It's a "hey, I can do this" promise rather than "hey, I _am_ this". Inheritance, on the other hand, conveys that a class is a _subcategory_ of something else. To me, that kind of relation only makes sense when we're talking about real-world modelling. Or maybe it's the other way around: if we try to apply that kind of relation to technical properties, things get out of hand fairly quickly. 

An example: `{php}User extends Model`, for me, that's wrong: a `{php}User` isn't a `{php}Model`; instead it's a class that can act like a model. It's a class that makes a promise to whatever context it's being used in. `{php}AdminUser`, on the other hand, should extend from `{php}User`, because it reflects a real-world relationship: an `{php}AdminUser` is everything a `{php}User` is, and probably a bit more. So, in my mind it should be `{php}AdminUser extends User implements Model` (although the `{php}implements Model` already happens on the parent class and isn't necessary here).

Like I said, I suspect not many people caring so deeply about this, and I'm not sure why _I_ care so deeply about it myself. I think this definition keeps things somewhat clear to me. It eliminates a gray zone that I find confusing to work in. 

And sure, there are some technical arguments to make against my thought experiment within PHP: there's no multiple inheritance, there are no interface default methods, and you can't implement an interface via a trait. There are limitations to the language we're using. 

But still, I find this mental model more comfortable than the alternative. 