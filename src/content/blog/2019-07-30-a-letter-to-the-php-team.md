To whomever contributes to ((PHP)), from a userland developer.

Let me start by thanking those who actively work on the ((PHP)) project. Those who contribute to the core, extensions, maintain the docs or vote on ((RFC))s: thank you for a language that I can use every day both in my professional and personal life. ((PHP)) has been a very useful tool to me for many years, and it's good to see lots of contributors help making it better every day.

I also want to mention that I, as everyone, am subject to confirmation bias. When I address one or two thoughts in this letter, I'll try my best to be as objective as possible, though I realise I'm looking through my lens, and not someone else's. 

Since the goal of this letter is to start a conversation, I'm open to hear your thoughts, also if they don't align to mine; please feel free to disagree.

---

I could continue by listing lots of good things — there are many. Though because I want to keep this letter on topic, I won't be doing that. Don't take this as me being a disgruntled developer, I simply want to be efficient in conveying what I want to say.

I want to write about how ((PHP)) is shaped and developed these days. I feel that I, as a userland developer, know a thing or two about using ((PHP)) in real projects. I believe I have an informed and relevant opinion on the matter.

Recently we've seen discussion regarding the ((RFC)) voting process. Besides [recent](*https://wiki.php.net/rfc/abolish-narrow-margins) [changes](*https://wiki.php.net/rfc/abolish-short-votes) to the voting rules, there have also been a few controversial ((RFC))s which passed the vote, and caused some — in some cases, lots of — discussion.

Two recent ((RFC))s come to mind: the [deprecation of the short open tags](*https://wiki.php.net/rfc/deprecate_php_short_tags), as well as [several small deprecations](*https://wiki.php.net/rfc/deprecations_php_7_4) for ((PHP 7.4)).

Both ((RFC))s caused discussion on whether these changes are actually beneficial to the language, whether they should be allowed with only a 2/3 majority vote, and whether they should be considered harmful to the ((PHP)) community.

The basis for most of these discussions is the fact that ((PHP)) tries to maintain backwards compatibility as much as possible. One of the main thoughts behind this is that we want users to stay up-to-date with modern ((PHP)) versions, so we should give them as little problems as possible to upgrade.

Lessons were, rightfully, learned from the 5.* era: I share the opinion that all ((PHP)) developers and ecosystems should strive to stay up-to-date. It's a message that companies and developers should tell their clients at the start of every project: keeping it secure and up-to-date will take time, cost money, and there's no responsible way to avoid it.

It's a characteristic of professionalism. 

On the other hand: if we want to achieve this professionalism with our clients, we are also allowed to spend reasonable amounts of time on upgrades. This means that it's not the end of the world if there's a backwards incompatible change. 

As a day-by-day user of ((PHP)), I too have had my share of legacy projects that needed updating. Let me tell you this: I much more prefer ((PHP)) to move forward and mature further, rather than me winning an hour during upgrades. 

In my opinion, "language maturity" means that some old legacy stuff is cleaned up. It means that the language sometimes removes two ways to do the same thing. It means that, for example, short open tags are deprecated and removed. It means that sometimes my code will break. And as long as the language evolves in a good and healthy way, I don't mind.

If you're one of the enthusiastic guards of backwards compatibility: I know you mean it for the best. But I don't think it's that big a deal you make out of it. Don't think the world will end because there's a breaking change. We, userland developers, will manage. 

Let's not waste too much time with seemingly endless discussion over and over again. Let's move forward in balanced way.

---

Speaking of how we spend time. Internals have been discussing voting mechanics and what to do with controversial ((RFC))s for months now. 

Shouldn't we start looking at how other communities do this? For sure ((PHP)) can't be the only open source language being developed out there? 

It's time to call the current way of ((PHP))'s development for what it really is: the same discussions happen over and over again on a weekly or monthly basis without any progress; people are personally attacking others regularly; an insignificant ((RFC)) takes months of discussion and requires a re-vote after being accepted; there aren't any good ways to share constructive feedback apart from the big mailing list; the group of voters doesn't seem to be an accurate representation the actual ((PHP)) community.

Am I fair to call this system, at least partially, broken?

I believe our system should be thoroughly evaluated, and I think we _should_ look at how other open source communities, outside of ((PHP)), manage to keep moving their project forward in a healthy way.

--- 

With all of that being said, I appreciate
