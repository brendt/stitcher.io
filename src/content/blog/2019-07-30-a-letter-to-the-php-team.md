To whomever contributes to ((PHP)), from a userland developer.

Let me start by thanking those who actively work on the ((PHP)) project. Those who contribute to the core, extensions, maintain the docs or vote on ((RFC))s: thank you for a language that I can use every day both in my professional and personal life. ((PHP)) has been a very useful tool to me for many years, and I'm happy to know so many people help making this project better every day.

Next I want to mention that I, as everyone, am subject to confirmation bias. When I address one or two thoughts in this letter, I'll try my best to be as objective as possible, though I realise I'm looking through my lens, and not someone else's. 

Since the goal of this letter is to start a conversation, I'm open to hear your thoughts, also when they are not the same as mine. So please feel free to disagree.

I could start by listing lots of good things — there are many. Because I want to keep this letter to the point, I won't do that though. Don't take this as me being a disgruntled developer, I simply want to be efficient in conveying this message.

On topic now. I want to write about how ((PHP)) is shaped and developed these days. I feel like I, as a userland developer, know a thing or two about using ((PHP)) in real projects, so I think I do have an informed relevant opinion on the matter.

Recently we've seen discussion on the topic of the ((RFC)) voting process. Besides [recent](*https://wiki.php.net/rfc/abolish-narrow-margins) [changes](*https://wiki.php.net/rfc/abolish-short-votes) to the voting rules, there have also be a few controversial ((RFC))s which passed the vote, and caused some discussion.

Two recent ((RFC))s come to mind: the [deprecation of the short open tags](*https://wiki.php.net/rfc/deprecate_php_short_tags), as well as [several small deprecations](*https://wiki.php.net/rfc/deprecations_php_7_4) for ((PHP 7.4)).

Both ((RFC))s caused discussion on whether these changes are actually beneficial to the language, whether they should be allowed in with only a 2/3 majority vote result, and whether they should be considered harmful.

The basis for most of this discussion is the fact that ((PHP)) tries it best to be backwards compatible as much as possible. The reasoning being that we want users to stay up-to-date with modern ((PHP)) versions, so we should give them as little problems as possible to upgrade.

Lessons were — rightfully — learned from the 5.* era. I share the mindset think that all ((PHP)) developers and ecosystems should always strive to stay up-to-date. It is something companies should tell their clients from the start: keeping your application up-to-date and secure will take time, cost money, and there's no way around it.

On the other hand though: if we can achieve this professionalism with our clients, we are also allowed to spend reasonable amounts of time on upgrades, meaning it's not the end of the world if there's a backwards incompatible change. 

As a developer, having to upgrade some old codebases, I want to tell you this: I much more prefer ((PHP)) to move forward and further mature, rather than me having to spend an hour less on upgrades. In my opinion, "further mature" means that some old legacy stuff is cleaned up. It means short open tags are deprecated and removed — rather sooner than later. It means sometimes my code will break; as long as the language can evolve, I don't mind.

All of this is of course based on me trusting the ((RFC)) voting process. And in general, I do trust it. There are some outliers though, that I want to address.

