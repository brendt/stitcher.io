To break, or not to break, that is — at least — _a_ question.

Today I want to share some thoughts on breaking changes. And just a little note up front, I also made a video about this topic, you can [check it out](https://www.youtube.com/watch?v=dzf0Du1W4DQ) if you'd like to watch it. 

Recently people have been growing more and more wary of the upcoming [deprecated dynamic properties in PHP 8.2](https://stitcher.io/blog/deprecated-dynamic-properties-in-php-82). It's an RFC that splits the PHP community. One group says that dynamic properties are bad and should never be used anyway. Another group prefers not to be bothered with dealing with deprecations — if a feature doesn't do any harm, why deprecate it?

Now, the original goal of [the RFC](https://wiki.php.net/rfc/deprecate_dynamic_properties) was this:

> To prevent mistakes (due to typos or renames) in the common case, and to make intentional uses explicit

I mean, I don't oppose those goals, but I also get that some people simply don't think it's worth introducing a breaking change to achieve them.

I'll admit that [Taylor's opinion on Twitter](https://twitter.com/taylorotwell/status/1564642802230853636) stuck with me for a day or two:

> Today's take: programming languages should never have breaking changes. 🤗
> 
> User land packages / software should trend towards less breaking changes over time - eventually approaching zero. 📉

I assume Taylor is taking a more extreme stance here, in part because he wants to challenge people to think about it. I don't blame him, I've done the same in the past. 

So here goes, I thought about it: I definitely don't agree with the opinion in general. If we were never allowed to introduce breaking changes — in a programming language or framework all the same — we would end up with one of three things:

- a terribly outdated language, because we'd soon run into situations where a breaking change is needed, for example, to add new syntax; or
- a terribly slow language, because of all the workarounds to keep backwards compatibility; or
- a terribly insecure language, because we wouldn't be able to deprecate insecure parts (like PHP did with mcrypt a couple of years ago).

So… I _get it_, I also wouldn't want to deal with boring stuff like deprecations, but I also feel it's just… part of the job? Not everything about coding can be fun all the time. A language _needs_ to evolve and that sometimes means saying goodbye to some of its parts.  

_But_… deprecated dynamic properties is a tricky one, because there aren't many tangible gains by deprecating them. Sure they push users towards a stricter language, but besides that? For example: I wasn't able to find any reference to how removing dynamic properties increase performance or make the engine code easier to maintain. I could have missed something though, so I'd love you to reply if you know something more on the matter.

If I had to summarise my opinion on this particular deprecation, I'd say something like this:

- I personally don't mind dynamic properties being deprecated; I _like_ a stricter language with less room for magic.
- I don't think deprecated dynamic properties mean the end of the world. Dealing with these kinds of changes is part of the job.
- However, dynamic properties aren't something I would be disappointed about if they _stayed_ in the language. It's easy to detect them with static analysers. So you can already enforce a "no dynamic properties" rule on the project level, without the language to interfere. So the question of whether it's worth a breaking change in this particular case — I would say — is worth asking.

Anyway, that's my opinion, but what about _you_? Do you want to share your thoughts? Will your projects be affected by deprecated dynamic properties? Hit reply and let me know!

## Vlog and blog

I haven't published anything new the past weeks, except for the [vlog about breaking changes](https://www.youtube.com/watch?v=dzf0Du1W4DQ). I do have one more video scheduled for tomorrow though: "PHP in 60 seconds" — I'm looking forward reading your comments on it as well! 

Hint: you can [subscribe here](https://www.youtube.com/user/BrenDtRoose) if you want to be the first one to get it ;)

## Roundup

I'd like to leave a quick word of thanks to everyone who replied to my previous email where I shared interesting links from across the web. The overall perception was very positive. I will share more of _my_ opinion on why I added particular links in the future.

I still don't know what frequency I want to share those roundup links with you, so I'll leave them out in this edition, and maybe do another one later this month.

The only thing left to say is that I wish you a great week, I am looking forward to reading your replies, and see you next time.

Until then!

Brent
