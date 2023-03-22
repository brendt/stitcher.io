Camels are weird. Granted, they _do_ manage to survive in some of the harshest environments on our planet, so, obviously, they are doing something right. But, I mean, look at them:

![](/resources/img/blog/committee/camel3.jpg)

It looks like someone combined a horse with a giraffe, and kept adding stuff until they met the requirements of "being able to survive in the desert". Who came up with that?  Don't get me wrong: camels _do_ work, so something _must_ be right. But honestly, the finishing touches are far from ideal:

![](/resources/img/blog/committee/camel4.jpg)

PHP is like a camel: there's little to say when it comes to beauty or elegance, but it does seem to survive many harsh conditions. So, who am I to judge? Coincidentally, PHP was originally inspired by PERL, whose logo is a camel; so maybe there _is_ a deeper connection even still? 

But let's not go too deep down the <span style="text-decoration:line-through">camel</span>&thinsp;rabbit hole; instead, let's talk about how programming languages are designed.

PHP is one of those programming languages that's not backed by a big company or a group of people whose job it is to work on it. PHP is mostly driven by a group of volunteers. A year ago, we got the [PHP Foundation](https://thephp.foundation/) which employs a handful of developers to work on the language, but it's not like they have any ownership. They have to submit their ideas, and are at the mercy of "PHP Internals" to decide what goes in the language, and what not.

So about that _group of volunteers_: PHP Internals are a group of people who discuss and vote on what gets added into the language. Part of that group consists of core developers, and then there are some prominent PHP community members, release managers, past contributors, docs maintainers, and probably a bunch of other people. We're talking somewhere around 200 members — probably, but there aren't any public numbers, as far as I know.

That group is the perfect example of a committee: they all come together to decide on how PHP should evolve next. They don't have a unified plan or vision, but all of them bring their own agenda. While a core programmer might favor cleaning up PHP's internal code (even when it brings some breaking changes), a prominent community member might push forward RFCs (request for comments) that help with the development of a PHP framework or package.

This phenomenon — designing a language with such a large group — is called "[design by committee](https://en.wikipedia.org/wiki/Design_by_committee)". At first glance, it might sound like a very democratic approach to software design where everyone can pitch an idea, and if enough people vote for it, it gets added. However, because of its nature, a committee rarely achieves excellence.

Say you want to add a feature in PHP that benefits you — a prominent framework developer. Then you'll have to convince enough people to vote for it. First, you request Internals to share their comments on your idea — an RFC. Naturally, you'll make compromises, trying to get as many people on your side as possible before voting starts. On top of that: people can lobby others to influence their vote and, in the end, the result. Suddenly you're playing a game of politics, instead of proper software design. 

And so, design by committee often leads to average results at best; or (in many cases) no result, because people couldn't find consensus. Camels were probably designed by a committee as well. Sure, they work, but they are far from excellence and full of compromise.

![](/resources/img/blog/committee/camel5.jpg)

The alternative to design by committee is called "design by dictator" or having a "benevolent dictator for life". It might sound rather negative at first, but hear me out. A benevolent dictator (or group of benevolent dictators) don't want to push their own agenda; they want the best for their product. It's _their_ product — they have full ownership. And exactly because it is _their_ product, they _will_ listen to "the masses". Because, their product is nothing, without their people.

In the end though, a dictator _will_ make decisions, even when those decisions aren't agreed upon within the community as a whole. There's much less need for compromise, and so, more room for excellence.

Maybe you are skeptical of the idea? Well, don't take my word for it, there are quite a lot of open source projects applying this technique. You might even recognise a couple:

- Rust
- Laravel
- Ruby
- Linux
- Ruby on Rails
- Ubuntu
- Vue.js
- [And more](https://en.wikipedia.org/wiki/Benevolent_dictator_for_life)

I think it's fair to say that all of these projects are a huge success. I believe much of that has to do because there's one person in charge, leading the way. They still listen to their audience, and they often allow for voting on features. But, in the end, there's one person having the ownership. There's one leader to look up to. 

{{ ad:carbon }}

When people trust a benevolent dictator, much more becomes possible than when people settle with a committee.

PHP will never change its leadership model; the committee would have to give up its power which, of course, they don't want to. I think this is one of the reasons I'm excited about the idea of a superset for PHP: it could be an independent project, led by one person or a company, regardless of what the committee wants. If that person or company gained my trust, I would be fine with them make the decisions to move the language forward, rather than settling with average.