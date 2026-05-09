---
title: Dependency Hygiene
---

_Edit: this blog post sparked a lot of interesting discussions. I first wrote a separate followup blog post, but I wanted to bundle everything in one place. So I added a section to this blog post instead, addressing some of the arguments that were made across the internet._

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

Anyway, there's one way to opt-out of pulling in these packages: by adding a [`replace`](https://getcomposer.org/doc/04-schema.md#replace) config in your project's composer.json:

```json
{
    "replace": {
        "symfony/polyfill-php54": "*",
        "paragonie/sodium_compat": "*",
        "symfony/polyfill-mbstring": "*"
    }
}
```

In indicates to composer that the current project _replaces_ these packages, thus they won't be pulled in. It's a bit convoluted, and most projects likely don't bother to set it up. Without doing the replace trick in every one of my projects, chances are very likely I end up with them: there are so many packages out there depending on something that depends on something that depends on `symfony/polyfill-mbstring`, which makes it vitually impossible to not require it.

Is there anything wrong with pulling in these packages? Not really. Unless, of course, one day, a supply-chain attack happens like we've seen more than once in the NPM ecosystem lately. It _is_ convenient to have the polyfill in place in case someone somewhere needs it. But is that really good enough a reason? 

Maybe we do need to practice a little more dependency hygiene? Let me know your thoughts in [the comments](#comments) or [on Discord](/discord)!

## Followup

Since publishing this blog post, many people have pitched in with their feedback, which was really valuable. I want to address these point here as well.

### On sodium specifically

First, most discussion turned out to be about `paragonie/sodium-compat` specifically. It's kind of unfortunate that this discussion overtook the point I was trying to get across, which was for a big part my fault. What I missed intially is that `ext-sodium` is indeed a bundled extension since PHP 7.2, but not enabled by default on Linux and Windows. Why that's the case, I'm not sure.

So while the argument I made for dependency hygiene still holds, for `paragonie/sodium-compat` it's a bit more tricky. Personally, as an open source maintainer, I would still think it's better to let users decide on the project/system level whether they want to rely on `ext-sodium` or  `paragonie/sodium-compat`, without a third-party library forcing me to download an external package. The `replace` trick works, but there's also no mechanism forcing you to do so. In practice, I doubt many projects actually go through the effort of doing so.

### On composer opportunities

Many people pitched the idea for an addition to the composer.json scheme, where a package itself could declare itself redundant if some system-requirements are met. For example, it could look something like this:

```json
{
    "name": "paragonie/sodium_compat",
    {:hl-comment:# …:}
    "replaced-by": {
        "ext-sodium": "*",
        "php": "^7.2"
    }
}
```

To be clear: this is not available in composer right now. I think it's an interesting idea, but I reckon there likely are many reasons why it's not viable. I'm going to ask Jordi about it, though.

### On package hygiene

My main worry, which I tried to convey with this blog post, is redundant code ending up on millions of systems that don't really need it (`paragonie/sodium-compat` has ±140k daily installs). Today there's no problem with that, just like there was no problem with any of the NPM packages that fell to the recent side-chain attacks. I really don't think we should act lightly about it, and I think it's worth having a discussion about it.

In my opinion, the problem with these widespread packages has been exacerbated because libraries require them without a second thought. These libraries, in turn, are then required by so many projects, causing a dependency of a dependency to end up in places where it shouldn't be. That's why I think part of "good dependency hygiene" is to not hard-require polyfills and compat packages on the library-level, and instead let the user decide on the best approach on the project or system level.  