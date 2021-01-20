I just read a post about PHP 8 that I couldn't just silently ignore. If you want to read it first, go [check it out](*https://24daysindecember.net/2020/12/21/a-perfect-storm/).

Suitably titled "A Perfect Storm", the author voices their concerns about how upgrading to PHP 8 isn't an easy path, and how open source maintainers have to struggle to be able to support PHP 8 on top of their existing codebase.

There's no denying that PHP 8 introduces [some breaking changes](/blog/new-in-php-8). Especially the addition of union types, named arguments and consequently the changes made to the reflection API might feel like a pain.

What the author seems to forget when calling PHP 8 "a nightmare" and claiming it'll take years before being able to use it, is that PHP 8 is a major version, and things are allowed to break. Better yet: it's the entire purpose of a major release.

I don't blame the author for this sentiment, it lives in lots of people's mind. I think it's a side effect from a long and stable period of releases during the 7.x era. We've got used to easy updates, and we find it difficult when our code breaks because of one.

The problem however isn't with PHP growing and maturing, it's with developers and companies not being able to adapt quickly enough. Here's the brutal truth: PHP 7.4 has [less than one year](*https://www.php.net/supported-versions.php) of active support to go. By the end of 2021, PHP 8 will be the only version (together with the future PHP 8.1) that's still actively worked on. You _need_ to get on board, or you risk becoming stuck in legacy land. Believe it or not, but PHP 7.4 will one day be what we perceive PHP 5.4 or PHP 5.6 to be: completely outdated, insecure and slow.

Instead of shifting the blame on the perfectly healthy and stable release cycle of PHP, we should look at ourselves and our companies. 

---

If you're still here and wanting to ride along, let's discuss a few things that _you_ can do to prevent ending up in legacy land.

First: learn and use the proper **tools**. A [craftsman is nothing without proper knowledge of his tools](/blog/craftsmen-know-their-tools). These tools include [static analysers](*https://psalm.dev/), [code formatters](*https://github.com/FriendsOfPHP/PHP-CS-Fixer), [IDEs](*https://www.jetbrains.com/phpstorm/), [test frameworks](*https://phpunit.de/) and [automatic updaters](*https://github.com/rectorphp/rector). Most problems you face during upgrades are actually solved automatically if only you'd use the proper tools. 

While you're at it, consider buying the people maintaining those projects a drink, they are going to save you hours upon hours of manual and boring labour; they are worth a few bucks. We recently upgraded a large project to PHP 8. It took a few hours of preparation and research, and only 2 hours to do the actual upgrade; it's doable. 

Next: if you're an **open source maintainer**. Don't bother supporting all of PHP 7 and 8 in the same version. Embrace PHP 8 as your main target and support 7.4 if it doesn't cause any problems. If you really need to actively support older versions, you'll have to support separate branches. 

What baffles me about the "open source argument" made by many against PHP 8, is that they seem to forget that their old tags will keep working just fine. It's not because code isn't actively maintained any more that you're prohibited from using it. If your users really need to support an older PHP version, have them use an older version of your packages. If there's a crucial feature missing from those older versions, they are free to fork the package and do whatever they want with it. There shouldn't be anything holding you back from only supporting the active PHP versions. If anything, you're encouraging the majority of your users to upgrade faster, you're doing the PHP community a favour. 

Finally, if you're in **management or running a company**: sticking with older PHP versions will always make you lose money in the end. Every year you hold off on updating your codebase, it becomes more difficult and time-consuming to catch up. Can you imagine what will happen when a critical security issue is discovered in your old version? On top of that: employees really worth their money won't stick with your legacy project forever. One day they'll seize a new opportunity if you don't keep them happy. If you're not keeping up to date, you're loosing in the end.

---

Part of being a professional developer is to be able to deal with these kinds of situations. Sure, I'd rather spend those hours spent updating on something else, but I know it's a small investment for a lot of joy in the long run.

Don't get dragged along the negativity, embrace the maturing language that is PHP and follow along. You won't regret it. 
