To whomever contributes to ((PHP)), from a userland developer.

Let me start by thanking those who actively work on the ((PHP)) project. Those who contribute to the core, extensions, maintain the docs or vote on ((RFC))s: thank you for a language that I can use every day both in my professional and personal life. ((PHP)) has been a very useful tool to me for many years, and It's good to see lots of contributors help making it better every day.

I also want to mention that I, as everyone, am subject to confirmation bias. When I address one or two thoughts in this letter, I'll try my best to be as objective as possible, though I realise I'm looking through my lens, and not someone else's. 

Since the goal of this letter is to start a conversation, I'm open to hear your thoughts, also if they don't align to mine; please feel free to disagree.

---

I could start by listing lots of good things — there are many. Because I want to keep this letter on topic, I won't be doing that though. Don't take this as me being a disgruntled developer, I simply want to be efficient in conveying what I want to say.

So I want to write about how ((PHP)) is shaped and developed these days. I feel like I, as a userland developer, know a thing or two about using ((PHP)) in real projects. I believe I have an informed and relevant opinion on the matter.

Recently we've seen discussion regarding the ((RFC)) voting process. Besides [recent](*https://wiki.php.net/rfc/abolish-narrow-margins) [changes](*https://wiki.php.net/rfc/abolish-short-votes) to the voting rules, there have also been a few controversial ((RFC))s which passed the vote, and caused some — lots of, in some cases — discussion.

Two recent ((RFC))s come to mind: the [deprecation of the short open tags](*https://wiki.php.net/rfc/deprecate_php_short_tags), as well as [several small deprecations](*https://wiki.php.net/rfc/deprecations_php_7_4) for ((PHP 7.4)).

Both ((RFC))s caused discussion on whether these changes are actually beneficial to the language, whether they should be allowed with only a 2/3 majority vote, and whether they should be considered harmful.

The basis for most of these discussions is the fact that ((PHP)) tries to maintain backwards compatibility as much as possible. Its main goal being that we want users to stay up-to-date with modern ((PHP)) versions, so we should give them as little problems as possible to upgrade.

Lessons were, rightfully, learned from the 5.* era: I share the opinion that all ((PHP)) developers and ecosystems should strive to stay up-to-date. It's a message that companies and developers should tell their clients at the start of every project: keeping it secure and up-to-date will take time, cost money, and there's no responsible way to avoid it.

It's a characteristic of professionalism. 

On the other hand: if we want to achieve this professionalism with our clients, we are also allowed to spend reasonable amounts of time on upgrades. This means that it's not the end of the world if there's a backwards incompatible change. 

As a day-by-day user of ((PHP)), I too have had my share of legacy projects that needed updating. Let me tell you this: I much more prefer ((PHP)) to move forward and mature further, rather than me having to spend an hour less on upgrades. In my opinion, "maturity" means that some old legacy stuff is cleaned up. It means that, for example, short open tags are deprecated and removed — rather sooner than later. It means that sometimes my code will break. And as long as the language evolves in a good and healthy way, I don't mind.

If you're one of the enthusiastic guards of backwards compatibility: I know you mean it for the best. But I don't think it's that big a deal you make out of it. Don't think the world will end because there's a breaking change. We, userland developers, will manage. 

Let's not waste too much time with seemingly endless discussion over and over again. Let's move forward in balanced way.

---

I think ((PHP)) contributors should keep this balance in mind. Sure we shouldn't break all projects with every ((PHP)) upgrade. But on the other hand, the legacy projects shouldn't be the _main_ focus group.

In this day and age we have tools to automatically fix and analyse our ((PHP)) projects. I'm thinking of tools like phpstan, phpcs and Rector. We have lots of automatic support in upgrading our projects, even if some breaking changes are introduced.

I think we, the userland developers, should focus on education and awareness on this front more, rather than the core developers needing to have endless discussions on whether we should keep backwards compatibility at all costs.   
