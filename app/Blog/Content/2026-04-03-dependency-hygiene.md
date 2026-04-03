---
title: Dependency Hygiene
---

I was reading this blog post about how [package managers are evil](https://stitcher.io/feed/posts/74429). I don't really agree on the "evil" part because package managers do solve a problem that would be very hard to deal with manually. That being said, I was curious enough to do a little experiment.

I recently came across a project that required `paragonie/sodium_compat`. Fair enough: it adds support for sodium functions before they came built-in with PHP 7.2. However… this particular project required PHP 7.3 at the minimum! So even though `paragonie/sodium_compat` got included in every installation, it wasn't actually useful anymore. So [I made an issue](https://github.com/pusher/pusher-http-php/issues/400) asking them about it, and eventually they did remove it.

An unused dependency in itself isn't a problem, but with all the recent [NPM shenanigans](https://cloud.google.com/blog/topics/threat-intelligence/north-korea-threat-actor-targets-axios-npm-package), it made me wonder: how conscious are we of the code we depend on? Do we blindly trust everything we pull in?

So I wrote a script to scan dependants of popular polyfill and compatibility packages: 

- `paragonie/sodium_compat`
- `paragonie/random_compat`
- `symfony/polyfill-php70`
- `symfony/polyfill-php70`
- `symfony/polyfill-php71`
- `symfony/polyfill-php72`
- `symfony/polyfill-php73`
- `symfony/polyfill-php80`
- `symfony/polyfill-php81`
- `symfony/polyfill-php82`
- `symfony/polyfill-php83`
- `symfony/polyfill-php84`

Whenever a project required one of these packages without actually needing it (listed as a "dependant" by Packagist and based on the PHP requirements), I made a note.

**Out of 1554 projects scanned, 229 had an unnecessary dependency.** That's around 15%. I did make the effort to send all of them a PR to fix it.

Of course, this is only a sample of the full PHP ecosystem. But it did seem significant enough a number to question how much effort we actually put in vetting the code we rely on? I don't think package managers are evil, although they might make us a bit… lazy? Maybe we should spend a little more time and effort into understand the code we depend on?

Another great example is `symfony/polyfill-mbstring`: it comes with virtually every PHP project I work on. I don't really know where it comes from without running `composer why` (it's a really useful command, by the way) — I've just come to accept it: if you're doing anything PHP related, chances are `symfony/polyfill-mbstring` will be in there somewhere. I couldn't include it in my scanner script, because `symfony/polyfill-mbstring` replaces an _optional_ PHP extension and isn't tied to a fixed PHP version (although to be honest, `mbstring` is always installed by default on my environments at least).

Anyway, there's no way for me to opt-out of pulling in this package. There are so many other packages out there depending on something that depends on something that depends on `symfony/polyfill-mbstring`, that makes it vitually impossible to not require it. Even though I have the `mbstring` extension in all my PHP installations!

Is there anything wrong with pulling in that package? Not really. Unless, of course, one day, a supply-chain attack happens like we've seen more than once in the NPM ecosystem lately. It _is_ convenient to have the polyfill in place in case someone somewhere needs it. But is that really good enough a reason? 

Maybe we do need to practice a little more dependency hygiene? Let me know your thoughts in [the comments](#comments) or [on Discord](/discord)!

